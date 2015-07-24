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
require_once(DIR_MAGNALISTER_MODULES.'hood/classes/HoodApiConfigValues.php');

class EbayHelper extends MagnaCompatibleHelper {
	protected static $priceConfigs = array();
	protected static $marketplaces = array();
	
	protected static function getMartketplaceById($mpId) {
		if (!array_key_exists($mpId, self::$marketplaces)) {
			self::$marketplaces[$mpId] = magnaGetMarketplaceByID($mpId);
		}
		return self::$marketplaces[$mpId];
	}
	
	public static function getPriceSettingsByListingType($mpId, $listingType){
		if ($listingType == 'Chinese') {
			$priceTypes = array('chinese.buyitnow', 'chinese');
		} else {//StoresFixedPrice, FixedPriceItem
			$priceTypes = array('fixed');
		}
		$priceConfigs = array();
		foreach ($priceTypes as $priceType) {
			$priceConfig = EbayHelper::getPriceSettingsByPriceType($mpId, $priceType);
			if ($priceConfig['active']) {
				unset($priceConfig['active']);
				$priceConfigs[$priceType] = $priceConfig;
			}
		}
		return $priceConfigs;
	}
	public static function getQuantitySettingsByListingType($mpId, $listingType){
		$currency = getDBConfigValue('ebay.currency', $mpId);
		if ($listingType == 'Chinese') {
			return array (
				'Type' => 'stocksub',
				'Value' => 0, 
				'MaxQuantity' => 1,
			);
		}else{
			$maxQuantity = (int)getDBConfigValue('ebay.maxquantity', $mpId, 0);
			$maxQuantity = (0 == $maxQuantity) ? PHP_INT_MAX : $maxQuantity;
			return array (
				'Type' => getDBConfigValue('ebay.fixed.quantity.type', $mpId),
				'Value' => (int)getDBConfigValue('ebay.fixed.quantity.value', $mpId), 
				'MaxQuantity' => $maxQuantity,
				'Currency' => $currency
			);
		}
	}

	public static function getPriceSettingsByPriceType($mpId, $priceType) {
		$marketplace = self::getMartketplaceById($mpId);
		if (
			!array_key_exists($mpId, self::$priceConfigs) 
			|| !array_key_exists($priceType, self::$priceConfigs[$mpId])
		) {
			foreach (array(
				array('key' => array('active', 'val'),			'default' => true), 
				array('key' => 'AddKind',						'default' => 'percent'), 
				array('key' => 'Factor',						'default' => 0), 
				array('key' => 'Signal',						'default' => ''), 
				array('key' => 'Group',							'default' => ''), 
				array('key' => array('UseSpecialOffer', 'val'), 'default' => false), 
				array('key' => 'Currency',						'default' => null), 
				array('key' => 'ConvertCurrency',				'default' => null)
			) as $config) {
				if (is_array($config['key'])) {
					$configKey = array(
						$marketplace.'.'.$priceType.'.price.'.strtolower($config['key'][0]), 
						strtolower($config['key'][1])
					);
					$priceKey = $config['key'][0];
				} else {
					$configKey = strtolower($marketplace.'.'.$priceType.'.price.'.$config['key']);
					// currency: same for all price types
					if (('Currency' == $config['key']) || ('ConvertCurrency' == $config['key'])) {
						$configKey = strtolower($marketplace.'.'.$config['key']);
					}
					$priceKey = $config['key'];
				}
					self::$priceConfigs[$mpId][$priceType][$priceKey] = getDBConfigValue(
						$configKey, 
						$mpId, 
						$config['default']
					);
				}
		}
		return self::$priceConfigs[$mpId][$priceType]['active'] ? self::$priceConfigs[$mpId][$priceType] : array();
	}

	/*
	 * return array - matched details (brand, mpn, ean)
	 */
	public static function getProductListingDetailsFromProduct($iProductId, $iLang) {
		global $_MagnaSession;

		if (getDBConfigValue('ebay.listingdetails.sync', $_MagnaSession['mpID'], false) == 'false') {
			return array();
		}

		MLProduct::gi()->setLanguage($iLang);

		// match manufacturer part number
		$aManufacturerPartNumber = getDBConfigValue('ebay.listingdetails.mpn.dbmatching.table', $_MagnaSession['mpID'], false);
		if (is_array($aManufacturerPartNumber) && !empty($aManufacturerPartNumber['column']) && !empty($aManufacturerPartNumber['table'])) {
			$sPidAlias = getDBConfigValue('ebay.listingdetails.mpn.dbmatching.alias', $_MagnaSession['mpID']);
			if (empty($sPidAlias)) {
				$sPidAlias = 'products_id';
			}
			MLProduct::gi()->setDbMatching('ManufacturerPartNumber', array (
				'Table'  => $aManufacturerPartNumber['table'],
				'Column' => $aManufacturerPartNumber['column'],
				'Alias'  => $sPidAlias,
			));
		}

		// match ean
		$aEAN = getDBConfigValue('ebay.listingdetails.ean.dbmatching.table', $_MagnaSession['mpID'], false);
		if (is_array($aEAN) && !empty($aEAN['column']) && !empty($aEAN['table'])) {
			$sPidAlias = getDBConfigValue('ebay.listingdetails.ean.dbmatching.alias', $_MagnaSession['mpID']);
			if (empty($sPidAlias)) {
				$sPidAlias = 'products_id';
			}
			MLProduct::gi()->setDbMatching('EAN', array (
				'Table'  => $aEAN['table'],
				'Column' => $aEAN['column'],
				'Alias'  => $sPidAlias,
			));
		}

		// get product
		$aProduct = MLProduct::gi()->getProductById($iProductId);

		// set listing details
		$aListingDetails = array(
			'Brand' => $aProduct['Manufacturer'],
			'MPN' => $aProduct['ManufacturerPartNumber'],
			'EAN' => $aProduct['EAN'],
		);

		// if brand is empty try to get it from config
		$sAlternativeBrand = getDBConfigValue('ebay.listingdetails.manufacturerfallback', $_MagnaSession['mpID'], false);
		if (   empty($aListingDetails['Brand'])
			&& $sAlternativeBrand !== false
		) {
			$aListingDetails['Brand'] = $sAlternativeBrand;
		}

		/* {Hook} "EbayHelper_getProductListingDetailsFromProduct": Is called before the data of the product in <code>$aListingDetails</code> will return.
			Useful to manipulate some of the data.
			Variables that can be used:
			<ul>
				<li>$aListingDetails: The data of a product for the preparation</li>
				<li>$_MagnaSession: magna session data (marketplace, mpID etc.)</li>
			</ul>
		*/
		if (($hp = magnaContribVerify('EbayHelper_getProductListingDetailsFromProduct', 1)) !== false) {
			require($hp);
		}

		return $aListingDetails;
	}
}
