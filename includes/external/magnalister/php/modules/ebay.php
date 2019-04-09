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
 * $Id: ebay.php 650 2010-12-29 10:30:52Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
/**
 * eBay Module
 */
require_once(DIR_MAGNALISTER_MODULES.'ebay/ebayFunctions.php');
if (MAGNA_DEBUG) {
	define('DEVELOPMENT_TEST', 'Bei dem Produkt handelt es sich lediglich um einen Testartikel den wir im '.
		'Rahmen einer Schnittstellenanbindung an eBay auf Marketplace eingestellt haben. '.
		'Wir bitten Missverst&auml;ndnisse zu entschuldigen.');
}
$_Marketplace = 'ebay';

MagnaConnector::gi()->setSubsystem($_modules[$_Marketplace]['settings']['subsystem']);
MagnaConnector::gi()->setAddRequestsProps(array(
	'MARKETPLACEID' => $_MagnaSession['mpID']
));

require_once(DIR_MAGNALISTER_MODULES.'ebay/ebayFunctions.php');
require_once(DIR_MAGNALISTER_MODULES.'ebay/classes/EbayApiConfigValues.php');
EbayApiConfigValues::gi()->init($_MagnaSession);

loadDBConfig($_MagnaSession['mpID']);
$requiredConfigKeys =$_modules[$_MagnaSession['currentPlatform']]['requiredConfigKeys'];
$authConfigKeys = array('ebay.token');

$_magnaQuery['mode'] = getCurrentModulePage();
$_magnaQuery['messages'] = array();


if (array_key_exists('mode', $_GET) && ($_GET['mode'] == 'ajax')) {
	include_once(DIR_MAGNALISTER_MODULES.'ebay/ebayajax.php');
	require(DIR_WS_INCLUDES . 'application_bottom.php');
	exit();
}

if (!allRequiredConfigKeysAvailable($requiredConfigKeys, $_MagnaSession['mpID'])) {
	$_magnaQuery['mode'] = 'conf';
}
if (!(
	array_key_exists('conf', $_POST) && 
	allRequiredConfigKeysAvailable($authConfigKeys, $_MagnaSession['mpID'], $_POST['conf'])
)) {
	$authed = getDBConfigValue('ebay.authed', $_MagnaSession['mpID']);
	//$authed = false;
	if (!is_array($authed)) {
		$authed = array('state' => false, 'expire' => 0);
	}

	if (!$authed['state'] || ($authed['expire'] <= time())) {
		$epires = '';
		try {
			$r = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'IsAuthed',
			));
			#echo print_m($r, 'IsAuthed');
			$authState = true;
			if (isset($r['EXPIRES']) && (($ts = @strtotime($r['EXPIRES'])) !== false)) {
				$epires = $ts;
			}
		} catch (MagnaException $e) {
			$authState = false;
			if ($e->getCode() != MagnaException::UNKNOWN_ERROR) {
				$e->setCriticalStatus(false);
			}
			$authError = $e->getErrorArray();
			$_GET['mode'] = $_magnaQuery['mode'] = 'conf';
		}
		$authed = array (
			'state' => $authState,
			'expire' => time() + 60 * 15 // 15 Min
		);
		setDBConfigValue('ebay.authed', $_MagnaSession['mpID'], $authed, true);
		setDBConfigValue('ebay.token.expires', $_MagnaSession['mpID'], $epires, true);
	}
}

if (!MagnaDB::gi()->recordExists(TABLE_CURRENCIES, array (
	'code' => getCurrencyFromMarketplace($_MagnaSession['mpID'])
))) {
	$_magnaQuery['mode'] = 'conf';
	$currencyError = '<p class="errorBox">'.sprintf(
		ML_GENERIC_ERROR_CURRENCY_NOT_IN_SHOP,
		getCurrencyFromMarketplace($_MagnaSession['mpID'])
	).'</p>';
}

$includes = array();
# in prepare passiert categoriematching
if ($_magnaQuery['mode'] == 'prepare') {
	$includes[] = DIR_MAGNALISTER_MODULES.'ebay/pre_prepare.php';
	
} else if ($_magnaQuery['mode'] == 'checkin') {
	$includes[] = DIR_MAGNALISTER_MODULES.'ebay/checkin.php';

} else if ($_magnaQuery['mode'] == 'listings') {
	$includes[] = DIR_MAGNALISTER_MODULES.'ebay/listings.php';
	
} else if ($_magnaQuery['mode'] == 'errorlog') {
	$includes[] = DIR_MAGNALISTER_MODULES.'ebay/errorlog.php';

} else if ($_magnaQuery['mode'] == 'conf') {
	$includes[] = DIR_MAGNALISTER_MODULES.'ebay/ebayConfig.php';

}

if (is_array($_modules[$_MagnaSession['currentPlatform']]['pages'][$_magnaQuery['mode']])) {
	$views = $_modules[$_MagnaSession['currentPlatform']]['pages'][$_magnaQuery['mode']]['views'];

	if (isset($_GET['view']) && array_key_exists($_GET['view'], $views)) {
		$_magnaQuery['view'] = $_GET['view'];
	} else {
		$_magnaQuery['view'] = array_first(array_keys($views));
	}
}

if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
	require(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');
}

foreach ($includes as $item) {
	include_once($item);
}

# Properties-Tabelle
if (! MagnaDB::gi()->columnExistsInTable('GalleryURL', TABLE_MAGNA_EBAY_PROPERTIES)) {
	MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` ADD COLUMN `GalleryURL` varchar(255) NOT NULL  AFTER `PictureURL`');
}

if ($GLOBALS['MagnaAjax']) {
	exit();
}

include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
exit();
