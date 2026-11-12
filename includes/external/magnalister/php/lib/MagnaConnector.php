<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id$
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

define('ML_LOG_API_REQUESTS', false);

require_once(DIR_MAGNALISTER_INCLUDES . 'lib/functionLib.php');

# Magnalister class
class MagnaConnector {
	const DEFAULT_TIMEOUT_RECEIVE = 30;
	const DEFAULT_TIMEOUT_SEND    = 10;

	/* How often in a row HTTPS may fail without plain HTTP being a replacement, before a PHP process stops trying it. */
	const HTTPS_FAILURES_PER_PROCESS = 2;

	protected static $instance = null;

	protected $passPhrase = '';
	protected $language = 'english';
	protected $subsystem = 'Core';
	protected $timeoutrc = self::DEFAULT_TIMEOUT_RECEIVE; /* Receive Timeout in Seconds */
	protected $timeoutsn = self::DEFAULT_TIMEOUT_SEND;    /* Send Timeout in Seconds    */
	protected $apiUrl = '';
	protected $lastRequest = array();
	protected $requestTime = 0;
	protected $addRequestProps = array();
	protected $timePerRequest = array();
	protected $cURLStatus = array ('use' => true, 'ssl' => true, 'force' => false);
	protected $httpsFailures = 0;

	protected $cacheShortTime = array();

	/* Upper size (bytes) of a RAW API response string kept in $cacheShortTime.
	   The cache only ever hits when the exact same request is sent twice within
	   one PHP process - that is true for small control calls, never for the paging
	   loops of the order sync, whose OFFSET changes on every iteration. Keeping
	   those multi-megabyte responses around only piles up until memory_limit is
	   hit, so raw strings above this stay uncached. Decoded arrays stored by
	   submitRequestCached() are not measured: those calls are small config
	   lookups and are kept as they were. */
	protected $cacheShortTimeMaxSize = 262144;

	protected function __construct() {
		$this->updatePassPhrase();
		$this->cURLStatusInit();
		
		$this->setApiUrl(MAGNA_SERVICE_URL.MAGNA_API_SCRIPT);
		if (function_exists('getDBConfigValue')) {
			$this->setApiUrl(getDBConfigValue('general.apiurl', 0, ''));
		}
	}

	protected function __clone() {}

	/**
	 * @return MagnaConnector
	 */
	public static function gi() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function setApiUrl($apiUrl) {
		if (!empty($apiUrl)) {
			$this->apiUrl = $apiUrl;
		}
		return $this;
	}
	
	public function getApiUrl() {
		return $this->apiUrl;
	}
	
	public function setLanguage($lang) {
		$this->language = $lang;
	}

	public function setSubsystem($subsystem) {
		$this->subsystem = $subsystem;
	}

	public function getSubsystem() {
		return $this->subsystem;
	}

	public function setAddRequestsProps($addReqProps) {
		if (!is_array($addReqProps)) {
			$this->addRequestProps = array();
		} else {
			$this->addRequestProps = $addReqProps;
		}
	}

	public function updatePassPhrase() {
		if (function_exists('getDBConfigValue')) {
			$this->passPhrase = getDBConfigValue('general.passphrase', '0');
		}
	}

	public function setPassPhrase($pp) {
		$this->passPhrase = $pp;
	}

	public function setTimeOutInSeconds($timeout) {
		$this->timeoutrc = $timeout;
	}

	public function resetTimeOut() {
		$this->timeoutrc = self::DEFAULT_TIMEOUT_RECEIVE;
	}

	public function getLastRequest() {
		return $this->lastRequest;
	}
	
	protected function cURLStatusSave() {
		$_SESSION['ML_'.__CLASS__.'_UseCURL'] = $this->cURLStatus;
		if (function_exists('__ml_useCURL')) {
			if ($this->cURLStatus['force']) {
				__ml_useCURL('ForceCURL');
			} else {
				__ml_useCURL($this->cURLStatus['use']);
			}
		}
		//echo print_m($_SESSION['ML_'.__CLASS__.'_UseCURL']);
	}
	
