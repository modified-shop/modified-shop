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
 * (c) 2011 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

$_Marketplace = 'meinpaket';

MagnaConnector::gi()->setSubsystem($_modules[$_Marketplace]['settings']['subsystem']);
MagnaConnector::gi()->setAddRequestsProps(array(
 	'MARKETPLACEID' => $_MagnaSession['mpID']
));

loadDBConfig($_MagnaSession['mpID']);

$_magnaQuery['mode'] = getCurrentModulePage();
$_magnaQuery['messages'] = array();

require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
require_once (DIR_MAGNALISTER_MODULES.'meinpaket/meinpaketFunctions.php');

require_once (DIR_MAGNALISTER_MODULES.'meinpaket/classes/MeinpaketApiConfigValues.php');
MeinpaketApiConfigValues::gi()->init($_MagnaSession);

$authConfigKeys = array(
	'meinpaket.username',
	'meinpaket.password',
);

if (!(
	array_key_exists('conf', $_POST) && 
	allRequiredConfigKeysAvailable($authConfigKeys, $_MagnaSession['mpID'], $_POST['conf'])
)) {
	$authed = getDBConfigValue('meinpaket.authed', $_MagnaSession['mpID']);
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
			'expire' => time() + 60 * 30 // 30 Min
		);
		setDBConfigValue('meinpaket.authed', $_MagnaSession['mpID'], $authed, true);
	}
}

$requiredConfigKeys = $_modules[$_MagnaSession['currentPlatform']]['requiredConfigKeys'];
if (!allRequiredConfigKeysAvailable($requiredConfigKeys, $_MagnaSession['mpID'], false, $which)) {
	$_magnaQuery['mode'] = 'conf';

} else {
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
if ($_magnaQuery['mode'] == 'prepare') {
	$includes[] = DIR_MAGNALISTER_MODULES.$_Marketplace.'/prepare.php';

} else if ($_magnaQuery['mode'] == 'catmatch') {
	$includes[] = DIR_MAGNALISTER_MODULES.$_Marketplace.'/categorymatching.php';

} else if ($_magnaQuery['mode'] == 'checkin') {
	$includes[] = DIR_MAGNALISTER_MODULES.$_Marketplace.'/checkin.php';

} else if ($_magnaQuery['mode'] == 'listings') {
	$includes[] = DIR_MAGNALISTER_MODULES.$_Marketplace.'/listings.php';

} else if ($_magnaQuery['mode'] == 'errorlog') {
	$includes[] = DIR_MAGNALISTER_MODULES.$_Marketplace.'/errorlog.php';

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
