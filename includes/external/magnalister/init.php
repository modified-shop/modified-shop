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
 * $Id: init.php 6806 2016-07-19 09:59:44Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

defined('MAGNA_WITHOUT_AUTH') OR define('MAGNA_WITHOUT_AUTH', 0x00000004);

//The timestamp of the start of the request. Available since PHP 5.1.0. 
$_SERVER['REQUEST_TIME'] = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();

// Do not enable unless you know what you are doing!
// Might break things.
define('MAGNA_SECRET_DEV', MAGNA_DEBUG && (strpos($_SERVER['HTTP_HOST'], 'magnalister.') !== false));

defined('MAGNA_DEV_PRODUCTLIST') OR define('MAGNA_DEV_PRODUCTLIST', true);

// backwards compat
defined('DIR_MAGNALISTER_FS') OR define('DIR_MAGNALISTER_FS', DIR_MAGNALISTER);
defined('DIR_MAGNALISTER_WS') OR define('DIR_MAGNALISTER_WS', DIR_MAGNALISTER);

// for Gambio 4.1 and newer
if (TABLE_CONFIGURATION == 'gx_configurations') {
    define('ML_GAMBIO_41_NEW_CONFIG_TABLE', true);
}

if (!array_key_exists('language', $_SESSION)) $_SESSION['language'] = 'english';

function outOfOrder() {
	require(DIR_MAGNALISTER_FS_INCLUDES.'admin_view_top.php');
	echo '<img style="display: block; margin: 0 auto 1em auto;" src="'.DIR_MAGNALISTER_WS_IMAGES.'out_of_order.png" alt="Out of Order" />';
	require(DIR_MAGNALISTER_FS_INCLUDES.'admin_view_bottom.php');
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
 * @param string $functionName Name der auszufuehrenden Funktion oder Aktion
 * @param array $arguments Assoziatives Array mit Parametern
 * @param array $includes
 * @param int $opts
 * @return bool
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
			require_once(DIR_MAGNALISTER_FS_INCLUDES.'callback/'.$incl);
		}
	}
	if (function_exists($functionName)) {
		return $functionName($arguments);
	}
	return false;
}

function updateErrorDiePage($errorText, $updaterErrors) {
	#print_r(func_get_args());
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

function mlGetLocalClientVersion() {
	$version = false;
	if (file_exists(DIR_MAGNALISTER_FS.'ClientVersion') 
		&& (($version = file_get_contents(DIR_MAGNALISTER_FS.'ClientVersion')) !== false)
	) {
		if (function_exists('json_decode')) {
			$version = json_decode($version, true);
		} else {
			$version = decodeClientVersion($version);
		}
	}
	if (!is_array($version) || !array_key_exists('CLIENT_VERSION', $version)) {
		$version = array(
			'CLIENT_VERSION' => 0,
		);
	}
	return $version;
}

function mlGetCurrentClientVersion($localVersion = 'unknown') {
	$version = false;
	/* 10s timeout. If the ClientVersion can't be fetched in under 10s, the server is probably to busy right now. */
	if (($version = fileGetContents(
			MAGNA_UPDATE_FILEURL.'ClientVersion/'.$localVersion.'/', $warnings, 10
		)) === false
	) {
		echoDiePage(
			(($_SESSION['language'] == 'german')
				? 'Keine Verbindung zum magnalister Server'
				: 'Cannot connect to magnalister server'
			),
			(($_SESSION['language'] == 'german')
				? 'Derzeit kann keine Verbindung zum Server aufgebaut werden - versuchen Sie es bitte in wenigen Momenten erneut. F&uuml;r Fragen wenden Sie sich bitte an unseren Support: <a href="mailto:support@magnalister.com">support@magnalister.com</a>'.(
					($warnings != '') ? ('<br />PHP verursachte folgenden Fehler:<br />'.$warnings) : ''
				)
				: 'A connection to the magnalister server could not be established. Please try again in a minute. For further questions, contact our support: <a href="mailto:support@magnalister.com">support@magnalister.com</a>'.(
					($warnings != '') ? ('<br />PHP encountered the following error:<br />'.$warnings) : ''
				)
			)
		);
	}
	
	if (function_exists('json_decode')) {
		$version = json_decode($version, true);
	} else {
		$version = decodeClientVersion($version);
	}
	if (!is_array($version)) {
		$version = array();
	}
	return $version;
}

function mlGetUpdateErrorTexts() {
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
		MagnaUpdaterSafeMode                => 'Ein Update ist durch die Safe Mode Beschr&auml;nkung nicht m&ouml;glich.',
		MagnaUpdaterDirectoryNotWritable    => 'In das Verzeichnis kann nicht geschrieben werden.',
		MagnaUpdaterFileNotWritable         => 'Die Datei ist nicht schreibbar.',
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
		MagnaUpdaterSafeMode                => 'An update is not possible due to the safe mode restriction.',
		MagnaUpdaterDirectoryNotWritable    => 'The directory is not writable.',
		MagnaUpdaterFileNotWritable         => 'The file is not writable.',
	);
	return $magnaUpdateErrorText;
}

