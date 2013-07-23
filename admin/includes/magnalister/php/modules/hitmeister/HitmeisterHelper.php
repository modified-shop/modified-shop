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
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/MagnaCompatibleHelper.php');

class HitmeisterHelper extends MagnaCompatibleHelper {
	public static function processCheckinErrors($result, $mpID) {
		$fieldname = 'MARKETPLACEERRORS';
		$dbCharSet = MagnaDB::gi()->mysqlVariableValue('character_set_connection');
    	if (('utf8mb3' == $dbCharSet) || ('utf8mb4' == $dbCharSet)) {
		# means the same for us
			$dbCharSet = 'utf8';
		}
		if ($dbCharSet != 'utf8') {
			arrayEntitiesToLatin1($result[$fieldname]);
		}
		$supportedFields = array('ErrorMessage', 'DateAdded', 'AdditionalData');
		if (!isset($result[$fieldname]) || empty($result[$fieldname])) {
			return;
		}
		foreach ($result[$fieldname] as $err) {
			if (!isset($err['AdditionalData'])) {
				$err['AdditionalData'] = array();
			}
			foreach ($err as $key => $value) {
				if (!in_array($key, $supportedFields)) {
					$err['AdditionalData'][$key] = $value;
					unset($err[$key]);
				}
			}
			$err = array (
				'mpID' => $mpID,
				'errormessage' => $err['ErrorMessage'],
				'dateadded' => $err['DateAdded'],
				'additionaldata' => serialize($err['AdditionalData']),
			);
			MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, $err);
		}
	}
	
	public static function GetConditionTypes() {
		global $_MagnaSession;
	
		$mpID = $_MagnaSession['mpID'];
	
		$types['values'] = array();
	
		if (isset($_MagnaSession[$mpID]['ConditionTypes'])) {
			return $_MagnaSession[$mpID]['ConditionTypes'];
		}
		try {
			$typesData = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetConditionTypes'
			));
		} catch (MagnaException $e) {
			$typesData = array(
				'DATA' => false
			);
		}
		if (!is_array($typesData) || !isset($typesData['DATA'])) {
			return false;
		}
		$_MagnaSession[$mpID]['ConditionTypes'] = $typesData['DATA'];
		return $typesData['DATA'];
	}
	
	public static function GetConditionTypesConfig(&$types) {
		$types['values'] = self::GetConditionTypes();
	}
	
	public static function GetShippingTimes() {
		global $_MagnaSession;
	
		$mpID = $_MagnaSession['mpID'];
	
		$times['values'] = array();
	
		if(   @isset($_MagnaSession[$mpID]['ShippingTimes'])
		   && !empty($_MagnaSession[$mpID]['ShippingTimes'])) {
			return $_MagnaSession[$mpID]['ShippingTimes'];
		}
		try {
			$timesData = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetShippingTimes'
			));
		} catch (MagnaException $e) {
			$timesData = array(
				'DATA' => false
			);
		}
		if (!is_array($timesData) || !isset($timesData['DATA'])) {
			return false;
		}
		foreach ($timesData['DATA'] as &$time) {
			$time = utf8_decode($time);
		}
		$_MagnaSession[$mpID]['ShippingTimes'] = $timesData['DATA'];
		return $timesData['DATA'];
	}
	
	public static function GetShippingTimesConfig(&$times) {
		$times['values'] = self::GetShippingTimes();
	}
}