	protected function cURLStatusInit() {
		if (   isset($_SESSION['ML_'.__CLASS__.'_UseCURL']) 
		    && is_array($_SESSION['ML_'.__CLASS__.'_UseCURL'])
		    && isset($_SESSION['ML_'.__CLASS__.'_UseCURL']['use'])
		) {
			$this->cURLStatus = $_SESSION['ML_'.__CLASS__.'_UseCURL'];
			return;
		}
		
		$this->cURLStatus['use'] = function_exists('__ml_useCURL')
			? __ml_useCURL()
			: function_exists('curl_init');
		
		if ($this->cURLStatus['use']) {
			$cURLVersion = curl_version();
			if (!is_array($cURLVersion) || !array_key_exists('protocols', $cURLVersion) || !array_key_exists('version', $cURLVersion)) {
				$this->cURLStatus['use'] = false;
			} else {
				$this->cURLStatus['ssl'] = in_array('https', $cURLVersion['protocols']);
			}
		}
		$this->cURLStatusSave();
	}
	
	protected function fwrite_stream($fp, $string) {
		for ($written = 0, $len = strlen($string); $written < $len; $written += $fwrite) {
			$fwrite = fwrite($fp, substr($string, $written));
			if ($fwrite === false) {
				return $written;
			}
		}
		return $written;
	}

	protected function file_post_contents($url, $request, $stripHeaders = true) {
		$eol = "\r\n";

		$url = parse_url($url);

		if (!isset($url['port'])) {
			if ($url['scheme'] == 'http') {
				$url['port'] = 80;
			} else if ($url['scheme'] == 'https') {
				$url['port'] = 443;
			}
		}
		$url['query'] = isset($url['query']) ? $url['query'] : '';
		$url['protocol'] = $url['scheme'].'://';

		$login = isset($url['user']) ? $url['user'].(isset($url['pass']) ? ':'.$url['pass'] : '') : '';
		$headers =
			"POST ".$url['path']." HTTP/1.0".$eol.
			"Host: ".$url['host'].$eol.
			"Referer: ".MLShop::gi()->getBaseUrl().$eol.
			"User-Agent: MagnaConnect NativeVersion".$eol.
			(($login != '') ? "Authorization: Basic ".base64_encode($login).$eol : '').
			"Content-Type: text/plain".$eol.
			"Content-Length: ".strlen($request).$eol.$eol.
			$request;
		
		//echo print_m($headers."\n\n");
		
		$result = '';
		
		$requestTime = microtime(true);
		
		$fp = false;
		$errno = $errstr = null;
		try {
			$fp = @fsockopen($url['host'], $url['port'], $errno, $errstr, $this->timeoutsn);
		} catch (Exception $e) { }
			
		if (!is_resource($fp)) {
			$curRequestTime = microtime(true) - $requestTime;
			$e = new MagnaException(ML_INTERNAL_API_TIMEOUT, MagnaException::TIMEOUT, $this->lastRequest, $result, $curRequestTime);
			MagnaError::gi()->addMagnaException($e);
			$this->timePerRequest[] = array (
				'request' => $this->lastRequest,
				'time' => $curRequestTime,
				'status' => 'TIMEOUT (Send)',
			);
			throw $e;
			return;
		}
		#echo print_m($headers."\n\n", trim(var_dump_pre($fp, true)));
		$this->fwrite_stream($fp, $headers);

		stream_set_timeout($fp, $this->timeoutrc);
		stream_set_blocking($fp, false);

		$info = stream_get_meta_data($fp);
		while ((!feof($fp)) && (!$info['timed_out'])) { 
			$result .= fgets($fp, 4096);
			$info = stream_get_meta_data($fp);
		}
		fclose($fp);

		#echo print_m($result, '$result');
		$curRequestTime = microtime(true) - $requestTime;
		
		if ($info['timed_out']) {
			$e = new MagnaException(ML_INTERNAL_API_TIMEOUT, MagnaException::TIMEOUT, $this->lastRequest, $result, $curRequestTime);
			MagnaError::gi()->addMagnaException($e);
			$this->timePerRequest[] = array (
				'request' => $this->lastRequest,
				'time' => $curRequestTime,
				'status' => 'TIMEOUT (Receive)',
			);
			throw $e;
		}

		if ($stripHeaders && (($nlpos = strpos($result, "\r\n\r\n")) !== false)) { // removes headers
			$result = substr($result, $nlpos + 4);
		}

		$this->requestTime += microtime(true) - $requestTime;

		return $result;
	}
	
	/**
	 * Tells whether the request left over the connection. Over HTTPS that means the secure connection was up.
	 */
	protected function requestWasSent($connection) {
		return curl_getinfo($connection, CURLINFO_REQUEST_SIZE) > 0;
	}

