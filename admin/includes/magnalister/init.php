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
 * $Id: init.php 2332 2013-04-04 16:12:19Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

defined('MAGNA_WITHOUT_AUTH') OR define('MAGNA_WITHOUT_AUTH', 0x00000004);

//The timestamp of the start of the request. Available since PHP 5.1.0. 
$_SERVER['REQUEST_TIME'] = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();

function outOfOrder() {
	require(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');
	echo '<img style="display: block; margin: 0 auto 1em auto;" src="'.DIR_MAGNALISTER_IMAGES.'out_of_order.png" alt="Out of Order" />';
	require(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
	require(DIR_WS_INCLUDES.'application_bottom.php');
	exit();
}

function decodeClientVersion($str) {
	$ret = array();
	
	if (!preg_match('/^\{([^\}]*)\}$/', $str, $match)) return $ret;
	if (!preg_match_all('/"([^\"]*)":"?([^\"]*)"?,/', $match[1].',', $match)) return $ret;

	foreach ($match[1] as $i => $key) {
		$ret[$key] = $match[2][$i];
	}
	return $ret;
}
function encodeClientVersion($arr) {
	$str = '';
	if (!is_array($arr) || empty($arr)) return '{}';
	$str = '{';
	foreach ($arr as $key => $value) {
		if (!is_int($value) && !ctype_digit($value)) {
			$value = '"'.(string)$value.'"';
		}
		$str .= '"'.$key.'":'.$value.',';
	}
	$str = rtrim($str, ',');
	return $str.'}';
}

/**
 * Diese Funktion ruft andere hier hinterlegte Funktionen auf. Sinn ist den zu
 * aendernden Code in Shop eigenen Scripten so gering wie moeglich zu halten.
 *
 * @param $functionName	Name der auszufuehrenden Funktion oder Aktion
 * @param $arguments	Assoziatives Array mit Parametern
 */
function magnaExecute($functionName, $arguments = array(), $includes = array(), $opts = 0) {
 	global $magnaConfig;
	if (!(
			(($opts & MAGNA_WITHOUT_AUTH) == MAGNA_WITHOUT_AUTH) || (
				isset($magnaConfig['maranon']['IsAccessAllowed']) 
				|| ($magnaConfig['maranon']['IsAccessAllowed'] == 'yes')
			)
		)
	) {
		return false;
	}
	if (!empty($includes)) {
		foreach ($includes as $incl) {
			require_once(DIR_MAGNALISTER_INCLUDES.'callback/'.$incl);
		}
	}
	if (function_exists($functionName)) {
		return $functionName($arguments);
	}
	return false;
}

date_default_timezone_set(@date_default_timezone_get());

$_executionTime = microtime(true);

/**
 * Defines
 */
define('DIR_MAGNALISTER_INCLUDES',   DIR_MAGNALISTER.'php/');
define('DIR_MAGNALISTER_MODULES',    DIR_MAGNALISTER_INCLUDES.'modules/');
define('DIR_MAGNALISTER_CALLBACK',   DIR_MAGNALISTER_INCLUDES.'callback/');
define('DIR_MAGNALISTER_CACHE',      DIR_MAGNALISTER.'cache/');
define('DIR_MAGNALISTER_IMAGECACHE', DIR_MAGNALISTER_CACHE.'images/');
define('DIR_MAGNALISTER_RESOURCE',   DIR_MAGNALISTER.'resource/');
define('DIR_MAGNALISTER_IMAGES',     DIR_MAGNALISTER.'images/');
define('DIR_MAGNALISTER_CONTRIBS',   DIR_MAGNALISTER.'contribs/');
define('DIR_MAGNALISTER_LOGS',       DIR_MAGNALISTER.'logs/');

if (isset($_GET['API'])) {
	$_SESSION['magnaAPI'] = ltrim(rtrim($_GET['API'], '/'), '/');
} else if (!isset($_SESSION['magnaAPI'])) {
	$_SESSION['magnaAPI'] = 'API';
}

define('MAGNA_API_SCRIPT', $_SESSION['magnaAPI'].'/');
define('MAGNA_APIRELATED', 'APIRelated/');

if (MAGNA_DEBUG) {
	define('MAGNA_DEBUG_TF', false);
}

/* Backwards compatibility */
if (!defined('MAGNA_PLUGIN_DIR')) define('MAGNA_PLUGIN_DIR', 'magnalister/');
if (!defined('MAGNA_SHOW_WARNINGS')) define('MAGNA_SHOW_WARNINGS', false);

/* Thumbsizes */
define('ML_THUMBS_MINI', 20);
define('ML_THUMBS_MATCHING', 80);

/* RAM, mit Einheit K, M oder G */
define('ML_DEFAULT_RAM', '256M');

if (isset($_GET['MLDEBUG']) && ($_GET['MLDEBUG'] == 'true')) {
	function ml_debug_out($m) {
		echo $m;
		flush();
	}
    /* MLDEBUG allein gibt ein Testlauf, fuer echten Lauf mit Ausgabe setze OPERATE
       (bisher nur bei eBay) */
    if (isset($_GET['OPERATE']) && ($_GET['OPERATE'] == 'true')) {
        define('OPERATE', true);
    }
}

if (isset($_GET['FIX_FILE_PERMISSIONS']) && ($_GET['FIX_FILE_PERMISSIONS'] == 'true')) {
	// fix file permissions for files added through auto update
	$fileList = file(DIR_MAGNALISTER.'files.list');
	foreach ($fileList as $flne) {
		$flne = explode("\t", $flne);
		echo $flne[0].'<br>';
		chmod(DIR_MAGNALISTER.$flne[0], 0777);
	}
}

if (MAGNA_DEBUG && isset($_GET['MagnaRAW'])) {
	$_SESSION['MagnaRAW'] = $_GET['MagnaRAW'];
}

if (isset($_GET['module']) && ($_GET['module'] == 'ajax') && isset($_GET['request']) && ($_GET['request'] == 'keepAlive')) {
	if (file_exists(DIR_MAGNALISTER_INCLUDES.'lib/MagnaDB.php')) {
		require_once(DIR_MAGNALISTER_INCLUDES.'lib/MagnaDB.php');
		MagnaDB::gi();
	}
	echo 'live!';
	include_once(DIR_WS_INCLUDES . 'application_bottom.php');
	exit();	
}

/* Abwaertskompatibilitaet zu aelteren magnalister Versionen */
if (!function_exists('fileGetContents')) {
	function fileGetContents($path, &$warnings = null, $timeout = -1) {
		return file_get_contents($path);
	}
}

/* fehlende PHP-Funktionen */

# Falls ctype ausgeschaltet (ja, das kommt vor)
if (!function_exists('ctype_digit')) {
	function ctype_digit($string) {
		return (boolean)preg_match('/^[0-9]*$/', $string);
	}
}

$_updaterTime = microtime(true);
require_once(DIR_MAGNALISTER.'MagnaUpdater.php');
if (file_exists(DIR_MAGNALISTER.'ftp.php')) {
	require_once(DIR_MAGNALISTER.'ftp.php');
}
$magnaUpdateErrorText = array();
$magnaUpdateErrorText['german'] = array(
	'headline' => 'Fehler bei automatischer Aktualisierung',
	'introduction' => 'Bei der automatischen Aktualisierung ihres magnalister Plugins sind folgende Fehler aufgetreten:',
	'label_file' => 'Datei',
	'label_error' => 'Fehler',
	'suggestions' => 'Versuchen Sie die Seite neu zu laden.',
	'persists' => 'Sollte das Problem weiterhin bestehen, wenden Sie sich an den Support von '.MAGNA_SUPPORT_URL.'.',
	MagnaUpdaterFailedOnLoadingFileList => 'Die Datei-Liste konnte nicht vom Update-Server geladen werden.',
	MagnaUpdaterFailedOnLoadingFile     => 'Die Datei konnte nicht vom Update-Server geladen werden.',
	MagnaUpdaterFailedOnWritingFile     => 'Die geladene Datei konnte nicht auf diesem Server gespeichert werden.',
	MagnaUpdaterSpecialFileListInvalid  => 'Die Datei-Liste ist fehlerhaft. Bitte wenden Sie sich an den Support von '.MAGNA_SUPPORT_URL.'.',
	MagnaUpdaterInvalidFTPAccess        => 'Die FTP-Zugangsdaten scheinen nicht mehr zu stimmen. Bitte korrigieren Sie die Daten in der Globalen
		Konfiguration bevor Sie ein automatisiertes Update ausf&uuml;hren.',
);
$magnaUpdateErrorText['other'] = array(
	'headline' => 'Error during automatic update process',
	'introduction' => 'Some errors occured during the automatic update procces of your mgnalister plugins:',
	'label_file' => 'File',
	'label_error' => 'Error',
	'suggestions' => 'Try to reload the page.',
	'persists' => 'If the error persists please contact the support of '.MAGNA_SUPPORT_URL.'.',
	MagnaUpdaterFailedOnLoadingFileList => 'The File-List couldn\'t be downloaded from the Update-Server.',
	MagnaUpdaterFailedOnLoadingFile     => 'The file couldn\'t be downloaded from the Update-Server.',
	MagnaUpdaterFailedOnWritingFile     => 'The downloaded File couldn\'t be saved on this server.',
	MagnaUpdaterSpecialFileListInvalid  => 'The File-List is invalid. Please contact contact the support of '.MAGNA_SUPPORT_URL.'.',
	MagnaUpdaterInvalidFTPAccess        => 'The FTP-Access Data seem to be invalid. Please correct the access data in Global Configuration before you
		continue with the automatic update procedure.',
);
$magnaFilePermissionErrors['german'] = array(
	'headline' => 'Fehler bei den Dateiberechtigungen',
	'introduction' => 'Bei der &Uuml;berpr&uuml;fung der Dateiberechtigungen wurde festgestellt, dass folgende Berechtigungen fehlerhaft gesetzt sind:',
	'label_file' => 'Datei',
	'label_error' => 'Fehler',
	'suggestions' => '',
	'persists' => 'Bitte setzen Sie die Rechte dieser Dateien und Verzeichnisse auf 777. <br />
		Hilfestellung zum richtigen Setzen von Dateiberechtigungen finden Sie auf der Support-Seite von '.MAGNA_SUPPORT_URL.'faq.',
	MagnaUpdaterDirectoryNotWritable    => 'In das Verzeichnis kann nicht geschrieben werden.',
	MagnaUpdaterNormalFileNotWritable   => 'Die Datei ist nicht schreibbar.',
	MagnaUpdaterSpecialFileNotWritable  => 'Die Datei ist nicht schreibbar.',
);
$magnaFilePermissionErrors['other'] = array(
	'headline' => 'Wrong File Permissions',
	'introduction' => 'The file permissions of the following files are set incorrectly:',
	'label_file' => 'File',
	'label_error' => 'Error',
	'suggestions' => '',
	'persists' => 'Please set the file permissions of these files to 777.<br />
		Additional information on how to set file permissions is given on the support page of '.MAGNA_SUPPORT_URL.'.',
	MagnaUpdaterDirectoryNotWritable    => 'The directory is not writable.',
	MagnaUpdaterNormalFileNotWritable   => 'The file is not writable.',
	MagnaUpdaterSpecialFileNotWritable  => 'The file is not writable.',
);

function updateErrorDiePage($errorText, $updaterErrors) {
	$errorText = $errorText[($_SESSION['language'] == 'german') ? 'german' : 'other'];
	$errorContent = '
		<p>'.$errorText['introduction'].'</p>
		<table class="updateError"><thead><tr><td>'.$errorText['label_file'].'</td><td>'.$errorText['label_error'].'</td></tr><tbody>
	';
	foreach ($updaterErrors as $error) {
		$errorContent .= '
			<tr><td>'.$error['file'].'</td>
				<td>'.$errorText[$error['error']].'</td></tr>
		';
	}
	$errorContent .= '
		</tbody></table>
		<p>'.$errorText['suggestions'].' '.$errorText['persists'].'</p>
	';

	$style = '
table.updateError td {
	padding: 1px 3px;
}
table.updateError thead td {
	border: 1px solid #999;
	background: #ccc;
	font-weight: bold;
	text-align: center
}
table.updateError tbody td {
	border: 1px solid #bbb;
	background: #eee;
}
';
	echoDiePage($errorText['headline'], $errorContent, $style);
}

$localClientVersion = false;
if (file_exists(DIR_MAGNALISTER.'ClientVersion') 
	&& (($localClientVersion = file_get_contents(DIR_MAGNALISTER.'ClientVersion')) !== false)
) {
	if (function_exists('json_decode')) {
		$localClientVersion = json_decode($localClientVersion, true);
	} else {
		$localClientVersion = decodeClientVersion($localClientVersion);
	}
}
if (!is_array($localClientVersion) || !array_key_exists('CLIENT_VERSION', $localClientVersion)) {
	$localClientVersion = array(
		'CLIENT_VERSION' => 0,
	);
}
/* 10s timeout. If the ClientVersion can't be fetched in under 10s, the server is probably to busy right now. */
if (($currentClientVersion = fileGetContents(
		MAGNA_UPDATE_FILEURL.'ClientVersion/'.$localClientVersion['CLIENT_VERSION'].'/', $warnings, 10
	)) === false
) {
	echoDiePage(
		(($_SESSION['language'] == 'german') ?
			'Keine Verbindung zum magnalister Server' :
			'Cannot connect to magnalister server'
		),
		(($_SESSION['language'] == 'german') ? 
			'Derzeit kann keine Verbindung zum Server aufgebaut werden - versuchen Sie es bitte in wenigen Momenten erneut. F&uuml;r Fragen wenden Sie sich bitte an unseren Support: <a href="mailto:support@magnalister.com">support@magnalister.com</a>'.(($warnings != '') ?
				('<br />PHP verursachte folgenden Fehler:<br />'.$warnings) : '') :
			'A connection to the magnalister server could not be established. Please try again in a minute. For further questions, contact our support: <a href="mailto:support@magnalister.com">support@magnalister.com</a>'.(($warnings != '') ?
	    		('<br />PHP encountered the following error:<br />'.$warnings) : '')
		)
	);
}

if (function_exists('json_decode')) {
	$currentClientVersion = json_decode($currentClientVersion, true);
} else {
	$currentClientVersion = decodeClientVersion($currentClientVersion);
}
if (!is_array($currentClientVersion)) {
	$currentClientVersion = array();
}

$_updatedSuccessfully = false;
$_mUpdater = new MagnaUpdater($currentClientVersion, $localClientVersion);

if (MAGNA_SAFE_MODE) {
	if (!$_mUpdater->checkMinimalFilePermissions()) {
		updateErrorDiePage($magnaFilePermissionErrors, $_mUpdater->getUpdaterAllErrors());		
	}
} else if (!MAGNA_SAFE_MODE && !file_exists(DIR_MAGNALISTER.'FilePermissionsOK')) {
	/* check EVERYTHING */
	if (!$_mUpdater->checkFilePermissions()) {
		/* Drop dead instantly */
		updateErrorDiePage($magnaFilePermissionErrors, $_mUpdater->getUpdaterAllErrors());
	} else {
		file_put_contents(DIR_MAGNALISTER.'FilePermissionsOK', 'OK');
	}
}

if (MAGNA_SAFE_MODE) {
	/* Load FTP-Data if available */
	$ftpAccess = array (
		'loginComplete' => false
	);
	if (file_exists(DIR_MAGNALISTER_INCLUDES.'lib/MagnaDB.php') 
		&& file_exists(DIR_MAGNALISTER_INCLUDES.'lib/json_wrapper.php')
		&& file_exists(DIR_MAGNALISTER_INCLUDES.'config.php')
	) {
		require_once(DIR_MAGNALISTER_INCLUDES.'lib/MagnaDB.php');
		require_once(DIR_MAGNALISTER_INCLUDES.'lib/json_wrapper.php');
		require_once(DIR_MAGNALISTER_INCLUDES.'config.php');

		if (MagnaDB::gi()->tableExists(TABLE_MAGNA_CONFIG) 
			&& MagnaDB::gi()->columnExistsInTable('mpID', TABLE_MAGNA_CONFIG)
		) {
			loadDBConfig(); /* Load configuration from database */
			$ftpAccess['host'] = getDBConfigValue('general.ftp.host', '0');
			$ftpAccess['port'] = getDBConfigValue('general.ftp.port', '0');
			$ftpAccess['username'] = getDBConfigValue('general.ftp.username', '0');
			$ftpAccess['password'] = getDBConfigValue('general.ftp.password', '0');
			$ftpAccess['path'] = getDBConfigValue('general.ftp.path', '0');
		}
	}

	if (class_exists('FTPConnect')
		&& array_key_exists('host', $ftpAccess) && !empty($ftpAccess['host'])
		&& array_key_exists('port', $ftpAccess) && !empty($ftpAccess['port'])
		&& array_key_exists('username', $ftpAccess) && !empty($ftpAccess['username'])
		&& array_key_exists('password', $ftpAccess) && !empty($ftpAccess['password'])
		&& array_key_exists('path', $ftpAccess) && !empty($ftpAccess['path'])
	) {
		$ftpAccess['loginComplete'] = true;
	}
}

if ((!MAGNA_SAFE_MODE || (MAGNA_SAFE_MODE && $ftpAccess['loginComplete'])) && 
		(!file_exists(DIR_MAGNALISTER.'ClientVersion') || (
			(isset($_GET['update']) && ($_GET['update'] == 'true')) || $_SESSION['MagnaPurge']
		))
) {
	if (MAGNA_SAFE_MODE && $ftpAccess['loginComplete']) {
		$_mUpdater->ftpAccess($ftpAccess);
	}
	$mangaUpdateState = $_mUpdater->update();
	if ($mangaUpdateState == MagnaUpdaterFailedOnUpdatingFiles) {
		/* hmmm... maybe file permissions? */
		@unlink(DIR_MAGNALISTER.'FilePermissionsOK');
		updateErrorDiePage($magnaUpdateErrorText, $_mUpdater->getUpdaterAllErrors());
	} else if ($mangaUpdateState == MagnaUpdaterInvalidFTPAccess) { 
		updateErrorDiePage($magnaUpdateErrorText, $_mUpdater->getUpdaterAllErrors());
	} else {
		$_updatedSuccessfully = true;
		$shopMod = trim(fileGetContents(
			MAGNA_UPDATE_FILEURL.'ShopChanges/from:'.$localClientVersion['CLIENT_VERSION'].'/to:'.$currentClientVersion['CLIENT_VERSION'].'/'
		));
		if ($shopMod == 'true') {
			define('MAGNA_SHOP_CHANGES', true);
		} else {
			define('MAGNA_SHOP_CHANGES', false);
		}
		$localClientVersion = $currentClientVersion;
		/* It updated. So everything was writable */
		file_put_contents(DIR_MAGNALISTER.'FilePermissionsOK', 'OK');
	}
}
if (!defined('MAGNA_SHOP_CHANGES')) {
	define('MAGNA_SHOP_CHANGES', false);	
}

define('LOCAL_CLIENT_VERSION', $localClientVersion['CLIENT_VERSION']);
if (array_key_exists('CLIENT_BUILD_VERSION', $localClientVersion) && ((int)$localClientVersion['CLIENT_BUILD_VERSION'] > 0)) {
	define('CLIENT_BUILD_VERSION', $localClientVersion['CLIENT_BUILD_VERSION']);
} else {
	define('CLIENT_BUILD_VERSION', false);
}

define('CURRENT_CLIENT_VERSION', $currentClientVersion['CLIENT_VERSION']);
define('MINIMUM_CLIENT_VERSION', $currentClientVersion['MIN_CLIENT_VERSION']);
if (array_key_exists('CLIENT_BUILD_VERSION', $currentClientVersion) && ((int)$currentClientVersion['CLIENT_BUILD_VERSION'] > 0)) {
	define('CURRENT_BUILD_VERSION', $currentClientVersion['CLIENT_BUILD_VERSION']);
} else {
	define('CURRENT_BUILD_VERSION', false);
}

if (file_exists(DIR_MAGNALISTER.'UpdaterError')) {
	$magnaUpdateErrorText['other']['headline'] = 'Error during last automatic update process';
	$magnaUpdateErrorText['other']['introduction'] = 'Some errors occured during the last automatic update procces of your mgnalister plugins:';
	$magnaUpdateErrorText['other']['suggestions'] = 'Click <a href="'.FILENAME_MAGNALISTER.'?update=true" title="restart the update process">here</a> to restart '.
						 							'the update process.';
	
	$magnaUpdateErrorText['german']['headline'] = 'Fehler bei letztmaliger automatischer Aktualisierung';
	$magnaUpdateErrorText['german']['introduction'] = 'Bei der letzten automatischen Aktualisierung ihres magnalister Plugins sind folgende Fehler aufgetreten:';
	$magnaUpdateErrorText['german']['suggestions'] = 'Klicken sie <a href="'.FILENAME_MAGNALISTER.'?update=true" title="Update-Vorang erneut starten">hier</a> '.
													 'um den Update-Vorgang erneut zu starten.';
													 
	$updaterErrors = unserialize(file_get_contents(DIR_MAGNALISTER.'UpdaterError'));
	updateErrorDiePage($magnaUpdateErrorText, $updaterErrors);
}

if (!MAGNA_SAFE_MODE && !file_exists(DIR_MAGNALISTER_CACHE)) {
	@mkdir(DIR_MAGNALISTER_CACHE, 0777, true);
} else if (!MAGNA_SAFE_MODE && !is_writable(DIR_MAGNALISTER_CACHE)) {
	@chmod(DIR_MAGNALISTER_CACHE, 0777);
}
if (!is_writable(DIR_MAGNALISTER_CACHE)) {
	echoDiePage(
		(($_SESSION['language'] == 'german') 
			? 'Cache Verzeichnis fehlt oder ist nicht schreibbar'
			: 'Cache directory is missing or not writeable'
		),
		(($_SESSION['language'] == 'german') 
			? (MAGNA_SAFE_MODE 
				? 'Aufgrund der Safe Mode Beschr&auml;nkung kann das Cache Verzeichnis 
			       (<tt>'.substr(DIR_WS_CATALOG.DIR_MAGNALISTER_CACHE, 1).'</tt>) nicht
	   		       erstellt und/oder schreibbar gemacht werden. 
	   		       Bitte erstellen Sie das Verzeichnis und stellen Sie sicher, dass es vom Webserver geschrieben werden kann.'
	   		    : 'Das Cache Verzeichnis (<tt>'.substr(DIR_WS_CATALOG.DIR_MAGNALISTER_CACHE, 1).'</tt>) konnte nicht
	   		       erstellt und/oder schreibbar gemacht werden. Bitte &uuml;berpr&uuml;fen Sie die Dateirechte des
	   		       magnalister Verzeichnisses und legen Sie gegebenenfalls das Cache Verzeichnis selbst an. Es muss
	   		       vom Webserver geschrieben werden k&ouml;nnen.'
	   		  )
			: (MAGNA_SAFE_MODE 
				? 'The cache directory (<tt>'.substr(DIR_WS_CATALOG.DIR_MAGNALISTER_CACHE, 1).'</tt>) 
			       cannot be created and/or made writable 
			       because of the Safe Mode restriction. 
			       Please create this directory and make sure it is writable by the webserver.'
	   		    : 'The  cache directory (<tt>'.substr(DIR_WS_CATALOG.DIR_MAGNALISTER_CACHE, 1).'</tt>) 
			       cannot be created and/or made writable. Please check the file permissions of the 
			       magnalister directory and create the cache directory if necessary. Make sure it is
			       writable by the webserver.'
	   		  )
	    )
	);
}
$_updaterTime = microtime(true) - $_updaterTime;

/**
 * Global includes and initialisation
 */
include_once(DIR_MAGNALISTER_INCLUDES.'identifyShop.php');
if (defined('DIR_FS_CATALOG_ORIGINAL_IMAGES')) {
	define('SHOP_FS_PRODUCT_IMAGES',  DIR_FS_CATALOG_ORIGINAL_IMAGES);
	define('SHOP_FS_PRODUCT_THUMBNAILS',  DIR_FS_CATALOG_THUMBNAIL_IMAGES);
	define('SHOP_FS_CATEGORY_IMAGES', DIR_FS_CATALOG_IMAGES.'categories/');
	define('SHOP_FS_MANUFACTURES_IMAGES', DIR_FS_CATALOG_IMAGES.'manufacturers/');

	define('SHOP_FS_POPUP_IMAGES', DIR_FS_CATALOG_POPUP_IMAGES);
	define('SHOP_URL_POPUP_IMAGES', HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES);
} else {
	define('SHOP_FS_PRODUCT_IMAGES',  DIR_FS_CATALOG.DIR_WS_IMAGES);
	define('SHOP_FS_PRODUCT_THUMBNAILS',  DIR_FS_CATALOG.DIR_WS_IMAGES); # osCommerce does not provide thumbnails
	define('SHOP_FS_CATEGORY_IMAGES', DIR_FS_CATALOG.DIR_WS_IMAGES);
	define('SHOP_FS_MANUFACTURES_IMAGES', DIR_FS_CATALOG.DIR_WS_IMAGES);

	define('SHOP_FS_POPUP_IMAGES', DIR_FS_CATALOG.DIR_WS_IMAGES);
	define('SHOP_URL_POPUP_IMAGES', HTTP_CATALOG_SERVER.DIR_WS_CATALOG_IMAGES);
}

require_once(DIR_MAGNALISTER_INCLUDES.'lib/json_wrapper.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/MagnaDB.php');
$magnaDB = MagnaDB::gi();	/* Database Connector */

/* Detect products_ean-like field if it exists. */
$productsFields = array_flip((array)MagnaDB::gi()->getTableCols(TABLE_PRODUCTS));
if (is_array($productsFields)) {
	$eanTypes = array (
		'products_ean',
		'products_barcode',
	);
	foreach ($eanTypes as $eanType) {
		if (array_key_exists($eanType, $productsFields)) {
			define('MAGNA_FIELD_PRODUCTS_EAN', $eanType);
			break;
		}
	}
}
/* Detect attributes_ean-like field if it exists. */
$attributesFields = array_flip((array)MagnaDB::gi()->getTableCols(TABLE_PRODUCTS_ATTRIBUTES));
if (is_array($attributesFields)) {
	$eanTypes = array (
		'attributes_ean',
		'gm_ean',
	);
	foreach ($eanTypes as $eanType) {
		if (array_key_exists($eanType, $attributesFields)) {
			define('MAGNA_FIELD_ATTRIBUTES_EAN', $eanType);
			break;
		}
	}
}

require_once(DIR_MAGNALISTER_INCLUDES.'lib/functionLib.php');

/* Update the database */
$_dbUpdateErrors = null;
if (MAGNA_SAFE_MODE || $_updatedSuccessfully || isset($_GET['dbupdate']) || !MagnaDB::gi()->tableExists(TABLE_MAGNA_CONFIG)) {
	$_dbUpdateErrors = $_mUpdater->updateDatabase();
}

/* Language-Foo */
$_magnaAvailableLanguages = magnaGetAvailableLanguages();
if (in_array($_SESSION['language'], $_magnaAvailableLanguages)) {
	$_magnaLanguage = $_lang = $_SESSION['language'];
} else {
	$_magnaLanguage = $_lang = array_first($_magnaAvailableLanguages);
}
$_langISO = strtolower(magnaGetLanguageCode($_lang));
@include_once(DIR_MAGNALISTER.'lang/'.$_lang.'.php');

if (!array_key_exists('languages_id', $_SESSION) || empty($_SESSION['languages_id'])) {
	$_SESSION['languages_id'] = MagnaDB::gi()->fetchOne(
		'SELECT languages_id '.
		'FROM '.TABLE_LANGUAGES.' l, '.TABLE_CONFIGURATION.' c '.
		'WHERE l.code=c.configuration_value '.
		'AND c.configuration_key=\'DEFAULT_LANGUAGE\'');
}

/* Title of page */
$_mainTitle = '';

/* Description of Modules */
include_once(DIR_MAGNALISTER_INCLUDES.'modules.php');
/* Must be loaded after loading the language definitions. */
require_once(DIR_MAGNALISTER_INCLUDES.'lib/magnaFunctionLib.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/BrowserDetect.php');

/* Zwingend notwendiges Update aufgrund von API-Inkomartibiliaeten? */
if (version_compare(CURRENT_CLIENT_VERSION, LOCAL_CLIENT_VERSION, '>') && version_compare(MINIMUM_CLIENT_VERSION, LOCAL_CLIENT_VERSION, '>')) {
	$_MagnaSession['currentPlatform'] = '';
	$_mainTitle = ' - '.ML_HEADLINE_UPDATE;
	
	if (!MAGNA_SAFE_MODE) {
		preg_match('~#(.*)#~', ML_TEXT_IMPORTANT_UPDATE, $matches);
		$content = '
			<h2>'.ML_HEADLINE_UPDATE.'</h2>
			<p class="successBox">
				'.sprintf(str_replace(
					$matches[0],
					'<a href="'.toUrl(array('update' => 'true')).'" title="Update">'.$matches[1].'</a>',
					ML_TEXT_IMPORTANT_UPDATE
				), CURRENT_CLIENT_VERSION).'
			</p>';
	} else {
		$content = '
			<h2>'.ML_HEADLINE_UPDATE.'</h2>
			<p class="successBox">
				'.sprintf(ML_TEXT_IMPORTANT_UPDATE_SAFE_MODE, CURRENT_CLIENT_VERSION).'
			</p>';
	}
	shopAdminDiePage($content);
}