function mlPrintLastUpdateError() {
	if (file_exists(DIR_MAGNALISTER_FS.'UpdaterError')) {
		$magnaUpdateErrorText = mlGetUpdateErrorTexts();
		$magnaUpdateErrorText['other']['headline'] = 'Error during last automatic update process';
		$magnaUpdateErrorText['other']['introduction'] = 'Some errors occured during the last automatic update procces of your mgnalister plugins:';
		$magnaUpdateErrorText['other']['suggestions'] = 'Click <a href="'.FILENAME_MAGNALISTER.'?update=true" title="restart the update process">here</a> to restart '.
							 							'the update process.';
	
		$magnaUpdateErrorText['german']['headline'] = 'Fehler bei letztmaliger automatischer Aktualisierung';
		$magnaUpdateErrorText['german']['introduction'] = 'Bei der letzten automatischen Aktualisierung ihres magnalister Plugins sind folgende Fehler aufgetreten:';
		$magnaUpdateErrorText['german']['suggestions'] = 'Klicken sie <a href="'.FILENAME_MAGNALISTER.'?update=true" title="Update-Vorang erneut starten">hier</a> '.
														'um den Update-Vorgang erneut zu starten.';
	
		$updaterErrors = unserialize(file_get_contents(DIR_MAGNALISTER_FS.'UpdaterError'));
		updateErrorDiePage($magnaUpdateErrorText, $updaterErrors);
	}
}

/**
 * @param $mUpdater MagnaUpdater
 * @param $currentVersion
 * @param $localVersion
 * @return false[]
 */