	/**
	 * Tells whether a plain HTTP answer only redirects to HTTPS, which makes plain HTTP no transport for the API there.
	 */
	protected function isRedirectToHttps($connection, $cURLVersion) {
		if (!is_array($cURLVersion) || !isset($cURLVersion['protocols']) || !in_array('https', $cURLVersion['protocols'])) {
			return false;
		}
		$info = curl_getinfo($connection);
		// "redirect_url" exists since PHP 5.3.7. Without it the answer is handled as before.
		if (!is_array($info) || !isset($info['http_code']) || !isset($info['redirect_url'])) {
			return false;
		}
		return in_array((int)$info['http_code'], array(301, 302, 307, 308))
			&& (stripos($info['redirect_url'], 'https://') === 0);
	}

	protected function curlRequest($url, $request, $useSSL = true) {
		if (!$this->cURLStatus['use']) {
			return $this->file_post_contents($url, $request);
		}
		
		$connection = curl_init();
		$cURLVersion = curl_version();
		
		// The session can be on plain HTTP from an earlier failure. Read here, because the status changes below.
		$sessionOnHttp = !$this->cURLStatus['ssl'];
		// When neither HTTPS nor plain HTTP reaches the API, the rest of this process stops waiting for HTTPS.
		$hasSSL = $this->cURLStatus['ssl'] && $useSSL && ($this->httpsFailures < self::HTTPS_FAILURES_PER_PROCESS);
		if ($hasSSL) {
			$url = str_replace('http://', 'https://', $url);
		} else {
			$url = str_replace('https://', 'http://', $url);
		}
		curl_setopt($connection, CURLOPT_URL, $url);
		if ($hasSSL) {
			curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
			if (defined('MAGNA_CURLOPT_SSLVERSION')) {
				curl_setopt($connection, CURLOPT_SSLVERSION, MAGNA_CURLOPT_SSLVERSION);
			}
		}
		curl_setopt($connection, CURLOPT_USERAGENT, "MagnaConnect cURLVersion".($hasSSL ? ' (SSL)' : ''));
		curl_setopt($connection, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($connection, CURLOPT_REFERER, MLShop::gi()->getBaseUrl());
		curl_setopt($connection, CURLOPT_POST, true);
		curl_setopt($connection, CURLOPT_POSTFIELDS, $request);
		curl_setopt($connection, CURLOPT_TIMEOUT, $this->timeoutrc);
		curl_setopt($connection, CURLOPT_CONNECTTIMEOUT, $this->timeoutsn);
		curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);

		$requestTime = microtime(true);
		$response = curl_exec($connection);
		#echo var_dump_pre($response, 'response');
		$curRequestTime = microtime(true) - $requestTime;
		$this->requestTime += $curRequestTime;
		#echo var_dump_pre(curl_error($connection), 'curl_error');
		
		if (curl_errno($connection) == CURLE_OPERATION_TIMEOUTED) {
			
			/* This detects a very seldom cURL bug, where cURL doesn't close the connection,
			 * even though it received everything in time. The connection is closed just because of
			 * of a timeout. If the respone is complete we can assume that this cURL version
			 * has a bug and we can switch to the fsocket version.
			 */
			if (   is_string($response)
			    && (strpos($response, '{#') !== false)
			    && (strpos($response, '#}') !== false)
			) {
				curl_close($connection);
				$this->cURLStatus['use'] = false;
				$this->cURLStatusSave();
				
				return $response;
			}

			if ($hasSSL && $this->requestWasSent($connection)) {
				// The secure connection was up and the request went out, so HTTPS works from this shop and only
				// the answer is late. Plain HTTP would not be faster: keep the transport and report the timeout.
				curl_close($connection);
				$e = new MagnaException(ML_INTERNAL_API_TIMEOUT, MagnaException::TIMEOUT, $this->lastRequest, is_string($response) ? $response : '', $curRequestTime);
				MagnaError::gi()->addMagnaException($e);
				$this->timePerRequest[] = array (
					'request' => $this->lastRequest,
					'time' => $curRequestTime,
					'status' => 'TIMEOUT (Receive)',
				);
				throw $e;
			}
			// A shop whose secure connection never came up may not be able to use HTTPS at all (old or special
			// servers): no exception here, the fallbacks below retry over plain HTTP and then over the socket.
		}
		
		if (curl_error($connection) != '') {
			if ($hasSSL) {
				$this->cURLStatus['ssl'] = false;
				$this->cURLStatusSave();
				
				return $this->curlRequest($url, $request, false);
			} else {
				$this->cURLStatus['use'] = false;
				$this->cURLStatusSave();
				
				return $this->file_post_contents($url, $request);
			}
		}

		if (!$hasSSL && $this->isRedirectToHttps($connection, $cURLVersion)) {
			// This server does not answer the API over plain HTTP, it only points to HTTPS. So plain HTTP is no
			// replacement here and the session goes back to HTTPS.
			curl_close($connection);
			$this->cURLStatus['ssl'] = true;
			$this->cURLStatusSave();
			if ($useSSL && $sessionOnHttp) {
				// HTTPS was not tried for this request, the session was on plain HTTP from before: send it once more.
				return $this->curlRequest($url, $request);
			}
			if (!$useSSL) {
				// HTTPS has just failed for this request. The next one tries it again, up to the limit per process.
				$this->httpsFailures++;
			}
			return $response;
		}

		if ($hasSSL) {
			$this->httpsFailures = 0;
		}
		$this->cURLStatus['force'] = true;
		$this->cURLStatusSave();
		
		curl_close($connection);
		
		return $response;
	}

