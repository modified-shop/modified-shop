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
 * (c) 2010 - 2012 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('E_RECOVERABLE_ERROR') OR define('E_RECOVERABLE_ERROR', 0x1000);
defined('E_DEPRECATED')        OR define('E_DEPRECATED',        0x2000);
defined('E_USER_DEPRECATED')   OR define('E_USER_DEPRECATED',   0x4000);
defined('PHP_INT_MAX')         OR define('PHP_INT_MAX',     2147483647); // for PHP < 5.0.5

/* Developer defines */
if (file_exists(dirname(__FILE__).'/magnadevconf.php')) {
	require_once(dirname(__FILE__).'/magnadevconf.php');
}

/**
 * Defines
 */
defined('MAGNA_DEBUG')         OR define('MAGNA_DEBUG', false);
defined('MAGNA_SHOW_WARNINGS') OR define('MAGNA_SHOW_WARNINGS', false);
defined('MAGNA_SHOW_FATAL')    OR define('MAGNA_SHOW_FATAL', false);
define('MAGNALISTER_PLUGIN', true);

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

define('MAGNA_CALLBACK_MODE', 'UTILITY');
define('MAGNA_IN_ADMIN', true);

$_backup = array (
	'REQUEST' => $_REQUEST,
	'GET'     => $_GET,
	'POST'    => $_POST,
	'COOKIE'  => $_COOKIE
);

date_default_timezone_set(@date_default_timezone_get());

#ob_start(); # avoid output from broken files, so that our ajax requests don't fail.
require_once(dirname(__FILE__).'/includes/application_top.php');
#ob_end_clean();

/*
$constants = get_defined_constants(true);
print_r($constants['user']);
die();
*/

/* Kein MagicQuotes mist mitmachen... */
$_REQUEST = $_backup['REQUEST'];
$_GET     = $_backup['GET'];
$_POST    = $_backup['POST'];
$_COOKIE  = $_backup['COOKIE'];

unset($_backup);

// remove CSRF Token from magnalister post data -> used to store date directly into database
//  IdealoProductPrepare L58 saveProperties($_POST)
if (   array_key_exists('CSRFName', $_SESSION)
    && array_key_exists('CSRFToken', $_SESSION)
    && array_key_exists($_SESSION['CSRFName'], $_POST)
) {
    $_postCSRFBackup = $_POST;
    unset($_POST[$_SESSION['CSRFName']]);
}

/* Allow setting a different Update-Paths */
if (isset($_GET['UPDATE_PATH'])) {
	$_SESSION['magna_UPDATE_PATH'] = ltrim(rtrim($_GET['UPDATE_PATH'], '/'), '/').'/';
} else if (!isset($_SESSION['magna_UPDATE_PATH'])) {
	$_SESSION['magna_UPDATE_PATH'] = 'update/';
}

//define('FILENAME_MAGNALISTER', basename($_SERVER['SCRIPT_NAME']));
defined('MAGNA_SERVICE_URL') OR define('MAGNA_SERVICE_URL', 'http://api.magnalister.com/');
define('MAGNA_PUBLIC_SERVER', 'http://magnalister.com/');
define('MAGNA_PLUGIN_DIR', 'magnalister/');
define('DIR_MAGNALISTER_ABSOLUTE', dirname(__FILE__).'/');
define('DIR_MAGNALISTER', 'includes/'.MAGNA_PLUGIN_DIR);
	
if (defined('DIR_FS_EXTERNAL') && is_dir(DIR_FS_EXTERNAL.'magnalister/') && defined('DIR_WS_EXTERNAL')) {
	define('DIR_MAGNALISTER_FS', DIR_FS_EXTERNAL.'magnalister/');
	define('DIR_MAGNALISTER_WS', DIR_WS_EXTERNAL.'magnalister/');
} else {
	define('DIR_MAGNALISTER_FS', dirname(__FILE__).'/includes/magnalister/');
	define('DIR_MAGNALISTER_WS', 'includes/magnalister/');
}