$_url = array();

/* JavaScript is ABSOLUTELY required! */
if (isset($_GET['module']) && ($_GET['module'] == 'nojs')) {
	shopAdminDiePage(ML_ERROR_NO_JAVASCRIPT);
}

if (   MLBrowserDetect::gi()->is(array ('Browser' => 'firefox', 'BVersion' => '< 3.0'))
	|| MLBrowserDetect::gi()->is(array ('Browser' => 'msie', 'BVersion' => '< 7.0'))
	|| MLBrowserDetect::gi()->is(array ('Browser' => 'opera', 'BVersion' => '< 9.0'))
) {
//	shopAdminDiePage(ML_ERROR_OLD_BROWSER);
}

/* RAM Check. Wenn RAM Begrenzung zu klein ist, wird diese erhoeht. 
 * Idr wird nur bei ImageResize Operationen mehr RAM benoetigt, falls 
 * die Produktbider zu gross sind. */
magnaFixRamSize();

/* Kein Error-Handling da DB Fehler immer Fatal */
//echo print_m($_dbUpdateErrors, 'updateDatabase');

require_once(DIR_MAGNALISTER_INCLUDES.'config.php');
loadDBConfig();				/* Load configuration from database */

require_once(DIR_MAGNALISTER_INCLUDES.'lib/MagnaException.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/MagnaError.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/MagnaConnector.php');
MagnaConnector::gi()->setLanguage($_langISO);
$_mConnect = MagnaConnector::gi();

