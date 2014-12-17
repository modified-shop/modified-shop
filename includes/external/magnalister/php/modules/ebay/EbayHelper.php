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
}