	protected function finalizeRequest(&$requestFields) {
		$requestFields['PASSPHRASE'] = $this->passPhrase;
		if (MAGNA_DEBUG) {
			$requestFields['ECHOREQUEST'] = true;
		}
		if (!isset($requestFields['SUBSYSTEM'])) {
			$requestFields['SUBSYSTEM'] = $this->subsystem;
		}
		
		$requestFields['LANGUAGE'] = $this->language;
		$requestFields['CLIENTVERSION'] = LOCAL_CLIENT_VERSION;
		$requestFields['CLIENTBUILDVERSION'] = CLIENT_BUILD_VERSION;
		$requestFields['SHOPSYSTEM'] = SHOPSYSTEM;

        if (defined('SHOPSYSTEM_GAMBIO_CLOUD') && SHOPSYSTEM_GAMBIO_CLOUD === true) {
            $requestFields['SHOPSYSTEM'] = 'gambiocloud';
        }

	}

	protected function decodeResponse($response, $timePerRequest) {
		if (MAGNA_DEBUG && isset($_SESSION['MagnaRAW']) && ($_SESSION['MagnaRAW'] == 'true')) {
			echo print_m($response, $this->apiUrl);
		}

		$startPos = strpos($response, '{#') + 2;
		$endPos = strrpos($response, '#}') - $startPos;
		$cResponse = substr($response, $startPos, $endPos);

		if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
			$result = base64_decode($cResponse, true);
		} else {
			$result = base64_decode($cResponse);
		}

		if ($result !== false) {
			try {
				$result = json_decode($result, true);
			} catch (Exception $e) {}
		}

		if (!is_array($result)) {
			$e = new MagnaException(ML_ERROR_UNKNOWN, MagnaException::UNKNOWN_ERROR, $this->lastRequest, $response, $timePerRequest['time']);
			MagnaError::gi()->addMagnaException($e);
			$timePerRequest['status'] = 'UNKNOWN';
			$this->timePerRequest[] = $timePerRequest;
			throw $e;
		}
		
		if (MAGNA_DEBUG && isset($_SESSION['MagnaRAW']) && ($_SESSION['MagnaRAW'] == 'true')) {
			echo print_m($result);
		}
		
