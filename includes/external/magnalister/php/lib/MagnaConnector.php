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

	protected $cacheShortTime = array();

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
	
	protected function curlRequest($url, $request, $useSSL = true) {
		if (!$this->cURLStatus['use']) {
			return $this->file_post_contents($url, $request);
		}
		
		$connection = curl_init();
		$cURLVersion = curl_version();
		
		$hasSSL = $this->cURLStatus['ssl'] && $useSSL;
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
			// dont throw exception here, sometimes there are timeout-problems with https, so retry curl without https and/or file_contents
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
		$this->cacheShortTime[$requestHash] = $response;
	}

	public function submitRequest($requestFields) {
		if (!is_array($requestFields) || empty($requestFields)) {
			return false;
		}
			
		if (!empty($this->addRequestProps)) {
			$requestFields = array_merge(
				$this->addRequestProps,
				$requestFields
			);
		}
		
		$this->finalizeRequest($requestFields);

		/* Requests is complete, save it. */
		$this->lastRequest = $requestFields;
		#echo print_m($this->lastRequest, (strpos(DIR_WS_CATALOG, HTTP_SERVER) === 0) ? DIR_WS_CATALOG : HTTP_SERVER.DIR_WS_CATALOG);
		if (ML_LOG_API_REQUESTS) file_put_contents(DIR_MAGNALISTER_FS.'debug.log', print_m($this->lastRequest, 'API Request ('.date('Y-m-d H:i:s').')', true)."\n", FILE_APPEND);

		/* Some black magic... Better don't touch it. It could bite! */
		${(chr(109)."\x61".chr(103)."\x69".chr(99)."\x46"."\x75"."\x6e"."\x63".chr(116)."\x69"."\x6f".chr(110
		).chr(115))}=array(("\x62"."\x61"."\x73".chr(101).chr(54).chr(52)."\x5f"."\x65".chr(110)."\x63"."\x6f"
		.chr(100)."\x65"),(chr(115)."\x74"."\x72"."\x74".chr(114)),array((chr(77).chr(76)."\x53".chr(104)."\x6f"
		.chr(112)),(chr(103)."\x69")),("\x63".chr(97)."\x6c"."\x6c"."\x5f".chr(117).chr(115)."\x65"."\x72".chr
		(95)."\x66"."\x75".chr(110).chr(99)),);${(chr(109).chr(97).chr(103).chr(105)."\x63")}=(chr(114)."\x65"
		."\x71".chr(117)."\x65"."\x73"."\x74".chr(70).chr(105).chr(101).chr(108).chr(100)."\x73");${("\x72".chr
		(101).chr(102).chr(101)."\x72"."\x65"."\x72")}=${("\x6d".chr(97)."\x67".chr(105)."\x63"."\x46".chr(117
		)."\x6e".chr(99).chr(116).chr(105).chr(111)."\x6e"."\x73")}[3](${(chr(109)."\x61".chr(103).chr(105)."\x63"
		."\x46".chr(117).chr(110).chr(99).chr(116)."\x69".chr(111).chr(110)."\x73")}[2])->{(chr(103).chr(101)."\x74"
		."\x42".chr(97).chr(115)."\x65"."\x55".chr(114)."\x6c")}();${${(chr(109).chr(97).chr(103)."\x69"."\x63")}
		}[(chr(66)."\x4c".chr(65)."\x43"."\x4b".chr(77).chr(65)."\x47"."\x49".chr(67))]=${("\x6d"."\x61"."\x67"
		.chr(105).chr(99)."\x46".chr(117).chr(110).chr(99).chr(116).chr(105)."\x6f"."\x6e".chr(115))}[1](${(chr
		(109)."\x61"."\x67".chr(105)."\x63"."\x46".chr(117)."\x6e".chr(99).chr(116).chr(105)."\x6f".chr(110)."\x73")}
		[0](${("\x72"."\x65"."\x66".chr(101)."\x72"."\x65"."\x72")}),("\x41".chr(66).chr(67)."\x44"."\x45".chr
		(70).chr(71)."\x48"."\x49"."\x4a"."\x4b"."\x4c"."\x4d".chr(78).chr(79).chr(80).chr(81).chr(82).chr(83
		).chr(84).chr(85)."\x56"."\x57"."\x58"."\x59"."\x5a"."\x61".chr(98).chr(99).chr(100)."\x65"."\x66"."\x67"
		.chr(104)."\x69".chr(106).chr(107).chr(108)."\x6d".chr(110).chr(111).chr(112)."\x71"."\x72"."\x73".chr
		(116)."\x75"."\x76".chr(119)."\x78"."\x79"."\x7a"."\x30"."\x31"."\x32".chr(51).chr(52).chr(53).chr(54)
		.chr(55)."\x38".chr(57).chr(43).chr(47).chr(61)),(chr(116)."\x66"."\x53"."\x58"."\x39"."\x4a"."\x59"."\x2b"
		."\x6d".chr(48).chr(106)."\x5a".chr(67).chr(99).chr(78)."\x70".chr(54)."\x7a"."\x57"."\x3d"."\x79"."\x64"
		."\x41".chr(105).chr(76).chr(55)."\x50".chr(52)."\x48".chr(49)."\x42"."\x6e"."\x4f".chr(119)."\x47".chr
		(81)."\x72".chr(115).chr(75)."\x6c"."\x52"."\x68".chr(56)."\x6f"."\x76".chr(70)."\x71".chr(47).chr(103
		).chr(68)."\x62"."\x55".chr(97)."\x54"."\x33".chr(77).chr(86)."\x45"."\x75"."\x49".chr(120)."\x35"."\x65"
		."\x6b"."\x32"));
		/* End of black magic :( */
		arrayEntitiesToUTF8($requestFields);
		
		$requestString = base64_encode(json_encode($requestFields));
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
		$this->setShortTimeCache($requestHash, $response);
		
		if (empty($response)) {
			return array ('ERRORS' => array('Empty response from magnalister server'));
		}
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
