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

$_Marketplace = 'daparto';

MagnaConnector::gi()->setSubsystem($_modules[$_Marketplace]['settings']['subsystem']);
MagnaConnector::gi()->setAddRequestsProps(array(
	'SEARCHENGINE' => $_Marketplace,
	'MARKETPLACEID' => $_MagnaSession['mpID']
));

loadDBConfig($_MagnaSession['mpID']);

$requiredConfigKeys = $_modules[$_MagnaSession['currentPlatform']]['requiredConfigKeys'];
$_magnaQuery['mode'] = getCurrentModulePage();
$_magnaQuery['messages'] = array();

require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/Shipping.php');
$_shippingClass = new Shipping();

$shippingMethod = getDBConfigValue($_Marketplace.'.shipping.method', $_MagnaSession['mpID']);
if (($shippingMethod !== null) &&
	!$_shippingClass->methodExists($shippingMethod) &&
	(strpos($shippingMethod, '__ml_') === false)
) {
	removeDBConfigValue($_Marketplace.'.shipping.method', $_MagnaSession['mpID']);
}
$failedConfig = array();
if (!allRequiredConfigKeysAvailable($requiredConfigKeys, $_MagnaSession['mpID'], false, $failedConfig)) {
	#echo print_m($failedConfig);
	$_magnaQuery['mode'] = 'conf';

} else {
	/* configure shipping class */
	$_shippingClass->configure(array(
		'prefferedMethod' => getDBConfigValue($_Marketplace.'.shipping.method', $_MagnaSession['mpID']),
		'shippingCountry' => getDBConfigValue($_Marketplace.'.shipping.country', $_MagnaSession['mpID']),
		'fallback' => getDBConfigValue($_Marketplace.'.shipping.cost', $_MagnaSession['mpID'])
	));
	
	/* Einstellen aus ErrorLog */
	if (isset($_POST['errIDs']) && isset($_POST['action']) && ($_POST['action'] == 'retry') &&
		($_SESSION['post_timestamp'] != $_POST['timestamp'])
	) {
		$_SESSION['post_timestamp'] = $_POST['timestamp'];
		require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/ComparisonShopping/ComparisonShoppingCheckinSubmit.php');
		$cS = new ComparisonShoppingCheckinSubmit(array(
			'marketplace' => $_Marketplace
		));
		if ($cS->makeSelectionFromErrorLog()) {
			$_magnaQuery['mode'] = 'checkin';
			$_magnaQuery['view'] = 'submit';
		}
	}
}

$includes = array();
if ($_magnaQuery['mode'] == 'checkin') {
	$includes[] = DIR_MAGNALISTER_MODULES.$_Marketplace.'/checkin.php';

} else if ($_magnaQuery['mode'] == 'listings') {
	$includes[] = DIR_MAGNALISTER_MODULES.'generic/cslistings.php';

} else if ($_magnaQuery['mode'] == 'conf') {
	$includes[] = DIR_MAGNALISTER_MODULES.$_Marketplace.'/'.$_Marketplace.'Config.php';
}	


if (is_array($_modules[$_MagnaSession['currentPlatform']]['pages'][$_magnaQuery['mode']])) {
	$views = $_modules[$_MagnaSession['currentPlatform']]['pages'][$_magnaQuery['mode']]['views'];

	if (isset($_GET['view']) && array_key_exists($_GET['view'], $views)) {
		$_magnaQuery['view'] = $_GET['view'];
	} else {
		$_magnaQuery['view'] = array_first(array_keys($views));
	}
	
	if (isset($_shitHappend) && $_shitHappend && ($_magnaQuery['mode'] == 'listings')) {
		$_magnaQuery['view'] = 'failed';
	}
}

if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');

	if (!empty($_magnaQuery['messages'])) {
		foreach ($_magnaQuery['messages'] as $message) {
			echo $message;
		}
	}
	
	/* DEBUG * /
	if (isset($checkInResult)) {
		echo '<textarea class="debugBox" wrap="off">checkInResult :: '.print_r($checkInResult, true).'</textarea>';
	} */

	if (isset($magnaExceptionOccured)) {
		echo $magnaExceptionOccured;
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
