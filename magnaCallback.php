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
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('E_RECOVERABLE_ERROR') OR define('E_RECOVERABLE_ERROR', 0x1000);
defined('E_DEPRECATED')        OR define('E_DEPRECATED',        0x2000);
defined('E_USER_DEPRECATED')   OR define('E_USER_DEPRECATED',   0x4000);
defined('PHP_INT_MAX')         OR define('PHP_INT_MAX',     2147483647); // for PHP < 5.0.5

//The timestamp of the start of the request. Available since PHP 5.1.0.
$_SERVER['REQUEST_TIME'] = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();

if (file_exists(dirname(__FILE__).'/magnadevconf.php')) {
	require_once(dirname(__FILE__).'/magnadevconf.php');
}

/**
 * Defines
 */
defined('MAGNA_DEBUG')         OR define('MAGNA_DEBUG', false);
defined('MAGNA_SHOW_WARNINGS') OR define('MAGNA_SHOW_WARNINGS', false);
defined('MAGNA_SHOW_FATAL')    OR define('MAGNA_SHOW_FATAL', false);

defined('MAGNA_SERVICE_URL')   OR define('MAGNA_SERVICE_URL', 'http://api.magnalister.com/');
define('MAGNA_API_SCRIPT', 'API/');
define('MAGNA_PLUGIN_DIR', 'magnalister/');
define('MAGNA_UPDATE_PATH', 'update/oscommerce/');
defined('MAGNA_UPDATE_FILEURL') OR define('MAGNA_UPDATE_FILEURL', MAGNA_SERVICE_URL.MAGNA_UPDATE_PATH);
define('MAGNA_PUBLIC_SERVER', 'http://magnalister.com/');
define('MAGNA_SUPPORT_URL', '<a href="'.MAGNA_PUBLIC_SERVER.'" title="'.MAGNA_PUBLIC_SERVER.'">'.MAGNA_PUBLIC_SERVER.'</a>');

$_magnacallbacktimer = $_executionTime = microtime(true);

function magnaHandleFatalError() {
	$errorOccured = false;
	if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
		$le = error_get_last();
		if (empty($le)) return;
		if (((E_NOTICE | E_USER_NOTICE | E_WARNING | E_USER_WARNING |
		      E_DEPRECATED | E_USER_DEPRECATED | E_STRICT) & $le['type']) == 0
		) {
			echo '<pre>'.print_r(error_get_last(), true).'</pre>';
			$errorOccured = true;
		}
	} else {
		global $php_errormsg;
		if (empty($php_errormsg)) return;
		echo '<pre>'.$php_errormsg.'</pre>';
		$errorOccured = true;
	}
	if ($errorOccured) {
		if (version_compare(PHP_VERSION, '5.2.5', '>=')) {
			echo '<pre>'.print_r(debug_backtrace(false), true).'</pre>';
		} else {
			echo '<pre>'.print_r(debug_backtrace(), true).'</pre>';
		}
	}
}

if (MAGNA_DEBUG && (MAGNA_SHOW_WARNINGS || MAGNA_SHOW_FATAL)) {
	ini_set("display_errors", 1);
	register_shutdown_function('magnaHandleFatalError');
	if (version_compare(PHP_VERSION, '5.2.0', '<')) {
		ini_set('track_errors', 1);
	}
}

if (isset($_GET['MLDEBUG']) && ($_GET['MLDEBUG'] == 'true')) {
	function ml_debug_out($m) {
		echo $m;
		flush();
	}
}

# Falls ctype ausgeschaltet (ja, das kommt vor)
if (!function_exists('ctype_digit')) {
	function ctype_digit($string) {
		return (boolean)preg_match('/^[0-9]*$/', $string);
	}
}

/**
 * Kodiert Ergebnisse die Funktionen liefern die API-artig aufgerufen wurden.
 */
function magnaEncodeResult($res) {
	return '{#'.base64_encode(serialize($res)).'#}';
}

define('MAGNA_WITHOUT_DB_INSTALL', 0x00000002);
define('MAGNA_WITHOUT_AUTH',       0x00000004);
define('MAGNA_WITHOUT_ACTIVATION', 0x00000008);

/**
 * Diese Funktion ruft andere hier hinterlegte Funktionnen auf. Sinn ist den zu
 * aendernden Code in Shop eigenen Scripten so gering wie moeglich zu halten.
 *
 * @param $functionName	Name der auszufuehrenden Funktion oder Aktion
 * @param $arguments	Assoziatives Array mit Parametern
 */
function magnaExecute($functionName, $arguments = array(), $includes = array(), $opts = 0) {
	if (!magnaInstalled(($opts & MAGNA_WITHOUT_DB_INSTALL) == MAGNA_WITHOUT_DB_INSTALL)
		|| !(
			(($opts & MAGNA_WITHOUT_ACTIVATION) == MAGNA_WITHOUT_ACTIVATION) || magnaActivated()
		)
		|| !(
			(($opts & MAGNA_WITHOUT_AUTH) == MAGNA_WITHOUT_AUTH) || magnaAuthed()
		)
	) {
		return false;
	}
	if (!empty($includes)) {
		foreach ($includes as $incl) {
			require_once(DIR_MAGNALISTER_FS_INCLUDES.'callback/'.$incl);
		}
	}

	if (function_exists($functionName) && is_string($functionName)) {
		return $functionName($arguments);
	}
	return false;
}

function magnaEchoDiePage($title, $content, $style = '') {
	header('Content-Type: text/html; charset=utf-8');
	echo '<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>magnalister :: ' . $title . '</title>
		<style>
body { font: 12px sans-serif; }
' . $style . '
		</style>
	</head>
	<body>
		' . $content . '
		<a href="' . $_SERVER['HTTP_REFERER'] . '" title="Back / Zur&uuml;ck">Back / Zur&uuml;ck</a>
	</body>
</html>';
	exit();
}

