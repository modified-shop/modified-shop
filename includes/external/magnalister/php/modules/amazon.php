<?php
/*
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
 * (c) 2010 - 2022 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
/**
 * Amazon Module
 */
$_Marketplace = 'amazon';

MagnaConnector::gi()->setSubsystem($_modules[$_Marketplace]['settings']['subsystem']);
MagnaConnector::gi()->setAddRequestsProps(array('MARKETPLACEID' => $_MagnaSession['mpID']));

require_once(DIR_MAGNALISTER_MODULES.'amazon/amazonFunctions.php');

loadDBConfig($_MagnaSession['mpID']);

$requiredConfigKeys = $_modules[$_MagnaSession['currentPlatform']]['requiredConfigKeys'];
$authConfigKeys = array(
	'amazon.merchantid',
	'amazon.site',
);
$_magnaQuery['mode'] = getCurrentModulePage();
$_magnaQuery['messages'] = array();

#outOfOrder();

if (!defined('MAGNA_FIELD_PRODUCTS_EAN')) {
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');
	echo '<br/><p class="noticeBox"><b class="notice">'.ML_LABEL_ATTENTION.':</b> '.ML_ERROR_MISSING_PRODUCTS_EAN.'</p>';
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
	require(DIR_WS_INCLUDES . 'application_bottom.php');
	exit();
}

if (array_key_exists('mode', $_GET) && ($_GET['mode'] == 'ajax')) {
	include_once(DIR_MAGNALISTER_MODULES.'amazon/amazonajax.php');
	exit();
}

if (!(
	array_key_exists('conf', $_POST) && 
	allRequiredConfigKeysAvailable($authConfigKeys, $_MagnaSession['mpID'], $_POST['conf'])
)) {
	$authed = getDBConfigValue('amazon.authed', $_MagnaSession['mpID']);
	if (!is_array($authed)) {
		$authed = array('state' => false, 'expire' => 0);
	}

	if (!$authed['state'] || ($authed['expire'] <= time())) {
		try {
			$r = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'IsAuthed',
			));
			$authState = true;
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
		setDBConfigValue('amazon.authed', $_MagnaSession['mpID'], $authed, true);
	}
}

if (!allRequiredConfigKeysAvailable($requiredConfigKeys, $_MagnaSession['mpID'])) {
	$_magnaQuery['mode'] = 'conf';
}


if (!MagnaDB::gi()->recordExists(TABLE_CURRENCIES, array (
	'code' => getCurrencyFromMarketplace($_MagnaSession['mpID'])
))) {
	$_magnaQuery['mode'] = 'conf';
	$currencyError = '<p class="errorBox">'.sprintf(
		ML_AMAZON_ERROR_CURRENCY_NOT_IN_SHOP,
		getCurrencyFromMarketplace($_MagnaSession['mpID'])
	).'</p>';
}

$includes = array();
if ($_magnaQuery['mode'] == 'prepare') {
	$includes[] = DIR_MAGNALISTER_MODULES.'amazon/prepare.php';

} else if ($_magnaQuery['mode'] == 'checkin') {
	$includes[] = DIR_MAGNALISTER_MODULES.'amazon/checkin.php';

} else if ($_magnaQuery['mode'] == 'shippinglabel') {
	$includes[] = DIR_MAGNALISTER_MODULES.'amazon/shippinglabel.php';
} else if ($_magnaQuery['mode'] == 'listings') {
	$includes[] = DIR_MAGNALISTER_MODULES.'amazon/listings.php';
	
} else if ($_magnaQuery['mode'] == 'errorlog') {
	$includes[] = DIR_MAGNALISTER_MODULES.'amazon/errorlog.php';

} else if ($_magnaQuery['mode'] == 'conf') {
	$includes[] = DIR_MAGNALISTER_MODULES.'amazon/amazonConfig.php';
	
} else if (isset($_modules['amazon']['pages']['apply']) && ($_magnaQuery['mode'] == 'apply')) {
	$includes[] = DIR_MAGNALISTER_MODULES.'amazon/apply.php';
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
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');

	if (!empty($_magnaQuery['messages'])) {
		foreach ($_magnaQuery['messages'] as $message) {
			echo $message;
		}
	}
}

foreach ($includes as $item) {
	include_once($item);
}

if ($GLOBALS['MagnaAjax']) {
	exit();
}

include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
exit();