define('MAGNA_UPDATE_PATH', $_SESSION['magna_UPDATE_PATH'].'oscommerce/');
defined('MAGNA_UPDATE_FILEURL') OR define('MAGNA_UPDATE_FILEURL', MAGNA_SERVICE_URL.MAGNA_UPDATE_PATH);
define('MAGNA_SUPPORT_URL', '<a href="'.MAGNA_PUBLIC_SERVER.'" title="'.MAGNA_PUBLIC_SERVER.'">'.MAGNA_PUBLIC_SERVER.'</a>');

#echo 'DIR_MAGNALISTER_FS: '.DIR_MAGNALISTER_FS."<br>\n";
#echo 'DIR_MAGNALISTER_WS: '.DIR_MAGNALISTER_WS."<br>\n";

if (MAGNA_SHOW_WARNINGS) {
	error_reporting(E_ALL | E_STRICT);
}

if (defined('PROJECT_VERSION') && !defined('_VALID_XTC')) {
	define('_VALID_XTC', true);
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

function echoDiePage($title, $content, $style = '', $showbacklink = true) {
	echo '<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>magnalister :: '.$title.'</title>
		<style>
			body { max-width: 600px; padding: 20px; font: 12px sans-serif; line-height: 16px; color: #333334;}
			h1{ font-size: 130%; letter-spacing: -0.5px; }
			a { color: #E31A1C; text-decoration: none; }
			a:hover { text-decoration: underline; }
			'.$style.'
		</style>
	</head>
	<body>
		<h1>'.$title.'</h1>
		<p>'.$content.'</p>
		'.(($showbacklink && isset($_SERVER['HTTP_REFERER']))
			? (($_SESSION['language'] == 'german') 
				? '<a href="'.$_SERVER['HTTP_REFERER'].'" title="Zur&uuml;ck">Zur&uuml;ck</a>'
				: '<a href="'.$_SERVER['HTTP_REFERER'].'" title="Back">Back</a>'
			)
			: ''
		).'
	</body>
</html>';
	include_once(DIR_WS_INCLUDES . 'application_bottom.php');
	exit();	
}

if (version_compare(PHP_VERSION, '5.0.0', '<')) {
	echoDiePage(
		(($_SESSION['language'] == 'german') ? 'PHP Version zu alt' : 'PHP version too old'),
		(($_SESSION['language'] == 'german') ?
			'Ihre PHP-Version ('.PHP_VERSION.') ist zu alt. Sie ben&ouml;tigen mindestens PHP Version 5.0 oder h&ouml;her.' :
			'Your PHP version ('.PHP_VERSION.') is too old. You need at least PHP version 5.0 or higher.'
		)
	);
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
		echo '';
		echoDiePage(
			(($_SESSION['language'] == 'german') ? 'Fehlerhafte Darstellung von Gleitkommazahlen' : 'Wrong representation of floats'),
			(($_SESSION['language'] == 'german')
				? 'Die Gleitkommazahlen wie 3.1415 werden mit einem "," statt einem "." dargestellt. Dieses Verhalten konnte nicht automatisch korrigiert werden. '.
				  'Bitte kontaktieren Sie Ihren Serveradministrator um dieses Verhalten abzustellen.'
				: 'Floats are represented with "," instead of ".". The behavior could not be changed. Please contact your administrator to fix this issue.'
			)
		);
	}
}
unset($str);

/* Alles ueber diesem Kommentar muss PHP 4 kompatibel sein! */
if (MAGNA_SAFE_MODE && !file_exists(DIR_MAGNALISTER_FS.'ClientVersion')) {
	echoDiePage(
		'Safe Mode '.(($_SESSION['language'] == 'german') ? 'Beschr&auml;nkung aktiv' : 'Restriction active'),
		(($_SESSION['language'] == 'german') ?
			'Die PHP Safe-Mode Beschr&auml;nkung auf Ihrem Server ist aktiv. Daher ist es nicht m&ouml;glich, automatische Updates zu machen. Um den magnalister manuell zu 
			 aktualisieren, laden Sie sich bitte die aktuelle Version aus dem
			 <a href="'.MAGNA_PUBLIC_SERVER.'" title="magnalister Seite">magnalister Download-Bereich</a> herunter, und entpacken den Ordner "files" aus dem ZIP-Archiv in das
			 Wurzelverzeichnis Ihres Shops. Kontaktieren Sie alternativ Ihren Server-Administrator und bitten Sie ihn, den Safe-Mode dauerhaft abzuschalten, um das Update per 	
			 Knopfdruck ausf&uuml;hren zu k&ouml;nnen. <br /><br />Gerne installieren wir Ihnen das manuelle Update auch gegen eine geringe Update-Pauschale (siehe http://www.magnalister.com/frontend/installation_pricing.php).' :
			'The PHP Save Mode restriction is active. That\'s why it is not possible to make automatic upgrades. To upgrade the magnalister manually, please
			 download the current version from <a href="'.MAGNA_PUBLIC_SERVER.'" title="magnalister.com">magnalister.com</a> and extract the contents
			 of the zip archive into the root directory of your shop or contact your server administrator and ask if the Safe Mode Restriction can be 
			 switched off permanently.'
		)
	);
}

if (!MAGNA_SAFE_MODE && !is_writable(DIR_MAGNALISTER_FS)) {
	echoDiePage(
		DIR_MAGNALISTER_WS.' '.(($_SESSION['language'] == 'german') ? 'kann nicht geschrieben werden' : 'is not writable'),
		(($_SESSION['language'] == 'german') ?
			'Das Verzeichnis <tt>'.DIR_MAGNALISTER_WS.'</tt> kann nicht vom Webserver geschrieben werden.<br/>
			 Dies ist allerdings zwingend notwendig um den magnalister verwenden zu k&ouml;nnen.' :
			'The directory <tt>'.DIR_MAGNALISTER_WS.'</tt> is not writable by the webserver.<br/>
			 This is however required to use the magnalister.'
		)
	);
}

$requiredFiles = array (
	'init.php',
	'MagnaUpdater.php'
);

if (!MAGNA_SAFE_MODE && MAGNA_DEBUG && isset($_GET['PurgeFiles'])) {
	$_SESSION['MagnaPurge'] = ($_GET['PurgeFiles'] == 'true') ? true : false;
} else {
	if (MAGNA_SAFE_MODE || !MAGNA_DEBUG || !isset($_SESSION['MagnaPurge'])) {
		$_SESSION['MagnaPurge'] = false;
	}
}

if (!MAGNA_SAFE_MODE) {
	foreach ($requiredFiles as $file) {
		$doDownload = (isset($_GET['update']) && ($_GET['update'] == 'true')) || ($_SESSION['MagnaPurge'] === true);
		$scriptPath = MAGNA_UPDATE_FILEURL.'magnalister/'.$file;
		if ($doDownload || !file_exists(DIR_MAGNALISTER_FS.$file)) {
			$scriptContent = fileGetContents($scriptPath, $foo, 30);
			if ($scriptContent === false) {
				echoDiePage(
					$scriptPath.' '.(
						($_SESSION['language'] == 'german') ? 
							'kann nicht geladen werden' : 
							'can\'t be loaded'
					),
					(($_SESSION['language'] == 'german') ?
						'Die Datei <tt>'.$scriptPath.'</tt> kann nicht heruntergeladen werden.' :
						'The File <tt>'.$scriptPath.'</tt> can not be downloaded.'
					)
				);
			}
		
			if (@file_put_contents(DIR_MAGNALISTER_FS.$file, $scriptContent) === false) {
				echoDiePage(
					DIR_MAGNALISTER_WS.$file.' '.(
						($_SESSION['language'] == 'german') ? 
							'kann nicht gespeichert werden' : 
							'can\'t be loaded'
					),
					(($_SESSION['language'] == 'german') ?
						'Die Datei <tt>'.DIR_MAGNALISTER_WS.$file.'</tt> kann nicht gespeichert werden.' :
						'The File <tt>'.DIR_MAGNALISTER_WS.$file.'</tt> can not be saved.'
					)
				);
			}
		}
	}
}

/**
 * Magnalister Core
 */
include_once(DIR_MAGNALISTER_FS.'init.php');

// restore post date of removed CSRF Token from magnalister post data -> used to store date directly into database L118
if (isset($_postCSRFBackup)) {
    $_POST = $_postCSRFBackup;
}

include_once(DIR_WS_INCLUDES.'application_bottom.php');