$requiredConfigKeys = array (
	'general.passphrase',
	'general.keytype',
	'general.stats.backwards',
	'general.callback.importorders',
);

/* Is magic_quotes on? */
if (get_magic_quotes_gpc()) {
	/* Strip the added slashes */
	$_REQUEST = arrayMap('stripslashes', $_REQUEST);
	$_GET     = arrayMap('stripslashes', $_GET);
	$_POST    = arrayMap('stripslashes', $_POST);
	$_COOKIE  = arrayMap('stripslashes', $_COOKIE);
}

/**
 * Gobal verfuegbare Variablen:
 */
$_js = array();
$_magnaQuery = array();

/* ViewPages */
if (isset($_GET['module']) && in_array($_GET['module'], array(
	'viewchangelog', 'fixcollations', 'fixorderstotal',
	'toolbox', 'viewdbtables', 'sql', 'simpletest',
))) {
	if ($_GET['module'] == 'sql') {
		$_GET['module'] = 'viewdbtables';
		$_GET['view'] = 'sql';
	}
	if (file_exists(DIR_MAGNALISTER_MODULES.$_GET['module'].'.php')) {
		$_url['module'] = $_magnaQuery['module'] = $_GET['module'];
		include_once(DIR_MAGNALISTER_MODULES.$_GET['module'].'.php');
	}
}

