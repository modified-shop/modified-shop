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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/AttributesMatchingHelper.php');

class HitmeisterHelper extends AttributesMatchingHelper
{
	protected $numberOfMaxAdditionalAttributes = self::UNLIMITED_ADDITIONAL_ATTRIBUTES;

	private static $instance;

	public static function gi()
	{
		if (self::$instance === null) {
			self::$instance = new HitmeisterHelper();
		}

		return self::$instance;
	}
	
	public static function loadPriceSettings($mpId) {
		$mp = magnaGetMarketplaceByID($mpId);

		$currency = getCurrencyFromMarketplace($mpId);
		$convertCurrency = getDBConfigValue(array($mp.'.exchangerate', 'update'), $mpId, false);

		$config = array(
			'Price' => array(
				'AddKind' => getDBConfigValue($mp.'.price.addkind', $mpId, 'percent'),
				'Factor'  => (float)getDBConfigValue($mp.'.price.factor', $mpId, 0),
				'Signal'  => getDBConfigValue($mp.'.price.signal', $mpId, ''),
				'Group'   => getDBConfigValue($mp.'.price.group', $mpId, ''),
				'UseSpecialOffer' => getDBConfigValue(array($mp.'.price.usespecialoffer', 'val'), $mpId, false),
				'Currency' => $currency,
				'ConvertCurrency' => $convertCurrency,
			),
			'PurchasePrice' => array(
				'AddKind' => getDBConfigValue($mp.'.purchaseprice.addkind', $mpId, 'percent'),
				'Factor'  => (float)getDBConfigValue($mp.'.purchaseprice.factor', $mpId, 0),
				'Signal'  => getDBConfigValue($mp.'.purchaseprice.signal', $mpId, ''),
				'Group'   => getDBConfigValue($mp.'.purchaseprice.group', $mpId, ''),
				'UseSpecialOffer' => false,
				'Currency' => $currency,
				'ConvertCurrency' => $convertCurrency,
				'IncludeTax' => false,
			),
		);

		return $config;
	}

	public static function loadQuantitySettings($mpId) {
		$mp = magnaGetMarketplaceByID($mpId);

		$config = array(
			'Type'  => getDBConfigValue($mp.'.quantity.type', $mpId, 'lump'),
			'Value' => (int)getDBConfigValue($mp.'.quantity.value', $mpId, 0),
			'MaxQuantity' => (int)getDBConfigValue($mp.'.quantity.maxquantity', $mpId, 0),
		);

		return $config;
	}

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

	protected function isProductPrepared($category, $prepare = false, $customIdentifier = '')
	{
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$sKeyType = 'products_model';
		} else {
			$sKeyType = 'products_id';
		}

		return MagnaDB::gi()->recordExists(TABLE_MAGNA_HITMEISTER_PREPARE, array(
			'MpId' => $this->mpId,
			$sKeyType => $prepare,
			'MarketplaceCategories' => $category,
		));
	}

	protected function getPreparedData($category, $prepare = false, $customIdentifier = '')
	{
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$sSQLAnd = ' AND products_model = "' . $prepare . '"';
		} else {
			$sSQLAnd = ' AND products_id = "' . $prepare . '"';
		}
		
		$availableCustomConfigs = array();
		if ($prepare) {
			$availableCustomConfigs = json_decode(MagnaDB::gi()->fetchOne(eecho('
				SELECT CategoryAttributes
				FROM ' . TABLE_MAGNA_HITMEISTER_PREPARE . '
				WHERE MpId = ' . $this->mpId . '
					AND MarketplaceCategories = "' . $category . '"
					' . $sSQLAnd . '
			', false)), true);
		}

		return !$availableCustomConfigs ? array() : $availableCustomConfigs;
	}

    /**
     * Gets prepared attributes data for products prepared for given category.
     *
     * @param string $category
     * @param string $customIdentifier
     * @return array|null
     */
	protected function getPreparedProductsData($category, $customIdentifier = '')
	{
		$dataFromDB = MagnaDB::gi()->fetchArray(eecho('
				SELECT `CategoryAttributes`
				FROM ' . TABLE_MAGNA_HITMEISTER_PREPARE . '
				WHERE mpID = ' . $this->mpId . '
					AND MarketplaceCategories = "' . $category . '"
			', false), true);

		if ($dataFromDB) {
			$result = array();
			foreach ($dataFromDB as $preparedData) {
				if ($preparedData) {
					$result[] = json_decode($preparedData, true);
				}
			}

			return $result;
		}

		return null;
	}

	protected function getAttributesFromMP($category, $additionalData = null, $customIdentifier = '')
	{
		$data = HitmeisterApiConfigValues::gi()->getVariantConfigurationDefinition($category);
		if (!is_array($data) || !isset($data['attributes'])) {
			$data = array();
		}

        if (empty($data['attributes'])) {
            $data['attributes'] = array();
        }

		return $data;
	}
}