function mlUpdatePlugin($mUpdater, $currentVersion, $localVersion) {
	$magnaUpdateErrorText = mlGetUpdateErrorTexts();
	$magnaFilePermissionErrors['german'] = array(
		'headline' => 'Fehler bei den Dateiberechtigungen',
		'introduction' => 'Bei der &Uuml;berpr&uuml;fung der Dateiberechtigungen wurde festgestellt, dass folgende Berechtigungen fehlerhaft gesetzt sind:',
		'label_file' => 'Datei',
		'label_error' => 'Fehler',
		'suggestions' => '',
		'persists' => 'Bitte setzen Sie die Rechte dieser Dateien und Verzeichnisse auf 777. <br />
			Hilfestellung zum richtigen Setzen von Dateiberechtigungen finden Sie auf der Support-Seite von '.MAGNA_SUPPORT_URL.'faq.',
		MagnaUpdaterDirectoryNotWritable    => 'In das Verzeichnis kann nicht geschrieben werden.',
		MagnaUpdaterFileNotWritable         => 'Die Datei ist nicht schreibbar.',
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
		MagnaUpdaterFileNotWritable         => 'The file is not writable.',
	);
	
	$status = array(
		'UpdatedSuccessfully' => false,
		'RequiresInstallationUpdate' => false,
	);
	
	// If you want to disable automatic updates uncomment the following line:
	// return $status;
	
	if (MAGNA_SAFE_MODE) {
		if (!$mUpdater->checkMinimalFilePermissions()) {
			updateErrorDiePage($magnaFilePermissionErrors, $mUpdater->getUpdaterAllErrors());
		}
	} else if (!MAGNA_SAFE_MODE && !file_exists(DIR_MAGNALISTER_FS.'FilePermissionsOK')) {
		/* check EVERYTHING */
		if (!$mUpdater->checkFilePermissions()) {
			/* Drop dead instantly */
			updateErrorDiePage($magnaFilePermissionErrors, $mUpdater->getUpdaterAllErrors());
		} else {
			file_put_contents(DIR_MAGNALISTER_FS.'FilePermissionsOK', 'OK');
		}
	}
	
	if (!MAGNA_SAFE_MODE
		&& (!file_exists(DIR_MAGNALISTER_FS.'ClientVersion')
			|| ((isset($_GET['update']) && ($_GET['update'] == 'true')) || $_SESSION['MagnaPurge'])
		)
	) {
		$mangaUpdateState = $mUpdater->update();
		if ($mangaUpdateState == MagnaUpdaterFailedOnUpdatingFiles) {
			/* hmmm... maybe file permissions? */
			@unlink(DIR_MAGNALISTER_FS.'FilePermissionsOK');
			updateErrorDiePage($magnaUpdateErrorText, $mUpdater->getUpdaterAllErrors());
		} else if ($mangaUpdateState == MagnaUpdaterSafeMode) { 
			updateErrorDiePage($magnaUpdateErrorText, $mUpdater->getUpdaterAllErrors());
		} else {
			$status['UpdatedSuccessfully'] = true;
			$shopMod = trim(fileGetContents(
				MAGNA_UPDATE_FILEURL.'ShopChanges/from:'.$localVersion['CLIENT_VERSION'].'/to:'.$currentVersion['CLIENT_VERSION'].'/'
			));
			if ($shopMod == 'true') {
				$status['RequiresInstallationUpdate'] = true;
			}
			/* It updated. So everything was writable */
			file_put_contents(DIR_MAGNALISTER_FS.'FilePermissionsOK', 'OK');
		}
	}
	
	mlPrintLastUpdateError();
	
	return $status;
}

function mlIsCacheDirWritable() {
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
}

function mlFixFilePermissions() {
	if (isset($_GET['FIX_FILE_PERMISSIONS']) && ($_GET['FIX_FILE_PERMISSIONS'] == 'true')) {
		// fix file permissions for files added through auto update
		$fileList = file(DIR_MAGNALISTER_FS.'files.list');
		foreach ($fileList as $flne) {
			$flne = explode("\t", $flne);
			echo $flne[0].'<br>';
			chmod(DIR_MAGNALISTER_FS.$flne[0], 0777);
		}
	}
}

function mlDetectShopFeatures() {
	// Detect products_ean-like field if it exists.
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
	
	// Detect attributes_ean-like field if it exists.
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
	
	// Detect Gambio GX2.1 property tables.
	define('MAGNA_GAMBIO_VARIATIONS',
		MagnaDB::gi()->tableExists('products_properties_combis')
		&& MagnaDB::gi()->columnExistsInTable('combi_ean', 'products_properties_combis')
	);
}

date_default_timezone_set(@date_default_timezone_get());

$_executionTime = microtime(true);

/**
 * Defines
 */
 
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
define('DIR_MAGNALISTER_WS_RESOURCE',   DIR_MAGNALISTER_WS.'resource/');
define('DIR_MAGNALISTER_WS_IMAGES',     DIR_MAGNALISTER_WS.'images/');

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
define('ML_DEFAULT_EXECUTIONTIME', 240);

if (isset($_GET['MLDEBUG']) && ($_GET['MLDEBUG'] == 'true')) {
	function ml_debug_out($m) {
		echo $m;
		flush();
	}
}

mlFixFilePermissions();