/* Statistics */
$globalStats = array(
	'orders' => array(
		'url' => array('module' => 'stats', 'view' => 'orders'),
		'title' => ML_LABEL_STATS_ORDERS_PER_MARKETPLACE,
	),
	'ordersPercent' => array(
		'url' => array('module' => 'stats', 'view' => 'ordersPercent'),
		'title' => ML_LABEL_STATS_ORDERS_PER_MARKETPLACE_PERCENT,
	),
);
$globalStatSize = array('h' => 200, 'w' => 400);
if (isset($_GET['module']) && ($_GET['module'] == 'stats')) {
	include_once(DIR_MAGNALISTER_MODULES.'stats/main.php');
}



if (isset($_GET['fixProductsModel']) && ($_GET['fixProductsModel'] == 'true')) {
	generateUniqueProductModels();
}

$forceConfigView = false;
# SKU == products_model: Fehlermeldung wenn nicht ueberall gefuellt und unique
if (getDBConfigValue('general.keytype', '0', 'pID') == 'artNr') {
	$countProductsIDs = MagnaDB::gi()->fetchOne('
		SELECT COUNT(DISTINCT products_id) FROM '.TABLE_PRODUCTS
	);
	$countProductsModels = MagnaDB::gi()->fetchOne('
		SELECT COUNT(DISTINCT products_model) FROM '.TABLE_PRODUCTS.' WHERE products_model <> \'\' AND products_model IS NOT NULL'
	);
	if ($countProductsIDs != $countProductsModels) {
		$forceConfigView = '<p class="errorBox">'.str_replace(
			'#LINK#', 
			toURL(array('module' => 'configuration', 'fixProductsModel' => 'true')),
			ML_GENERIC_ERROR_PRODUCTS_WITHOUT_MODEL_EXIST
		).'</p>';
	}
}

