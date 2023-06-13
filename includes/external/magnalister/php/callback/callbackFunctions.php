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

function magnaGetClientVersion($args) {
	return array(
		'ClientVersion' => LOCAL_CLIENT_VERSION,
		'BuildVersion'  => CLIENT_BUILD_VERSION,
	);
}

function magnaCollectStats() {
	global $magnaConfig, $_modules;
	(function_exists('session_status') && session_status() == PHP_SESSION_ACTIVE) or @ini_set('session.referer_check', 'TRUE');
	
	$referers = array();
	$refererToModule = array();
	$modules = array_unique(array_values($magnaConfig['maranon']['Marketplaces']));
	foreach ($_modules as $marketplace => $module) {
		if (isset($module['referer']) && in_array($marketplace, $modules)) {
			foreach ($module['referer'] as $referer) {
				$referers[] = escape_string_for_regex($referer);
				$refererToModule[$referer] = $marketplace;
			}
		}
	}
    if (!empty($referers) && isset($_SERVER['HTTP_REFERER']) && preg_match('/'.implode('|', $referers).'/i', $_SERVER['HTTP_REFERER'], $match)) {
		$info = array (
			'Marketplace' => $refererToModule[$match[0]],
			'IP' => $_SERVER['REMOTE_ADDR'],
			'Browser' => $_SERVER['HTTP_USER_AGENT'],
			'Referer' => $_SERVER['HTTP_REFERER'],
			'DateTime' => gmdate('Y-m-d H:i:s'),
		);
		global $product;
		$pID = false;
		if (array_key_exists('products_id', $_GET)) {
			$pID = (int)$_GET['products_id'];
		} else if (is_object($product)) {
			$mlProdArray = (array)$product;
			if (array_key_exists('pID', $mlProdArray)) {
				$pID = (int)$mlProdArray['pID'];
			}
		}
		$title = '';
		if ($pID > 0) {
			$title = trim((string)MagnaDB::gi()->fetchOne('
				SELECT products_name
				  FROM `'.TABLE_PRODUCTS_DESCRIPTION.'`
				 WHERE products_id=\''.$pID.'\'
				       AND products_name <> \'\'
				 LIMIT 1
			'));
		}
		if (!empty($title)) {
			$info['SKU'] = magnaPID2SKU($pID);
			$info['ItemTitle'] = $title;
		}
		$mpID = false;
		$campaign = '';
		if (array_key_exists('mlcampaign', $_GET) && preg_match('/^[A-Za-z0-9]*$/', $_GET['mlcampaign'])) {
			$campaign = $_GET['mlcampaign'];
			$mpID = MagnaDB::gi()->fetchOne('
				SELECT mpID FROM '.TABLE_MAGNA_CONFIG.'
				 WHERE mkey=\''.MagnaDB::gi()->escape($info['Marketplace']).'.campaignlink\'
				       AND value=\''.MagnaDB::gi()->escape($campaign).'\'
				 LIMIT 1
			');
		}
		if ($mpID === false) {
			$mpID = MagnaDB::gi()->fetchOne('
				SELECT mpID FROM '.TABLE_MAGNA_CONFIG.'
				 WHERE mkey LIKE \''.MagnaDB::gi()->escape($info['Marketplace']).'%\'
				 LIMIT 1
			');
		}
		$clickCost = 0;
		$monthlyCost = 0;
		if ($mpID !== false) {
			loadDBConfig($mpID);
			$clickCost = getDBConfigValue($info['Marketplace'].'.cost.click', $mpID, 0.0);
			$monthlyCost = getDBConfigValue($info['Marketplace'].'.cost.montly', $mpID, 0.0);
		}

		$info['MarketplaceID'] = (int)$mpID;
		$info['CostClick'] = $clickCost;
		$info['CostMonthly'] = $monthlyCost;
		$info['Campaign'] = $campaign;

		$_SESSION['magnalister']['camefrom'] = $info;

		try {
			$res = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'CollectVisitStats',
				'SUBSYSTEM' => 'Core',
				'DATA' => $info
			));
		} catch (MagnaException $e) {
			if ($e->getCode() == MagnaException::TIMEOUT) {
				$e->saveRequest();
				$e->setCriticalStatus(false);
			}
			$res = $e->getErrorArray();
		}
		if (MAGNA_CALLBACK_MODE == 'STANDALONE') {
		#	echo print_m($res);
		}
	}
}

function magnaGetInvolvedMarketplaces() {
	global $_modules;
	$fm = array();
	if (isset($_GET['mps']) && !empty($_GET['mps'])) {
		$mps = explode(',', $_GET['mps']);
		foreach ($mps as $m) {
			if (array_key_exists($m, $_modules) && ($_modules[$m]['type'] == 'marketplace')) {
				$fm[] = $m;
			}
		}
	}
	if (!empty($fm)) {
		return $fm;
	}
	foreach ($_modules as $m => $mp) {
		if ($mp['type'] == 'marketplace') {
			$fm[] = $m;
		}
	}
	return $fm;
}

function magnaGetInvolvedMPIDs($marketplace) {
	$mpIDs = magnaGetIDsByMarketplace($marketplace);
	if (empty($mpIDs)) {
		return array();
	}
	if (isset($_GET['mpid'])) {
		if (in_array($_GET['mpid'], $mpIDs)) {
			return array($_GET['mpid']);
		} else {
			return array();
		}
	}
	return $mpIDs;
}

function magnaResetEbayOrderStatus20191206() {
	// prevent multiple usage
	if ('1' == getDBConfigValue('ResetOrderStatus20191206', 0, 0)) return;
	$mpIDs = magnaGetIDsByMarketplace('ebay');
	$mpIDsList = implode(',', $mpIDs);
	// fetch status sync config for eBay
	$aStats = MagnaDB::gi()->fetchArray('SELECT mpID, mkey, value
		 FROM magnalister_config
		WHERE mkey in (\'ebay.orderstatus.sync\', \'ebay.orderstatus.open\', \'ebay.orderstatus.shipped\', \'ebay.orderstatus.cancelled\')
		  AND mpID in ('.$mpIDsList.')
		ORDER BY mpID');
	$aStatsByMpId = array();
	foreach ($mpIDs as $mpID) {
		$aStatsByMpId[$mpID] = array();
	}
	foreach ($aStats as $aStat) {
		$aStatsByMpId[$aStat['mpID']][$aStat['mkey']] = $aStat['value'];
	}
	// take only the mpIDs where sync is on
	foreach ($mpIDs as $no => $mpID) {
		if ($aStatsByMpId[$mpID]['ebay.orderstatus.sync'] <> 'auto') {
			unset($aStatsByMpId[$mpID]);
			unset($mpIDs[$no]);
		}
	}
	$aDoneStats = array();
	// fetch relevant stats
	foreach ($aStatsByMpId as $aStatByMpId) {
		foreach ($aStatByMpId as $mkey => $val) {
			if (    ('ebay.orderstatus.shipped'   == $mkey)
			     || ('ebay.orderstatus.cancelled' == $mkey)) {
				$aDoneStats[] = $val;
			}
		}
	}
	array_unique($aDoneStats);
	$doneStatsList = implode(',', $aDoneStats);
	do {
		// find orders
		$aOrdersIds =  MagnaDB::gi()->fetchArray('SELECT orders_id, MAX(date_added) 
			 FROM '.TABLE_ORDERS_STATUS_HISTORY.'
			WHERE orders_status_id in ('.$doneStatsList.')
			  AND date_added BETWEEN \'2019-12-05 11:00:00\' AND \'2019-12-06 11:00:00\'
			GROUP BY orders_id');
		$orderIdsList = '';
		foreach ($aOrdersIds as $aOrdersId) {
			$orderIdsList .= ','.$aOrdersId['orders_id'];
		}
		$orderIdsList = ltrim($orderIdsList , ',');
		if (empty($orderIdsList)) {
			break;
		}
		// find out which are from eBay
		$aOrdersIdsInMLTable = MagnaDB::gi()->fetchArray('SELECT orders_id, mpID
			 FROM magnalister_orders
			WHERE platform=\'ebay\'
			  AND orders_id IN ('.$orderIdsList.')');
		if (empty($aOrdersIdsInMLTable)) {
			break;
		}
		$aOrdersIdsByMpId = array();
		foreach ($mpIDs as $mpID) {
			$aOrdersIdsByMpId[$mpID] = '';
		}
		foreach ($aOrdersIdsInMLTable as $aOrder) {
			$aOrdersIdsByMpId[$aOrder['mpID']] .= ",'".$aOrder['orders_id']."'";
		}
		foreach ($mpIDs as $mpID) {
			$aOrdersIdsByMpId[$mpID] = ltrim($aOrdersIdsByMpId[$mpID], ',');
		}
		if (empty($aOrdersIdsByMpId)) {
			break;
		}
		// reset send status
		foreach ($mpIDs as $mpID) {
			$q = 'UPDATE `magnalister_orders`
				  SET `orders_status` = \''.$aStatsByMpId[$mpID]['ebay.orderstatus.open'].'\'
				WHERE `orders_id` IN ('.$aOrdersIdsByMpId[$mpID].')';
			MagnaDB::gi()->query($q);
		}
	} while(false);
	// config setting to prevent multiple usage
	MagnaDB::gi()->query('REPLACE INTO magnalister_config(mpID, mkey, value)
		VALUES(0, \'ResetOrderStatus20191206\', 1)');
}