		return $result;
	}
	
	protected function preprocessResult($result, $response, &$timePerRequest) {
		if (!isset($result['STATUS'])) {
			$e = new MagnaException(
				html_entity_decode(ML_INTERNAL_INVALID_RESPONSE, ENT_NOQUOTES), 
				MagnaException::INVALID_RESPONSE, 
				$this->lastRequest, 
				(is_array($result) ? $result : $response),
				$timePerRequest['time']
			);
			MagnaError::gi()->addMagnaException($e);
			$timePerRequest['status'] = 'INVALID_RESPONSE';
			$this->timePerRequest[] = $timePerRequest;
			throw $e;
		}

		if ($result['STATUS'] == 'ERROR') {
			$msg = '';
			if (isset($result['ERRORS'])) {
				foreach ($result['ERRORS'] as $error) {
					if (isset($error['ERRORLEVEL']) && $error['ERRORLEVEL'] == 'FATAL') {
						$msg = $error['ERRORMESSAGE'];
						break;
					}
				}
			}
			$e = new MagnaException(
				($msg != '' ) ? $msg : ML_INTERNAL_API_CALL_UNSUCCESSFULL,
				MagnaException::NO_SUCCESS,
				$this->lastRequest,
				$result,
				$timePerRequest['time']
			);
			$timePerRequest['status'] = 'API_ERROR';
			$this->timePerRequest[] = $timePerRequest;
			MagnaError::gi()->addMagnaException($e);
			throw $e;
		}
		if (array_key_exists('DEBUG', $result)) {
			unset($result['DEBUG']);
		}
		$timePerRequest['status'] = $result['STATUS'];
	}

	protected function getFromShortTimeCache($requestHash) {
		if (isset($this->cacheShortTime[$requestHash])) {
			return $this->cacheShortTime[$requestHash];
		}
		return false;
	}
	
	protected function setShortTimeCache($requestHash, $response) {
		if (is_string($response) && (strlen($response) > $this->cacheShortTimeMaxSize)) {
			return;
		}
		$this->cacheShortTime[$requestHash] = $response;
	}

	/**
	 * Submit a request, reusing an identical response from cache.
	 *
	 * Two cache layers:
	 *   L1 — in-memory, always on, valid for the current request only.
	 *   L2 — persistent file cache under magnalister/cache/, used only when $ttl > 0,
	 *        expiring $ttl seconds after it was written (file mtime).
	 *
	 * Only successful (array) responses are cached — errors/false are never stored.
	 * Pass $ttl = 0 for calls whose result must stay fresh across requests (e.g. IsAuthed):
	 * that keeps the L1 per-request cache but skips persistence.
	 *
	 * @param array $requestFields The request fields
	 * @param int   $ttl Persistent cache lifetime in seconds; 0 disables persistence.
	 * @return array|bool The response, or false on empty/failed request.
	 */
	public function submitRequestCached($requestFields, $ttl = 1800) {
		if (!is_array($requestFields) || empty($requestFields)) {
			return false;
		}
		
		/* Key on the payload that is really sent, not on the caller's raw fields:
		 * finalizeRequest() still adds passphrase, language, subsystem, client
		 * versions and shopsystem. Keying on the raw fields would serve a cached
		 * response after an account, passphrase or admin language change. */
		$requestHash = md5($this->encodeRequest($this->finalizeRequestFields($requestFields)).'_cached');

		// L1: in-memory (this request).
		$cached = $this->getFromShortTimeCache($requestHash);
		if ($cached !== false) {
			return $cached;
		}

		// L2: persistent file cache with TTL.
		$cached = $this->getFromPersistentCache($requestHash, $ttl);
		if ($cached !== false) {
			$this->setShortTimeCache($requestHash, $cached);
			return $cached;
		}

		$result = $this->submitRequest($requestFields);

		/* Cache only successful responses. An empty reply comes back as
		   array('ERRORS' => ...), so is_array() alone would persist a transient
		   failure for the whole TTL - up to 24 h for some callers. */
		if (is_array($result) && empty($result['ERRORS'])) {
			$this->setShortTimeCache($requestHash, $result);
			$this->setPersistentCache($requestHash, $result, $ttl);
		}
		return $result;
	}

	protected function getPersistentCacheFile($requestHash) {
		return DIR_MAGNALISTER_FS.'cache/apicache_'.$requestHash.'.json';
	}

	/**
	 * Read a persisted response if the file exists and is younger than $ttl.
	 * @return array|bool The cached array, or false when disabled/missing/expired.
	 */
	protected function getFromPersistentCache($requestHash, $ttl) {
		if ($ttl <= 0) {
			return false;
		}
		$file = $this->getPersistentCacheFile($requestHash);
		if (!is_file($file)) {
			return false;
		}
		$mtime = @filemtime($file);
		if ($mtime === false || (time() - $mtime) >= $ttl) {
			return false; // expired
		}
		$raw = @file_get_contents($file);
		if ($raw === false || $raw === '') {
			return false;
		}
		$data = json_decode($raw, true);
		return is_array($data) ? $data : false;
	}

	/**
	 * Persist a successful response. No-op when $ttl <= 0 or the cache dir is absent.
	 * Writes atomically (temp file + rename) so concurrent reads never see a partial file.
	 */
	protected function setPersistentCache($requestHash, $response, $ttl) {
		if ($ttl <= 0 || !is_array($response)) {
			return;
		}
		$dir = DIR_MAGNALISTER_FS.'cache/';
		if (!is_dir($dir) || !is_writable($dir)) {
			return; // cache is best-effort; never fatal
		}
		$json = json_encode($response);
		if ($json === false) {
			return;
		}
		$file = $this->getPersistentCacheFile($requestHash);
		$tmp = $file.'.'.getmypid().'.tmp';
		if (@file_put_contents($tmp, $json, LOCK_EX) !== false) {
			@rename($tmp, $file);
		}
	}

	/**
	 * Merge addRequestProps and apply finalizeRequest(): produces the field set
	 * that submitRequest() actually sends, including passphrase, language,
	 * subsystem, client versions and shopsystem.
	 *
	 * @param array $requestFields
	 * @return array The finalized fields.
	 */
	protected function finalizeRequestFields($requestFields) {
		if (!empty($this->addRequestProps)) {
			$requestFields = array_merge(
				$this->addRequestProps,
				$requestFields
			);
		}
		
		$this->finalizeRequest($requestFields);
		
		return $requestFields;
	}
	
	/**
	 * Encode finalized fields into the exact payload that is POSTed to the API.
	 * md5() of the return value is the canonical identity of a request and is
	 * therefore the only correct cache key.
	 *
	 * @param array $requestFields Fields as returned by finalizeRequestFields().
	 * @return string The base64 encoded request.
	 */
	protected function encodeRequest($requestFields) {
		/* Add the shop base URL as the BLACKMAGIC field expected by the API. */
		$requestFields['BLACKMAGIC'] = strtr(
			base64_encode(MLShop::gi()->getBaseUrl()),
			'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=',
			'tfSX9JY+m0jZCcNp6zW=ydAiL7P4H1BnOwGQrsKlRh8ovFq/gDbUaT3MVEuIx5ek2'
		);
		arrayEntitiesToUTF8($requestFields);
		
		return base64_encode(json_encode($requestFields));
	}
	
	public function submitRequest($requestFields) {
		if (!is_array($requestFields) || empty($requestFields)) {
			return false;
		}
		
		$requestFields = $this->finalizeRequestFields($requestFields);
		
		/* Requests is complete, save it. */
		$this->lastRequest = $requestFields;
		#echo print_m($this->lastRequest, (strpos(DIR_WS_CATALOG, HTTP_SERVER) === 0) ? DIR_WS_CATALOG : HTTP_SERVER.DIR_WS_CATALOG);
		if (ML_LOG_API_REQUESTS) file_put_contents(DIR_MAGNALISTER_FS.'debug.log', print_m($this->lastRequest, 'API Request ('.date('Y-m-d H:i:s').')', true)."\n", FILE_APPEND);
		
		$requestString = $this->encodeRequest($requestFields);
		$requestHash = md5($requestString);
		
		#echo print_m($requestFields['ACTION'].' '.$requestHash);
		
		$_timer = microtime(true);
		$response = $this->getFromShortTimeCache($requestHash);
		if ($response === false) {
			if (function_exists("curl_version")) {
				$response = $this->curlRequest($this->apiUrl, $requestString);
			} else {
				$response = $this->file_post_contents($this->apiUrl, $requestString);
			}
		} else {
			#echo print_m('Cache');
		}
		$timePerRequest = array (
			'apiurl' => $this->apiUrl,
			'request' => $requestFields,
			'time' => microtime(true) - $_timer,
			'status' => 'ERROR'
		);
		
		if (empty($response)) {
			return array ('ERRORS' => array('Empty response from magnalister server'));
		}
		/* Cache only after the empty check, so a failed call is retried instead of
		   being replayed from cache for the rest of this request. */
		$this->setShortTimeCache($requestHash, $response);
		$result = $this->decodeResponse($response, $timePerRequest);
		
		$this->preprocessResult($result, $response, $timePerRequest);
		
		$this->timePerRequest[] = $timePerRequest;
		
		if (isset($result['Client'])) {
			$result['Client'] = array (
				'Connect' => $result['Client'],
			);
		}
		$result['Client']['Time'] = $timePerRequest['time'];
		
		return $result;
	}
	
	public function getRequestTime() {
		return $this->requestTime;
	}
	
	public function getTimePerRequest() {
		return $this->timePerRequest;
	}

}