/* If the PassPhrase is not set in the database show the global config */
if (!allRequiredConfigKeysAvailable($requiredConfigKeys, '0') || ($forceConfigView !== false)) {
	/* Send the user to the configuration panel */
	$_url['module'] = $_GET['module'] = $_magnaQuery['module'] = 'configuration';
	$_MagnaSession['currentPlatform'] = '';
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');
	include_once(DIR_MAGNALISTER_MODULES.'configuration.php');
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
	include_once(DIR_WS_INCLUDES . 'application_bottom.php');
	exit();
}

/* Don't try to authenticate if the PassPhrase is going to be set */
if (!isset($_POST['conf']['general.passphrase']) && !loadMaranonCacheConfig() 
    && (!isset($_GET['module']) || ($_GET['module'] != 'configuration'))
) {
	$_mainTitle = ' - '.ML_ERROR_CANNOT_CONNECT_TO_SERVICE_LAYER_HEADLINE;
	$accessDenied = isset($magnaConfig['maranon']['IsAccessAllowed']) && ($magnaConfig['maranon']['IsAccessAllowed'] == 'no');
	shopAdminDiePage('
		<h2>'.ML_ERROR_CANNOT_CONNECT_TO_SERVICE_LAYER_HEADLINE.'</h2>
		'.($accessDenied
			? '<p>'. ML_ERROR_ACCESS_DENIED_TO_SERVICE_LAYER_TEXT.'</p>'
			: '<p>'. ML_ERROR_CANNOT_CONNECT_TO_SERVICE_LAYER_TEXT.'</p>'
		).'
	');
}