/**
 * Testet ob alle notwendigen und hinreichenden Kriterien zum Betrieb des magnalisters erfuellt werden.
 */
function magnaCompartCheck() {
	/* TimeOut Check */
	$maxExecutionTime = ini_get('max_execution_time');
	if ($maxExecutionTime != '0') {
		@set_time_limit($maxExecutionTime+5);
		$newMaxExecutionTime = ini_get('max_execution_time');
	}

	/* RAM Check */
	$maxRam = ini_get('memory_limit');
	ini_set('memory_limit', '247M');
	$newMaxRam = ini_get('memory_limit');
	ini_set('memory_limit', $maxRam);

	$currentClientURL = MAGNA_SERVICE_URL.MAGNA_UPDATE_PATH.'ClientVersion';
	/* cURL Check */
	if (function_exists('curl_version')) {
		$url = $currentClientURL;

		$cURLVersion = curl_version();

		$ch = curl_init();
		$hasSSL = @in_array('https', $cURLVersion['protocols']);
		if ($hasSSL) {
			$url = str_replace('http://', 'https://', $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			if (defined('MAGNA_CURLOPT_SSLVERSION')) {
				curl_setopt($ch, CURLOPT_SSLVERSION, MAGNA_CURLOPT_SSLVERSION);
			}
		}
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$localClientVersionCURL = curl_exec($ch);
		if (curl_errno($ch) == CURLE_OPERATION_TIMEOUTED) {
			$localClientVersionCURL = false;
		}
		curl_close($ch);

	} else {
		$cURLVersion = array();
		$cURLVersion['version'] = false;
		$localClientVersionCURL = false;
		$hasSSL = false;
	}

	if (file_exists(DIR_MAGNALISTER_FS_INCLUDES . 'lib/MagnaDB.php')) {
		require_once(DIR_MAGNALISTER_FS_INCLUDES . 'lib/MagnaDB.php');
	}

	if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
		$magicquotes = false;
	} else {
		$magicquotes = (boolean)get_magic_quotes_gpc();
	}

	return array(
		'timeout' => array (
			'changeable' => (($maxExecutionTime == '0') || ($maxExecutionTime != $newMaxExecutionTime)),
			'default' => $maxExecutionTime
		),
		'ram' => array (
			'changeable' => ($maxRam != $newMaxRam),
			'default' => $maxRam
		),
		'safemode' => MAGNA_SAFE_MODE,
		'magicquotes' => $magicquotes,
		'phpversion' => PHP_VERSION,
		'mysqlversion' => class_exists('MagnaDB') ? MagnaDB::gi()->fetchOne('SELECT VERSION()') : mysql_result(mysql_query('SELECT VERSION()'), 0),
		'curl' => array (
			'version' => $cURLVersion['version'],
			'hasSSL' => $hasSSL,
			'connects' => ($localClientVersionCURL != 0)
		),
		'file_get_contents' => (@file_get_contents($currentClientURL) !== false),
		'sapi_name' => php_sapi_name(),
		'ml_installed' => magnaInstalled(),
		'ml_activated' => magnaActivated(),
	);
}

function __ml_useCURL($bl = null) {
	global $__ml_useCURL;

	$d = isset($_SESSION['ML_UseCURL']) && is_array($_SESSION['ML_UseCURL'])
		? $_SESSION['ML_UseCURL']
		: (isset($__ml_useCURL) && is_array($__ml_useCURL)
			? $__ml_useCURL
			: array ()
		);

	if (!isset($d['ForceCURL']) || !isset($d['UseCURL'])) {
		$d = array (
			'ForceCURL' => false,
			'UseCURL' => function_exists('curl_init')
		);
	}

	/* read */
	if ($bl === null) {
		if (defined('MAGNA_USE_CURL') && is_bool(MAGNA_USE_CURL)) {
			return MAGNA_USE_CURL;
		}
		if (isset($d['ForceCURL']) && ($d['ForceCURL'] === true)) {
			// READ ForceCURL === true
			return true;
		}
		if (isset($d['UseCURL']) && is_bool($d['UseCURL'])) {
			// READ UseCURL (bool)
			return $d['UseCURL'];
		}
		//echo "NO READ\n";
		return function_exists('curl_init');

	/* write */
	} else {
		if ($bl === 'ForceCURL') {
			$d['ForceCURL'] = true;
			$d['UseCURL'] = true;
		} else if ($d['ForceCURL'] !== true) {
			$d['UseCURL'] = (bool)$bl;
		}

		if (!empty($_SESSION)) {
			//echo "WRITE SESSION\n";
			$_SESSION['ML_UseCURL'] = $d;
		} else {
			//echo "WRITE GLOBAL\n";
			$__ml_useCURL = $d;
		}
		return $d['UseCURL'];
	}
}

function fileGetContentsPHP($path, &$warnings = null, $timeout = 10) {
        //echo __METHOD__."\n";
        if ($timeout > 0) {
                $context = stream_context_create(array(
                        'http' => array('timeout' => $timeout)
                ));
        } else {
                $context = null;
        }
        $timeout_ts = time() + $timeout;
        $next_try = false;

        ob_start();
        do {
                if ($next_try) usleep(rand(500000, 1500000));
                $return = file_get_contents($path, false, $context);
                $warnings = ob_get_contents();
                $next_try = true;
        } while ((false === $return) && (time() < $timeout_ts));
        ob_end_clean();

        return $return;
}

