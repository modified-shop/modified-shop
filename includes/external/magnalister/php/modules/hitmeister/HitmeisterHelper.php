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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
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
	
		if (   isset($_MagnaSession[$mpID]['ConditionTypes'])
			&& !empty($_MagnaSession[$mpID]['ConditionTypes'])
		) {
			return $_MagnaSession[$mpID]['ConditionTypes'];
		}
		try {
			$typesData = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetUnitConditions'
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

		if (   isset($_MagnaSession[$mpID]['ShippingTimes'])
			&& !empty($_MagnaSession[$mpID]['ShippingTimes'])
		) {
			return $_MagnaSession[$mpID]['ShippingTimes'];
		}
		try {
			$timesData = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetDeliveryTimes'
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
			$time = stringToUTF8($time);
		}
		$_MagnaSession[$mpID]['ShippingTimes'] = $timesData['DATA'];
		return $timesData['DATA'];
	}
	
	public static function GetShippingTimesConfig(&$times) {
		$times['values'] = self::GetShippingTimes();
	}
	
	public static function GetDeliveryCountries() {
		global $_MagnaSession;
	
		$mpID = $_MagnaSession['mpID'];
	
		$times['values'] = array();

		if (   isset($_MagnaSession[$mpID]['DeliveryCountries'])
			&& !empty($_MagnaSession[$mpID]['DeliveryCountries'])
		) {
			return $_MagnaSession[$mpID]['DeliveryCountries'];
		}
		
		try {
			$timesData = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetDeliveryCountries'
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
			$time = stringToUTF8($time);
		}
		
		$_MagnaSession[$mpID]['DeliveryCountries'] = $timesData['DATA'];
		return $timesData['DATA'];
	}
	
	public static function GetDeliveryCountriesConfig(&$times) {
		$times['values'] = self::GetDeliveryCountries();
	}

	public static function SearchOnHitmeister($search = '', $searchBy = 'EAN') {
		try {
			$data = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetItemsFromMarketplace',
				'DATA' => array(
					$searchBy => $search
				)
			));
		} catch (MagnaException $e) {
			$data = array(
				'DATA' => false
			);
		}

		if (!is_array($data) || !isset($data['DATA']) || empty($data['DATA'])) {
			return false;
		}

		return $data['DATA'];
	}

	public static function GetWeightFromShop($itemId) {
		$result = MagnaDB::gi()->fetchOne('
			SELECT products_weight
			FROM ' . TABLE_PRODUCTS. '
			WHERE products_id = "' . $itemId . '"
		');

		if ($result && (int) $result > 0) {
			$weight = round($result, 2);
			return $weight . 'kg';
		}

		return '';
	}

	public static function GetContentVolumeFromShop($itemId) {
		$result = MagnaDB::gi()->fetchRow('
			SELECT p.products_vpe_value AS vpe, pvpe.products_vpe_name AS sufix
			FROM ' . TABLE_PRODUCTS. ' p, ' . TABLE_PRODUCTS_VPE . ' pvpe
			WHERE p.products_id = "' . $itemId . '"
				AND pvpe.products_vpe_id = p.products_vpe
		');
		if ($result  && (int) $result > 0) {
			$factor = array();
			if (preg_match('/^([0-9][0-9,.]*)/', $result['sufix'], $factor)) {
				$factor = mlFloatalize($factor[1]);
				$contentValue    = round($result['vpe'] * $factor, 2);
				$result['sufix'] = trim(preg_replace('/^[0-9][0-9,.]*/', '', $result['sufix']));
			} else {
				$contentValue = round($result['vpe'], 2);
			}
			return $contentValue . $result['sufix'];
		}

		return '';
	}

}