/* No modules are available (usually the case when the PassPhrase is wrong) or global config is requested.
   Let's go to the global config page */
if (!isset($magnaConfig['maranon']['Marketplaces']) || empty($magnaConfig['maranon']['Marketplaces'])) {
	$_GET['module'] = 'configuration';
}
if (isset($_GET['module']) && array_key_exists($_GET['module'], $_modules) 
	&& ($_modules[$_GET['module']]['type'] == 'system')
	&& file_exists(DIR_MAGNALISTER_MODULES.$_GET['module'].'.php')
) {
	/* Send the user to the configuration panel */
	$_url['module'] = $_magnaQuery['module'] = $_GET['module'];
	$_MagnaSession['currentPlatform'] = '';
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');
	include_once(DIR_MAGNALISTER_MODULES.$_GET['module'].'.php');
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
	include_once(DIR_WS_INCLUDES . 'application_bottom.php');
	exit();
}

loadJSONConfig();
loadJSONConfig($_lang);

/* Testpages */
if (isset($_GET['module']) && in_array($_GET['module'], array('apitest', 'generictests', 'SimpleCategoryView'))) {
	$_url['module'] = $_GET['module'];
	include_once(DIR_MAGNALISTER_MODULES.$_GET['module'].'.php');
}

/* No requests older than 24h */
MagnaDB::gi()->query('
	DELETE FROM '.TABLE_MAGNA_API_REQUESTS.'
	 WHERE `date` < \''.date('Y-m-d H:i:s', time() - 60 * 60 * 24).'\'
');
if (($allRequests = MagnaDB::gi()->fetchArray('SELECT * FROM '.TABLE_MAGNA_API_REQUESTS)) !== false) {
	foreach ($allRequests as $request) {
		$request['data'] = unserialize($request['data']);
		try {
			MagnaConnector::gi()->submitRequest($request['data']);
		} catch (MagnaException $e) {
			//echo print_m($e->getErrorArray());
		}
		MagnaDB::gi()->delete(TABLE_MAGNA_API_REQUESTS, array('id' => $request['id']));
		//echo print_m($request);
	}
}

if (isset($_GET['do'])) {
	require_once(DIR_MAGNALISTER_CALLBACK.'callbackProcessor.php');
	magnaProcessCallbackRequest();
}
magnaFixOrders();
if (!isset($_SESSION['magnaRunOnce']) || isset($_GET['magnaRunOnce'])) {
    $_SESSION['magnaRunOnce'] = true;
    cleanPrepareData();
}

$GLOBALS['MagnaAjax'] = (isset($_GET['kind']) && ($_GET['kind'] == 'ajax'));

if (array_key_exists('mp', $_GET) && array_key_exists($_GET['mp'], $magnaConfig['maranon']['Marketplaces'])
	&& ($mp = $magnaConfig['maranon']['Marketplaces'][$_GET['mp']])
	&& array_key_exists($mp, $_modules)
) {
	$_MagnaSession['mpID'] = $_GET['mp'];
	$_MagnaSession['currentPlatform'] = $mp;

	$_magnaQuery['module'] = $_MagnaSession['currentPlatform'];
	$_url = array('mp' => $_MagnaSession['mpID']);

	include_once(DIR_MAGNALISTER_MODULES.$_MagnaSession['currentPlatform'].'.php');

} else {
	if (isset($_GET['module']) && array_key_exists($_GET['module'], $_modules)) {
		$_url['module'] = $_GET['module'];
		if ($_GET['module'] == 'more') {
			$_mainTitle = ' - '.ML_HEADLINE_MORE_MODULES;
			shopAdminDiePage('
				<h2>'.ML_HEADLINE_MORE_MODULES.'</h2>
				<p>'.ML_TEXT_MORE_MODULES.'</p>
			');
		} else {
			$_mainTitle = ' - '.ML_HEADLINE_NOT_YET_BOOKED;
			shopAdminDiePage('
				<h2>'.ML_HEADLINE_NOT_YET_BOOKED.'</h2>
				<p>'.sprintf(ML_TEXT_CURRENT_MODULE_NOT_BOOKED, $_modules[$_GET['module']]['title']).'</p>
			');
		}
	} else {
		$marketingText = fileGetContents(MAGNA_SERVICE_URL.MAGNA_APIRELATED.'Marketing/', $warings, 10);
		$marketingText = !empty($marketingText) ? '<div class="marketing">'.$marketingText.'</div>' : '';

		$_mainTitle = ' - '.ML_HEADLINE_WELCOME;
		$welcomeHTML = '
			<h2>'.ML_HEADLINE_WELCOME.'</h2>
			<p>'.ML_TEXT_MAKE_YOUR_CHOISE.'</p>';

		if (!empty($globalStats)) {
			$welcomeHTML .= '
				<h2>'.ML_HEADLINE_STATS.'</h2>
				<div id="stats">';
			if (!function_exists('imagecreatetruecolor')) {
				$welcomeHTML .= '<b class="noticeBox">'.ML_ERROR_GD_LIB_MISSING.'</b>';
			} else {
				foreach ($globalStats as $stat) {
					$welcomeHTML .= '
						<div class="stat" title="'.$stat['title'].'">
							<img width="'.$globalStatSize['w'].'" height="'.$globalStatSize['h'].'" alt="'.$stat['title'].'" src="'.toURL($stat['url']).'"/>
						</div>';
				}
			}
			$welcomeHTML .= '
				<div class="visualClear"></div>
				</div>';
		}
		$welcomeHTML .= '
			'.$marketingText;
		
		shopAdminDiePage($welcomeHTML);
	}
}