function fileGetContentsCURL($path, &$warnings = null, $timeout = 10, $forceSSLOff = false) {
        $useCURL = __ml_useCURL();
        if ($useCURL === false) {
                $warnings = 'cURL disabled';
                return false;
        }

        //echo __METHOD__."\n";
        if (!function_exists('curl_init') || (strpos($path, 'http') !== 0)) {
                return false;
        }
        $cURLVersion = curl_version();
        if (!is_array($cURLVersion) || !array_key_exists('version', $cURLVersion)) {
                return false;
        }

        $warnings = '';
        $ch = curl_init();

        $hasSSL = is_array($cURLVersion) && array_key_exists('protocols', $cURLVersion) && in_array('https', $cURLVersion['protocols']);
        if ($hasSSL && !$forceSSLOff) {
                $path = str_replace('http://', 'https://', $path);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                if (defined('MAGNA_CURLOPT_SSLVERSION')) {
                        curl_setopt($ch, CURLOPT_SSLVERSION, MAGNA_CURLOPT_SSLVERSION);
                }
        }

        curl_setopt($ch, CURLOPT_URL, $path);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($timeout > 0) {
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        }
        //*
        $timeout_ts = time() + $timeout;
        $next_try = false;
        $return = false;

        do {
                //break;
                if ($next_try) usleep(rand(500000, 1500000));
                $return = curl_exec($ch);
                $next_try = true;
        } while (curl_errno($ch) && (time() < $timeout_ts));
        //*/
        if (curl_errno($ch) == CURLE_OPERATION_TIMEOUTED) {
                __ml_useCURL(false);
                $return = false;
        }

        $warnings = curl_error($ch);
        /*
        __ml_useCURL(false);
        $return = false;
        $warnings = 'Timeout';
        //*/

        if (!empty($return)) {
                __ml_useCURL('ForceCURL');
        }

        curl_close($ch);

        return $return;
}

function fileGetContents($path, &$warnings = null, $timeout = 10) {
        if (!defined('Magna_CURL_NO_SSL') && ($contents = fileGetContentsCURL($path, $warnings, $timeout)) !== false) {
                return $contents;
        } elseif (($contents = fileGetContentsCURL($path, $warnings, $timeout, true)) !== false) {// no-ssl
                defined('Magna_CURL_NO_SSL') or define('Magna_CURL_NO_SSL', true);
                return $contents;
        }
        return fileGetContentsPHP($path, $warnings, $timeout);
}

function defineMagnalisterDir() {
	// Order is important here. Do not change without a good reason!

	// modified v >= 2.0
	if (defined('DIR_FS_EXTERNAL') && is_dir(DIR_FS_EXTERNAL.'magnalister/') && defined('DIR_WS_EXTERNAL')) {
		define('DIR_MAGNALISTER_FS', DIR_FS_EXTERNAL.'magnalister/');
		define('DIR_MAGNALISTER_WS', DIR_WS_EXTERNAL.'magnalister/');

	// called from admin
	} else if ((MAGNA_IN_ADMIN == true) && file_exists('includes/magnalister/')) {
		define('DIR_MAGNALISTER_FS', 'includes/magnalister/');
		define('DIR_MAGNALISTER_WS', 'includes/magnalister/');

	// fallback 1 (called from frontend)
	} else if ((MAGNA_IN_ADMIN == false) && defined('DIR_FS_ADMIN') && file_exists(DIR_FS_ADMIN.'includes/magnalister/')) {
		define('DIR_MAGNALISTER_FS', DIR_FS_ADMIN.'includes/magnalister/');
		define('DIR_MAGNALISTER_WS', basename(DIR_FS_ADMIN).'/includes/magnalister/');

	// fallback 2
	} else if ((MAGNA_IN_ADMIN == false) && file_exists(dirname(__FILE__).'/admin/includes/magnalister/')) {
		define('DIR_MAGNALISTER_FS', dirname(__FILE__).'/admin/includes/magnalister/');
		define('DIR_MAGNALISTER_WS', 'admin/includes/magnalister/');

	// failure
	} else {
		define('DIR_MAGNALISTER_FS', false);
		define('DIR_MAGNALISTER_WS', false);
	}
}

