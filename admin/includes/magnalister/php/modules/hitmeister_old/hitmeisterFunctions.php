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
 * (c) 2010 - 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'generic/genericFunctions.php');

function updateHitmeisterInventoryByEdit($mpID, $updateData) {
	$updateItem = genericInventoryUpdateByEdit($mpID, $updateData);	
	if (!is_array($updateItem)) {
		return false;
	}
	try {
		$result = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'UpdateItems',
			'SUBSYSTEM' => 'Hitmeister',
			'MARKETPLACEID' => $mpID,
			'DATA' => array($updateItem),
		));
		#echo print_m($result, '$result');
	} catch (MagnaException $e) {
		if ($e->getCode() == MagnaException::TIMEOUT) {
			$e->saveRequest();
			$e->setCriticalStatus(false);
		}
		#echo print_m($e->getErrorArray(), '$error');
	}
}

function GetConditionTypes(&$types) {
	global $_MagnaSession;

	$mpID = $_MagnaSession['mpID'];

	$types['values'] = array();

	if(@isset($_MagnaSession[$mpID]['ConditionTypes'])) {
		$types['values'] = $_MagnaSession[$mpID]['ConditionTypes'];
	} else {
		try { $typesData = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetConditionTypes'));
		} catch (MagnaException $e) {
			$typesData = array(
				'DATA' => false
			);
		}
		if (!is_array($typesData) || @empty($typesData['DATA'])) {
			return false;
		}
		foreach ($typesData['DATA'] as &$type) {
			$type = utf8_decode($type);
		}
		$_MagnaSession[$mpID]['ConditionTypes'] = $typesData['DATA'];
		$types['values'] = $typesData['DATA'];
	}
}

function GetShippingTimes(&$times) {
	global $_MagnaSession;

	$mpID = $_MagnaSession['mpID'];

	$times['values'] = array();

	if(@isset($_MagnaSession[$mpID]['ShippingTimes'])) {
		$times['values'] = $_MagnaSession[$mpID]['ShippingTimes'];
	} else {
		try { $timesData = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetShippingTimes'));
		} catch (MagnaException $e) {
			$timesData = array(
				'DATA' => false
			);
		}
		if (!is_array($timesData) || @empty($timesData['DATA'])) {
			return false;
		}
		foreach ($timesData['DATA'] as &$time) {
			$time = utf8_decode($time);
		}
		$_MagnaSession[$mpID]['ShippingTimes'] = $timesData['DATA'];
		$times['values'] = $timesData['DATA'];
	}
}