if (MAGNA_DEBUG && isset($_GET['MagnaRAW'])) {
	$_SESSION['MagnaRAW'] = $_GET['MagnaRAW'];
}

define('ML_RETINA_DISPLY', isset($_COOKIE['device_pixel_ratio']) && ((float)$_COOKIE['device_pixel_ratio'] > 1));

if (isset($_GET['module']) && ($_GET['module'] == 'ajax') && isset($_GET['request']) && ($_GET['request'] == 'keepAlive')) {
	if (file_exists(DIR_MAGNALISTER_FS_INCLUDES.'lib/MagnaDB.php')) {
		require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/MLTables.php');
		require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/MagnaDB.php');
		include_once(DIR_MAGNALISTER_FS_INCLUDES.'identifyShop.php');
		MagnaDB::gi();
		//commerce:Seo v2
		if (defined('DB_SERVER_CHARSET')) {
			MagnaDB::gi()->setCharset(DB_SERVER_CHARSET);
		}
		if (SHOPSYSTEM == 'gambio' && (($sVersion = mlGetGambioShopSystemVersion()) !== false)) {
			if (version_compare($sVersion, '2.1', '>=')) {
				MagnaDB::gi()->setCharset('utf8');
			}
			// in gambio v2.5.2.1 ml will displayed in iframe so no stuff to display from gambio backend
			if (version_compare($sVersion, '2.5.2.1', '>=') && !defined('ML_GAMBIO_USE_IFRAME')) {
				define('ML_GAMBIO_USE_IFRAME', true);
			}
			// Store information for Gambio updater - that ml will be updated when merchants update Gambio shopsystem
			MagnaDB::gi()->insert(TABLE_MAGNA_CONFIG, array('mpID' => '0', 'mkey' => 'ShopSystemVersion', 'value' => $sVersion), true);
		}
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

require_once(DIR_MAGNALISTER_FS.'MagnaUpdater.php');

$mlLocalClientVersion = mlGetLocalClientVersion();
$mlCurrentClientVersion = mlGetCurrentClientVersion();

$mlUpdater = new MagnaUpdater($mlCurrentClientVersion, $mlLocalClientVersion);

$mlUpdateStatus = mlUpdatePlugin($mlUpdater, $mlCurrentClientVersion, $mlLocalClientVersion);

$_updatedSuccessfully = $mlUpdateStatus['UpdatedSuccessfully'];
if ($mlUpdateStatus['UpdatedSuccessfully']) {
	$mlLocalClientVersion = $mlCurrentClientVersion;
}

define('MAGNA_SHOP_CHANGES', $mlUpdateStatus['RequiresInstallationUpdate']);

define('LOCAL_CLIENT_VERSION', $mlLocalClientVersion['CLIENT_VERSION']);
if (array_key_exists('CLIENT_BUILD_VERSION', $mlLocalClientVersion) && ((int)$mlLocalClientVersion['CLIENT_BUILD_VERSION'] > 0)) {
	define('CLIENT_BUILD_VERSION', $mlLocalClientVersion['CLIENT_BUILD_VERSION']);
} else {
	define('CLIENT_BUILD_VERSION', false);
}

define('CURRENT_CLIENT_VERSION', $mlCurrentClientVersion['CLIENT_VERSION']);
define('MINIMUM_CLIENT_VERSION', $mlCurrentClientVersion['MIN_CLIENT_VERSION']);
if (array_key_exists('CLIENT_BUILD_VERSION', $mlCurrentClientVersion) && ((int)$mlCurrentClientVersion['CLIENT_BUILD_VERSION'] > 0)) {
	define('CURRENT_BUILD_VERSION', $mlCurrentClientVersion['CLIENT_BUILD_VERSION']);
} else {
	define('CURRENT_BUILD_VERSION', false);
}

unset($mlUpdateStatus);
unset($mlLocalClientVersion);
unset($mlCurrentClientVersion);

$_updaterTime = microtime(true) - $_updaterTime;

mlIsCacheDirWritable();

/**
 * Global includes and initialisation
 */
require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/classes/MLShop.php');
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

require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/json_wrapper.php');
require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/MLTables.php');
require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/MagnaDB.php');
include_once(DIR_MAGNALISTER_FS_INCLUDES.'identifyShop.php');
$magnaDB = MagnaDB::gi(); /* Database Connector */
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
} else {
    // commerce:Seo uses gambio functionalities, but has no gambio version
    define('ML_GAMBIO_VERSION', '3.0');
}

mlDetectShopFeatures();

require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/functionLib.php');

BacktraceProccessor::setProjectDir(DIR_FS_CATALOG);
BacktraceProccessor::addHiddenStackElement(DB_SERVER_PASSWORD);

/* Update the database */
$_dbUpdateErrors = null;
if (MAGNA_SAFE_MODE || $_updatedSuccessfully || isset($_GET['dbupdate']) || !MagnaDB::gi()->tableExists(TABLE_MAGNA_CONFIG)) {
	$_dbUpdateErrors = $mlUpdater->updateDatabase();
}
// DB Update trigger for Gambio Cloud
if (MagnaDB::gi()->recordExists(TABLE_MAGNA_CONFIG, array(
    'mpID' => 0,
    'mkey' => 'trigger.dbupdate',
    'value' => 'true',
))) {
    $_dbUpdateErrors = $mlUpdater->updateDatabase();
    MagnaDB::gi()->update(TABLE_MAGNA_CONFIG, array(
        'value' => 'false'
    ), array(
        'mpID' => 0,
        'mkey' => 'trigger.dbupdate',
    ));
}
unset($mlUpdater);
#echo __FILE__.'{L'.__LINE__.'}';
#die();

/* Language-Foo */
$_magnaAvailableLanguages = magnaGetAvailableLanguages();
if (in_array($_SESSION['language'], $_magnaAvailableLanguages)) {
	$_magnaLanguage = $_lang = $_SESSION['language'];
} else {
	$_magnaLanguage = $_lang = array_first($_magnaAvailableLanguages);
}
$_langISO = strtolower(magnaGetLanguageCode($_lang));
@include_once(DIR_MAGNALISTER_FS.'lang/'.$_lang.'.php');

if (!array_key_exists('languages_id', $_SESSION) || empty($_SESSION['languages_id'])) {
    if (defined('ML_GAMBIO_41_NEW_CONFIG_TABLE')) {
        $_SESSION['languages_id'] = MagnaDB::gi()->fetchOne("
            SELECT `languages_id`
              FROM ".TABLE_LANGUAGES." l, ".TABLE_CONFIGURATION." c
             WHERE     l.`code` = c.`value`
                   AND c.`key` = 'configuration/DEFAULT_LANGUAGE'
        ");
    } else {
        $_SESSION['languages_id'] = MagnaDB::gi()->fetchOne(
            'SELECT languages_id '.
            'FROM '.TABLE_LANGUAGES.' l, '.TABLE_CONFIGURATION.' c '.
            'WHERE l.code=c.configuration_value '.
            'AND c.configuration_key=\'DEFAULT_LANGUAGE\'');
    }
}

/* Title of page */
$_mainTitle = '';

/* Description of Modules */
include_once(DIR_MAGNALISTER_FS_INCLUDES.'modules.php');
/* Must be loaded after loading the language definitions. */
require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/magnaFunctionLib.php');
/* {Hook} "init_extendModules" : define additional modules
    Here, you can define additional modules by extending the $modules array.
    Variables that can be used:
    <ul>
        <li>$modules</li>
    </ul>
*/
if (function_exists('magnaContribVerify') && (($hp = magnaContribVerify('init_extendModules', 1)) !== false)) {
    require($hp);
}
require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/classes/BrowserDetect.php');
/* Must be loaded after magnaFunctionLib */
require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/MLProduct.php');

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


if (isset($_GET['fix_ot_tax_free']) 
	&& ($_GET['fix_ot_tax_free'] == 'true') 
	// && (SHOPSYSTEM == 'gambio')
	&& (
		!defined('MODULE_ORDER_TOTAL_GM_TAX_FREE_STATUS')
		|| (strtolower(MODULE_ORDER_TOTAL_GM_TAX_FREE_STATUS) != 'true')
	)
) {
	$orderIds = MagnaDB::gi()->fetchArray("
	    SELECT ot.orders_id 
	      FROM ".TABLE_ORDERS_TOTAL." ot
	INNER JOIN ".TABLE_MAGNA_ORDERS." mo ON ot.orders_id = mo.orders_id AND mo.platform='ebay'
	     WHERE ot.`class` = 'ot_gm_tax_free'
	           AND ot.sort_order = 0 
	  ORDER BY ot.orders_id ASC
	", true);
	if (!empty($orderIds)) {
		MagnaDB::gi()->query("
			DELETE FROM ".TABLE_ORDERS_TOTAL."
			 WHERE `class` = 'ot_gm_tax_free'
			       AND sort_order = 0
			       AND orders_id IN (".implode(', ', $orderIds).")
		");
	}
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

magnaFixExecutionTime();

/* Kein Error-Handling da DB Fehler immer Fatal */
//echo print_m($_dbUpdateErrors, 'updateDatabase');

require_once(DIR_MAGNALISTER_FS_INCLUDES.'config.php');
/* Load configuration from database */
loadDBConfig();

require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/MagnaException.php');
require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/MagnaError.php');
require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/MagnaConnector.php');
MagnaConnector::gi()->setLanguage($_langISO);
$_mConnect = MagnaConnector::gi();

$requiredConfigKeys = array (
	'general.passphrase',
	'general.keytype',
	'general.stats.backwards',
	'general.callback.importorders',
);

/* Is magic_quotes on? (only old PHP versions) */
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
	if (get_magic_quotes_gpc()) {
		/* Strip the added slashes */
		$_REQUEST = arrayMap('stripslashes', $_REQUEST);
		$_GET     = arrayMap('stripslashes', $_GET);
		$_POST    = arrayMap('stripslashes', $_POST);
		$_COOKIE  = arrayMap('stripslashes', $_COOKIE);
	}
}

/**
 * Gobal verfuegbare Variablen:
 */
$_js = array();
$_magnaQuery = array();

/* ViewPages */
if (isset($_GET['module']) && in_array($_GET['module'], array(
	'fixcollations', 'fixorderstotal',
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
	/* 2016-04-26 help page allowed here */
	if ('guide' == $_GET['module']) {
		$_url['module'] = $_magnaQuery['module'] = 'guide';
	} else {
		$_url['module'] = $_GET['module'] = $_magnaQuery['module'] = 'configuration';
	}
	$_MagnaSession['currentPlatform'] = '';
	include_once(DIR_MAGNALISTER_FS_INCLUDES.'admin_view_top.php');
	include_once(DIR_MAGNALISTER_MODULES.$_url['module'].'.php');
	include_once(DIR_MAGNALISTER_FS_INCLUDES.'admin_view_bottom.php');
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

if (isset($_GET['module']) && in_array($_GET['module'], array(
	'viewchangelog',
))) {
	if (file_exists(DIR_MAGNALISTER_MODULES.$_GET['module'].'.php')) {
		$_url['module'] = $_magnaQuery['module'] = $_GET['module'];
		include_once(DIR_MAGNALISTER_MODULES.$_GET['module'].'.php');
	}
}
/* If Rookie limit exceeded and tab not global configuration $magnaConfig['maranon']*/
if ($magnaConfig['maranon']['IsRookieLimitExceeded'] === 'yes' && $_GET['module'] !== 'configuration') {
    $_GET['module'] = 'rookie';
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
	include_once(DIR_MAGNALISTER_FS_INCLUDES.'admin_view_top.php');
	include_once(DIR_MAGNALISTER_MODULES.$_GET['module'].'.php');
	include_once(DIR_MAGNALISTER_FS_INCLUDES.'admin_view_bottom.php');
	include_once(DIR_WS_INCLUDES . 'application_bottom.php');
	exit();
}

loadJSONConfig();
loadJSONConfig($_lang);

// Setup MLProduct
MLProduct::gi()->setOptions(array (
	// todo: Set default to 'false'
	'useGambioProperties' => MAGNA_GAMBIO_VARIATIONS && (getDBConfigValue('general.gambio.useproperties', '0', 'true') == 'true')
));

/* {Hook} "init_additionalModules" : include additional modules
    Here, you can set the address of an extra (contrib) module,
    if defined under init_extendModules hookpoint.
    Variables that can be used:
*/
if (function_exists('magnaContribVerify') && (($hp = magnaContribVerify('init_additionalModules', 1)) !== false)) {
    require($hp);
}

/* Testpages */
if (isset($_GET['module']) && in_array($_GET['module'], array('apitest', 'generictests', 'dev'))) {
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

ml_setMinRam('256M');

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

# prevent the "not yet booked" message if the mpID gets lost / access via ?module= for some reason
if (    !array_key_exists('mp', $_GET)
     &&  array_key_exists('module', $_GET)
     &&  in_array($_GET['module'], $magnaConfig['maranon']['Marketplaces'])) {
	foreach ($magnaConfig['maranon']['Marketplaces'] as $currMp => $currModule) {
		if ($currModule == $_GET['module']) {
			$_GET['mp'] = $currMp ;
			break;
		}
	}
}

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
	} elseif (isset($_GET['module']) && ($_GET['module'] == 'ajax') && isset($_GET['request']) && ($_GET['request'] == 'refreshPromotionHtml')) {
		$partner = trim((string)@file_get_contents('magnabundle.dat'));
		$sPromotionTextFile = DIR_MAGNALISTER_FS_CACHE.'promotion_'.$_langISO.'.html';
		$sPromotionTextUrl = MAGNA_SERVICE_URL.MAGNA_APIRELATED.'promotion/?shopsystem='.SHOPSYSTEM.(!empty($partner) && ($partner != 'key') ? '&partner='.$partner : ''). '&lang='.$_langISO;
		$sPromotionContent = fileGetContents($sPromotionTextUrl, $warings, 10);
		if (!empty($sPromotionContent)) {
			file_put_contents($sPromotionTextFile, $sPromotionContent);
		}
		exit();
	} else {
		$sMarketingTextFile = DIR_MAGNALISTER_FS_CACHE.'marketing_'.$_langISO.'.html';
		if (
			isset($_GET['module']) && ($_GET['module'] == 'ajax') && isset($_GET['request']) && ($_GET['request'] == 'refreshMarketingHtml')
			|| !file_exists($sMarketingTextFile)
		) {
			$marketingText = fileGetContents(MAGNA_SERVICE_URL.MAGNA_APIRELATED.'Marketing/?shop='.SHOPSYSTEM.'&build='.CLIENT_BUILD_VERSION.'&lang='.$_langISO, $warings, 10);
			$marketingText = !empty($marketingText) ? '<div class="marketing">'.$marketingText.'</div>' : '';
			if (!empty($marketingText)) {
				file_put_contents($sMarketingTextFile, $marketingText);
			}
			if (isset($_GET['module']) && ($_GET['module'] == 'ajax') && isset($_GET['request']) && ($_GET['request'] == 'refreshMarketingHtml')) {
				exit();
			}
		}
		$marketingText = file_exists($sMarketingTextFile) ? file_get_contents($sMarketingTextFile) : '';
		shopAdminDiePage($marketingText.'
			<script type="text/javascript">/*<![CDATA[*/
				(function(jQuery) {
					jQuery(document).ready(function() {
						jQuery.get(
							"magnalister.php", {
								"module":"ajax",
								"request":"refreshMarketingHtml"
							},
							function(data) {
								//myConsole.log(data);
							}
						);
					});
				})(jQuery);
			/*]]>*/</script>
		');
	}
}