function magnalisterFilesLocated() {
	if (!defined('DIR_MAGNALISTER_FS') || (($dir_magnalister_fs = DIR_MAGNALISTER_FS) && empty($dir_magnalister_fs))) {
		magnaEchoDiePage(
			'Shop Admin directory not found / Shop Admin Verzeichnis nicht gefunden.',
			'<p>The Shop Admin directory can not be found. To fix this open the file
			<tt>' . dirname(__FILE__) . '/includes/configure.php</tt> and add the following line:</p>
			<pre>define(\'DIR_FS_ADMIN\', \'/absolute/path/to/your/shop/admin/\');</pre>
			<p>Please use the absolute path to your shop admin directory.</p><br/>
			<p>Das Shop Admin Verzeichnis konnte nicht gefunden werden. Um dies zu
			korrigieren &ouml;ffnen Sie die Datei <tt>' . dirname(__FILE__) . '/includes/configure.php</tt>
			und f&uuml;gen Sie folgende Zeile hinzu:</p>
			<pre>define(\'DIR_FS_ADMIN\', \'/absoluter/pfad/zum/shop/admin/\');</pre>
			<p>Bitte benutzen Sie den absoluten Pfad zum Shop Admin Verzeichnis.</p>
		');
	}
	return true;
}

function magnaInstalled($woDBCheck = false) {
	global $_magnaIsInstalled, $_magnaFilesInstalled;

	if ($woDBCheck) {
		if (isset($_SESSION['magnaFilesInstalled']) && ($_SESSION['magnaFilesInstalled'] === true)) {
			$_magnaFilesInstalled = $_SESSION['magnaFilesInstalled'];
		}
		if (isset($_magnaFilesInstalled) && is_bool($_magnaFilesInstalled)) {
			return $_magnaFilesInstalled;
		}
	} else {
		if (isset($_SESSION['magnaIsInstalled']) && ($_SESSION['magnaIsInstalled'] === true)) {
			$_magnaIsInstalled = $_SESSION['magnaIsInstalled'];
		}
		if (isset($_magnaIsInstalled) && is_bool($_magnaIsInstalled)) {
			return $_magnaIsInstalled;
		}
	}

	if (!defined('DIR_MAGNALISTER_FS_INCLUDES')) {
		if (!defined('DIR_MAGNALISTER_FS')) defineMagnalisterDir();
		define('DIR_MAGNALISTER_FS_INCLUDES', DIR_MAGNALISTER_FS.'php/');
		define('DIR_MAGNALISTER_FS_CALLBACK', DIR_MAGNALISTER_FS_INCLUDES.'callback/');
	}

	$_magnaFilesInstalled = file_exists(DIR_MAGNALISTER_FS_INCLUDES.'lib/MagnaDB.php')
		&& file_exists(DIR_MAGNALISTER_FS_INCLUDES.'modules.php')
		&& file_exists(DIR_MAGNALISTER_FS_INCLUDES.'lib/functionLib.php')
		&& file_exists(DIR_MAGNALISTER_FS_CALLBACK.'callbackFunctions.php')
		&& is_dir(DIR_MAGNALISTER_FS.'db/');
	if (!$_magnaFilesInstalled) return $_magnaFilesInstalled;
	if ($woDBCheck) {
		return $_magnaFilesInstalled;
	}
	require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/MLTables.php');
	require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/MagnaDB.php');
	include_once(DIR_MAGNALISTER_FS_INCLUDES.'identifyShop.php');
	//commerce:Seo v2
	if (defined('DB_SERVER_CHARSET')) {
		MagnaDB::gi()->setCharset(DB_SERVER_CHARSET);
	}
	if (SHOPSYSTEM == 'gambio' && (($sVersion = mlGetGambioShopSystemVersion()) !== false)) {
        if (!defined('ML_GAMBIO_VERSION')) {
            define('ML_GAMBIO_VERSION', $sVersion);
        }
		if (version_compare($sVersion, '2.1', '>=')) {
			MagnaDB::gi()->setCharset('utf8');
		}
		// in gambio v2.5.2.1 ml will displayed in iframe so no stuff to display from gambio backend
		if (version_compare($sVersion, '2.5.2.1', '>=') && !defined('ML_GAMBIO_USE_IFRAME')) {
			define('ML_GAMBIO_USE_IFRAME', true);
		}
	}
	$_magnaIsInstalled = MagnaDB::gi()->tableExists(TABLE_MAGNA_CONFIG);
	if (!$_magnaIsInstalled) return $_magnaIsInstalled;

	$dbV = (int)MagnaDB::gi()->fetchOne('SELECT `value` FROM `'.TABLE_MAGNA_CONFIG.'` WHERE `mkey`=\'CurrentDBVersion\'');
	if ($dbV <= 0) {
		$_magnaIsInstalled = false;
		return $_magnaIsInstalled;
	}

	$dbDir = DIR_MAGNALISTER_FS.'db/';
	if (!$dirhandle = @opendir($dbDir)) {
		$_magnaIsInstalled = false;
		return $_magnaIsInstalled;
	}
	$sqlFiles = array();
	while (false !== ($filename = readdir($dirhandle))) {
		if (!preg_match('/^[0-9]*\.sql\.php$/', $filename)) continue;
		$sqlFiles[] = $filename;
	}
	sort($sqlFiles);
	$nDBV = (int)array_pop($sqlFiles);
	#var_dump($dbV, $nDBV, $dbV < $nDBV);
	if ($dbV < $nDBV) {
		$_magnaIsInstalled = false;
	}
	$_SESSION['magnaIsInstalled'] = $_magnaIsInstalled = true;
	return $_magnaIsInstalled;
}

function magnaActivated() {
	global $_magnaIsActivated;
	if (isset($_magnaIsActivated) && is_bool($_magnaIsActivated)) {
		return $_magnaIsActivated;
	}
	if (!class_exists('MagnaDB')) {
		$_magnaIsActivated = false;
		return $_magnaIsActivated;
	}
	if (!defined('TABLE_ADMIN_ACCESS')) define('TABLE_ADMIN_ACCESS', 'admin_access');
	if (MagnaDB::gi()->tableExists(TABLE_ADMIN_ACCESS)) {
		$adminAccess = MagnaDB::gi()->fetchRow('SELECT * FROM '.TABLE_ADMIN_ACCESS.' LIMIT 1');
		$_magnaIsActivated = isset($adminAccess['magnalister']) && MagnaDB::gi()->tableExists(TABLE_MAGNA_CONFIG);
	} else {
		$_magnaIsActivated = MagnaDB::gi()->tableExists(TABLE_MAGNA_CONFIG);
	}
	// gambio 3.9 and newer: Table admin_access can exist but is not used, check gx_configurations
	if (    !$_magnaIsActivated
	     && MagnaDB::gi()->tableExists('gx_configurations')) {
		$_magnaActiveKeyCount = 0;
		$magnalisterInstalledAndActiveGX = MagnaDB::gi()->fetchArray('SELECT `key`, `value`
		     FROM gx_configurations
		    WHERE `key` IN (\'gm_configuration/MODULE_CENTER_MAGNALISTER_INSTALLED\', \'configuration/MODULE_MAGNALISTER_STATUS\')');
		foreach ($magnalisterInstalledAndActiveGX as $magnalisterInstalledAndActiveGXRow) {
			if (    $magnalisterInstalledAndActiveGXRow['value'] = 'True'
			     || $magnalisterInstalledAndActiveGXRow['value'] = '1') {
				$_magnaActiveKeyCount += 1;
			}
		}
		if ($_magnaActiveKeyCount >= 2) {
			$_magnaIsActivated = true;
		}
	}
	return $_magnaIsActivated;
}

function magnaAuthed() {
	global $_magnaIsAuthed, $magnaConfig;
	if (isset($_magnaIsAuthed) && is_bool($_magnaIsAuthed)) {
		return $_magnaIsAuthed;
	}
	if (isset($magnaConfig['maranon']) && is_array($magnaConfig['maranon']) && !empty($magnaConfig['maranon'])) {
		$_magnaIsAuthed = true;
	} else {
		$_magnaIsAuthed = false;
	}
	return $_magnaIsAuthed;
}

function refreshCurrentClientVersion() {
	if (($currentClientVersion = fileGetContents(
			MAGNA_UPDATE_FILEURL.'ClientVersion/'.LOCAL_CLIENT_VERSION.'/',
			$foo,
			(MAGNA_CALLBACK_MODE == 'UTILITY') ? 1 : 5
		)) !== false
	) {
		$currentClientVersion = @json_decode($currentClientVersion, true);
	}
	if (   !is_array($currentClientVersion)
		|| !array_key_exists('CLIENT_VERSION', $currentClientVersion)
		|| ($currentClientVersion['CLIENT_VERSION'] == 0)
	) {
		$currentClientVersion = array (
			'CLIENT_VERSION' => 0,
			'MIN_CLIENT_VERSION' => 0,
			'CLIENT_BUILD_VERSION' => 0,
			/* only 10 minutes */
			'DATETIME' => time() - 60 * 60 * 24 + 10 * 60
		);
	} else {
		$currentClientVersion['DATETIME'] = time();
	}
	#echo print_m($currentClientVersion, 'nu cache');
	MagnaDB::gi()->insert(TABLE_MAGNA_CONFIG, array (
		'mpID' => 0,
		'mkey' => 'CurrentClientVersion',
		'value' => serialize($currentClientVersion)
	), true);
	return $currentClientVersion;
}

function magnaDetermineCurrentClientVersion() {
	#$_t = microtime(true);
	$cCVDB = MagnaDB::gi()->fetchOne('SELECT value FROM '.TABLE_MAGNA_CONFIG.' WHERE mpID=0 AND mkey=\'CurrentClientVersion\'');
	$cCVDB = @unserialize($cCVDB);
	$cCV = array();
	do {
		if (    !is_array($cCVDB)
			 || !array_key_exists('DATETIME', $cCVDB)) break;
		if (    ($cCVDB['DATETIME'] < (time() - 10 * 60))
			 && (    !array_key_exists('CLIENT_VERSION',       $cCVDB)
			      || !array_key_exists('CLIENT_BUILD_VERSION', $cCVDB)
			      || !array_key_exists('MIN_CLIENT_VERSION',   $cCVDB))
		   ) break;
		$lastPulled = @strtotime($cCVDB['DATETIME']);
		/* Cached for 24h, but only in magnaCallback */
		if ($cCVDB['DATETIME'] < (time() - 60 * 60 * 24)) break;
		$cCV = $cCVDB;
		#echo 'Cache! :-D '.(microtime(true) - $_t)."\n";
	} while (false);
	if (empty($cCV)) {
		$cCV = refreshCurrentClientVersion();
	}
	#flush();
	define('CURRENT_CLIENT_VERSION', $cCV['CLIENT_VERSION']);
	define('MINIMUM_CLIENT_VERSION', $cCV['MIN_CLIENT_VERSION']);
	define('CURRENT_BUILD_VERSION', $cCV['CLIENT_BUILD_VERSION']);
	if (CURRENT_CLIENT_VERSION != 0) {
		define(
			'MAGNA_VERSION_TOO_OLD',
			version_compare(CURRENT_CLIENT_VERSION, LOCAL_CLIENT_VERSION, '>') && version_compare(MINIMUM_CLIENT_VERSION, LOCAL_CLIENT_VERSION, '>')
		);
	} else {
		define('MAGNA_VERSION_TOO_OLD', false);
	}
}

function magnaCallbackRun() {
	/* These variables are used among the mangalister. So they have to be declared as global. */
	global $magnaConfig, $_magnaLanguage, $_MagnaSession, $_MagnaShopSession, $_modules,
	       $_magnaIsInstalled, $_magnaIsActivated, $_magnaIsAuthed;

	date_default_timezone_set(@date_default_timezone_get());

	if (!defined('_VALID_XTC')) {
		define('_VALID_XTC', true);
	}

	/* ZOMG... why is that even possible. This is grist to the mill of all php haters.
	 * We have to check if the locale settings of php are converting the representation of
	 * floats to something that contains a ',' instead of a '.'.
	 * ... because , works so well with databases etc :-/
	 */
	$str = (string)(float)3.1415;
	if (strpos($str, ',') !== false) {
		setlocale(LC_NUMERIC, 'en_US');
		$str = (string)(float)3.1415;
		if (strpos($str, ',') !== false) {
			echo 'Floats are represented with "," instead of ".". '.
			     'The behavior could not be changed. '.
			     'Please contact your administrator to fix this issue.';
			return; // do nothing
		}
	}
	unset($str);

	// detect safe mode
	$safe_mode = strtolower(ini_get('safe_mode'));
	switch ($safe_mode) {
		case 'on':
		case 'yes':
		case 'true': {
			define('MAGNA_SAFE_MODE', true);
			break;
		}
		default: {
			define('MAGNA_SAFE_MODE', (bool)((int)$safe_mode));
			break;
		}
	}
	unset($safe_mode);

	// locate the magnalister files if that has not been done yet
	if (!defined('DIR_MAGNALISTER_FS')) {
		defineMagnalisterDir();
	}
	// check if the magnalister files have been located
	if (DIR_MAGNALISTER_FS == false) {
		// They have not. Show a warning if the magnaCallback is running standalone.
		if (MAGNA_CALLBACK_MODE == 'STANDALONE') {
			magnalisterFilesLocated();
		}
		// Otherwise simply abort the rest of the magnaCallback bootstrap and don't do anything.
		#echo 'NO NOEZ';
		return;
	}

	#echo 'HAI :D';
	#var_dump(DIR_MAGNALISTER_FS);

	if (!defined('DIR_FS_DOCUMENT_ROOT')) {
		define('DIR_FS_DOCUMENT_ROOT', dirname(__FILE__).'/');
	}

	// FS
	define('DIR_MAGNALISTER_FS_INCLUDES',   DIR_MAGNALISTER_FS.'php/');
	define('DIR_MAGNALISTER_FS_MODULES',    DIR_MAGNALISTER_FS_INCLUDES.'modules/');
	define('DIR_MAGNALISTER_FS_CALLBACK',   DIR_MAGNALISTER_FS_INCLUDES.'callback/');
	define('DIR_MAGNALISTER_FS_CACHE',      DIR_MAGNALISTER_FS.'cache/');
	define('DIR_MAGNALISTER_FS_IMAGECACHE', DIR_MAGNALISTER_FS_CACHE.'images/');
	define('DIR_MAGNALISTER_FS_RESOURCE',   DIR_MAGNALISTER_FS.'resource/');
	define('DIR_MAGNALISTER_FS_IMAGES',     DIR_MAGNALISTER_FS.'images/');
	define('DIR_MAGNALISTER_FS_CONTRIBS',   DIR_MAGNALISTER_FS.'contribs/');
	define('DIR_MAGNALISTER_FS_LOGS',       DIR_MAGNALISTER_FS.'logs/');

	// @deprecated
	define('DIR_MAGNALISTER',            DIR_MAGNALISTER_FS);
	define('DIR_MAGNALISTER_INCLUDES',   DIR_MAGNALISTER_FS_INCLUDES);
	define('DIR_MAGNALISTER_MODULES',    DIR_MAGNALISTER_FS_MODULES);
	define('DIR_MAGNALISTER_CALLBACK',   DIR_MAGNALISTER_FS_CALLBACK);
	define('DIR_MAGNALISTER_CACHE',      DIR_MAGNALISTER_FS_CACHE);
	define('DIR_MAGNALISTER_IMAGECACHE', DIR_MAGNALISTER_FS_IMAGECACHE);
	define('DIR_MAGNALISTER_RESOURCE',   DIR_MAGNALISTER_FS_RESOURCE);
	define('DIR_MAGNALISTER_IMAGES',     DIR_MAGNALISTER_FS_IMAGES);
	define('DIR_MAGNALISTER_CONTRIBS',   DIR_MAGNALISTER_FS_CONTRIBS);
	define('DIR_MAGNALISTER_LOGS',       DIR_MAGNALISTER_FS_LOGS);

	// WS
	define('DIR_MAGNALISTER_WS_CACHE',      DIR_MAGNALISTER_WS.'cache/');
	define('DIR_MAGNALISTER_WS_IMAGECACHE', DIR_MAGNALISTER_WS_CACHE.'images/');
	define('DIR_MAGNALISTER_WS_IMAGES',     DIR_MAGNALISTER_WS.'images/');

    // HTTP_CATALOG_SERVER defined only in admin area, used by some magnaCallback called functions
    if (!defined('HTTP_SERVER')) {
        $sServer = '';
        if (isset($_SERVER) && array_key_exists('HTTP_HOST', $_SERVER)) {
            $sServer = $_SERVER['HTTP_HOST'];
        }
    } else {
        $sServer = HTTP_SERVER;
    }
    defined('HTTP_CATALOG_SERVER') OR define('HTTP_CATALOG_SERVER', $sServer);

	/* Issued a compart check (eiter get or post)? */
	if ((MAGNA_CALLBACK_MODE == 'STANDALONE') && array_key_exists('function', $_REQUEST) && ($_REQUEST['function'] == 'magnaCompartCheck')) {
		echo magnaEncodeResult(magnaCompartCheck());
		return;
	}

	/* Wenn Dateien noch nicht installiert, nix machen */
	if (!magnaInstalled(true)) {
		if (MAGNA_CALLBACK_MODE == 'STANDALONE') {
			echo 'magnalister files not installed yet';
		}
		return;
	}

	require_once(DIR_MAGNALISTER_FS_INCLUDES . 'lib/classes/MLShop.php');
	require_once(DIR_MAGNALISTER_FS_INCLUDES . 'lib/json_wrapper.php');
	require_once(DIR_MAGNALISTER_FS_INCLUDES . 'lib/functionLib.php');
	require_once(DIR_MAGNALISTER_FS_INCLUDES . 'lib/MLTables.php');
	require_once(DIR_MAGNALISTER_FS_INCLUDES . 'lib/MagnaDB.php');
	include_once(DIR_MAGNALISTER_FS_INCLUDES . 'identifyShop.php');
	//commerce:Seo v2
	if (defined('DB_SERVER_CHARSET')) {
		MagnaDB::gi()->setCharset(DB_SERVER_CHARSET);
	}

    // for Gambio 4.1 and newer
    if (TABLE_CONFIGURATION == 'gx_configurations' && !defined('ML_GAMBIO_41_NEW_CONFIG_TABLE')) {
        define('ML_GAMBIO_41_NEW_CONFIG_TABLE', true);
    }

	if (SHOPSYSTEM == 'gambio' && (($sVersion = mlGetGambioShopSystemVersion()) !== false)) {
        if (!defined('ML_GAMBIO_VERSION')) {
            define('ML_GAMBIO_VERSION', $sVersion);
        }
		if (version_compare($sVersion, '2.1', '>=')) {
			MagnaDB::gi()->setCharset('utf8');
		}
		// in gambio v2.5.2.1 ml will displayed in iframe so no stuff to display from gambio backend
        if (version_compare($sVersion, '2.5.2.1', '>=') && !defined('ML_GAMBIO_USE_IFRAME')) {
			define('ML_GAMBIO_USE_IFRAME', true);
		}
	}

    // DB Update trigger for Gambio Cloud
    if (MagnaDB::gi()->recordExists(TABLE_MAGNA_CONFIG, array(
        'mpID' => 0,
        'mkey' => 'trigger.dbupdate',
        'value' => 'true',
    ))) {
        require_once(DIR_MAGNALISTER_FS.'MagnaUpdater.php');
        $mlUpdater = new MagnaUpdater(0, 0);
        $mlUpdater->updateDatabase();
        MagnaDB::gi()->update(TABLE_MAGNA_CONFIG, array(
            'value' => 'false'
        ), array(
            'mpID' => 0,
            'mkey' => 'trigger.dbupdate',
        ));
    }

	/* Language-Foo */
	$_magnaAvailableLanguages = magnaGetAvailableLanguages();
    if (defined('ML_GAMBIO_41_NEW_CONFIG_TABLE')) {
        $defaultLanguage = MagnaDB::gi()->fetchOne("
            SELECT `directory`
              FROM ".TABLE_LANGUAGES." l, ".TABLE_CONFIGURATION." c
             WHERE     l.`code` = c.`value`
                   AND c.`key` = 'configuration/DEFAULT_LANGUAGE'
             LIMIT 1
        ");
    } else {
        $defaultLanguage = MagnaDB::gi()->fetchOne('
            SELECT `directory`
              FROM '.TABLE_LANGUAGES.' l, '.TABLE_CONFIGURATION.' c
             WHERE c.`configuration_key` = "DEFAULT_LANGUAGE"
                   AND c.`configuration_value` = l.`code`
             LIMIT 1
        ');
    }
	if (in_array($defaultLanguage, $_magnaAvailableLanguages)) {
		$_magnaLanguage = $defaultLanguage;
	} else {
		$_magnaLanguage = array_first($_magnaAvailableLanguages);
	}

	include_once(DIR_MAGNALISTER_FS.'lang/'.$_magnaLanguage.'.php');
	/* Description of Modules */
	require_once(DIR_MAGNALISTER_FS_INCLUDES.'modules.php');
	/* Must be loaded after loading the language definitions. */
	require_once(DIR_MAGNALISTER_FS_INCLUDES . 'lib/magnaFunctionLib.php');
	require_once(DIR_MAGNALISTER_FS_INCLUDES . 'config.php');
	require_once(DIR_MAGNALISTER_FS_INCLUDES . 'lib/MagnaException.php');
	require_once(DIR_MAGNALISTER_FS_INCLUDES . 'lib/MagnaError.php');
	require_once(DIR_MAGNALISTER_FS_INCLUDES . 'lib/MagnaConnector.php');
	require_once(DIR_MAGNALISTER_FS_INCLUDES . 'lib/MLProduct.php');

	$_langISO = strtolower(magnaGetLanguageCode($_magnaLanguage));
	MagnaConnector::gi()->setLanguage($_langISO);

	require_once(DIR_MAGNALISTER_FS_CALLBACK . 'callbackFunctions.php');

	if (!defined('TABLE_ADMIN_ACCESS')) {
		define('TABLE_ADMIN_ACCESS', 'admin_access');
	}

	if (($localClientVersion = @file_get_contents(DIR_MAGNALISTER_FS.'ClientVersion')) !== false) {
		$localClientVersion = @json_decode($localClientVersion, true);
	}
	if (is_array($localClientVersion) && array_key_exists('CLIENT_VERSION', $localClientVersion)) {
		define('LOCAL_CLIENT_VERSION', $localClientVersion['CLIENT_VERSION']);
		define('CLIENT_BUILD_VERSION', $localClientVersion['CLIENT_BUILD_VERSION']);
	} else {
		define('LOCAL_CLIENT_VERSION', 0);
		define('CLIENT_BUILD_VERSION', 0);
	}

	/* Wenn DB noch nicht installiert, nix machen */
	if (!magnaInstalled(false)) {
		if (MAGNA_CALLBACK_MODE == 'STANDALONE') {
			echo 'magnalister database not installed yet';
		}
		return;
	}

	/* Wenn Modul nicht aktiviert, dann auch nix machen. */
	if (!magnaActivated()) {
		if (MAGNA_CALLBACK_MODE == 'STANDALONE') {
			echo 'magnalister not activated yet';
		}
		return;
	}

	loadDBConfig();

	/* The plugin noticed that it has no access to the service layer for multiple times.
	   Don't send any requests that are going to fail anyway.
	   However if magnaCallback is called stand alone try to access the service anyhow as this
	   won't slow any customers down.
	 */
	if ((bool)getDBConfigValue('CallbackAccessInterrupted', 0, false)) {
		if (MAGNA_CALLBACK_MODE == 'STANDALONE') {
			setDBConfigValue('CallbackAccessInterrupted', 0, false);
			echo 'CallbackAccessInterrupted';
		} else {
			return;
		}
	}

	magnaDetermineCurrentClientVersion();
	/* Do nothing if magnalister server is currently not available. */
	if (CURRENT_CLIENT_VERSION == 0) return;

	/* Check ob's kritisches Update gibt. Falls ja, nichts machen, Meldung ausgeben. */
	if (MAGNA_VERSION_TOO_OLD) {
		if (MAGNA_CALLBACK_MODE == 'STANDALONE') {
			echo 'magnalister version is too old. Please update.';
		}
		return;
	}

	loadJSONConfig();
	loadJSONConfig($_magnaLanguage);

	if ((MAGNA_CALLBACK_MODE == 'UTILITY') && !MAGNA_IN_ADMIN) {
		MagnaConnector::gi()->setTimeOutInSeconds(2);
	}

	if (!loadMaranonCacheConfig()) return;

	/* Wenn noch kein oder fehlerhafter PassPhrase hinterlegt: auch nix machen. */
	if (!magnaAuthed()) return;

	# verhindern dass sich die Datenbank mit Fehler 2006 verabschiedet
	if (class_exists('MagnaDB') && method_exists('MagnaDB','mysqlSetHigherTimeout')) {
		MagnaDB::gi()->mysqlSetHigherTimeout((MAGNA_CALLBACK_MODE == 'UTILITY') ? 60 * 60 : 60 * 60 * 2);
	}

	/* API-Artige Funktionalitaet */
	if ((MAGNA_CALLBACK_MODE == 'STANDALONE') &&
		array_key_exists('passphrase', $_POST) &&
		($_POST['passphrase'] == getDBConfigValue('general.passphrase', 0)) &&
		array_key_exists('function', $_POST)
	) {
		$arguments = array_key_exists('arguments', $_POST) ? unserialize($_POST['arguments']) : array();
		$arguments = is_array($arguments) ? $arguments : array();

		$includes = array_key_exists('includes', $_POST) ? unserialize($_POST['includes']) : array();
		$includes = is_array($includes) ? $includes : array();

		MagnaDB::gi()->setShowDebugOutput(false);

		echo magnaEncodeResult(magnaExecute($_POST['function'], $arguments, $includes));

		#ob_start(); /* Kein Output, nur ordendliches Beenden */
		#require_once('includes/application_bottom.php'); // Bindet oftmals jede menge mist ein den wir nicht gebrauchen koennen, der dann auseinander fallt, daher erst mal raus.
		#ob_end_clean();
		return;
	}

	ml_setMinRam('256M');

	/* Nur im Standalone-Modus zeitintensive Prozesse verarbeiten. */
	if (MAGNA_CALLBACK_MODE == 'STANDALONE') {
		if (!defined('MAGNA_EXECUTE_INSTEAD')) {
			require_once(DIR_MAGNALISTER_FS_CALLBACK.'callbackProcessor.php');
			magnaProcessCallbackRequest();
		} else {
			$magnaFunc = MAGNA_EXECUTE_INSTEAD;
			$magnaFunc();
		}
		#ob_start(); /* Kein Output, nur ordendliches Beenden */
		#require_once('includes/application_bottom.php'); // Selbe Grund wie weiter oben.
		#ob_end_clean();
	}

}

# Damit application_top oder was inkludiertes nicht meckert
if (!defined('_VALID_XTC')) {
	define('_VALID_XTC', true);
}

//modified - run callback processes also if shop is offline
if (!defined('_MODIFIED_SHOP_LOGIN')) {
    define('_MODIFIED_SHOP_LOGIN', 1);
}

# Modus festlegen
if (!defined('MAGNA_CALLBACK_MODE')) {
	if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__)) {
		define('MAGNA_CALLBACK_MODE', 'STANDALONE');
		header('Content-Type: text/plain; charset=utf-8');
	} else {
		define('MAGNA_CALLBACK_MODE', 'UTILITY');
	}
}

if (MAGNA_CALLBACK_MODE == 'STANDALONE') {
	define('MAGNA_IN_ADMIN', false);
	if (!in_array('application_top.php', preg_replace("/\/.*\//", "", get_included_files()))) {
		$_backup = array (
			'REQUEST' => $_REQUEST,
			'GET'     => $_GET,
			'POST'    => $_POST,
			'COOKIE'  => $_COOKIE
		);
		// Gambio 4.1 and newer doesn't like our language placeholder
		if (    !file_exists(dirname(__FILE__).'/GambioCore/Application/Kernel/Bootstrapper/Language/LanguageFinder.php')
		     && !file_exists(dirname(__FILE__).'/GambioCore/Language/Services/LanguageService.php')) {
			$_GET['language'] = '__notExistingLanguageToForceDefault__';
		}
		require_once('includes/application_top.php');

		/* Kein MagicQuotes mist mitmachen... */
		$_REQUEST = $_backup['REQUEST'];
		$_GET     = $_backup['GET'];
		$_POST    = $_backup['POST'];
		$_COOKIE  = $_backup['COOKIE'];

		unset($_backup);
	}
	header('Content-Type: text/plain; charset=utf-8');
} else {
	/* Where have we been called? Frontend or backend?! */
	if (!defined('DIR_FS_DOCUMENT_ROOT')) {
		define('DIR_FS_DOCUMENT_ROOT', str_replace('\\', '/', dirname(__FILE__)).'/');
	}
	if ((dirname($_SERVER['SCRIPT_FILENAME']).'/' == DIR_FS_DOCUMENT_ROOT) #browser
		|| (!isset($_SERVER['HTTP_USER_AGENT']) && isset($_SERVER['argv']) && !empty($_SERVER['argv']) && file_exists(getcwd().'/'.basename(__FILE__))) #cli
	) {
		/* Frontend */
		define('MAGNA_IN_ADMIN', false);
	} else {
		define('MAGNA_IN_ADMIN', true);
	}
}

magnaCallbackRun();

$_magnacallbacktimer = microtime(true) - $_magnacallbacktimer;
