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

class HoodHelper extends MagnaCompatibleHelper {
	public static function processCheckinErrors($result, $mpID) {
		// Empty is ok, the API has a method to fetch the error log later.
	}
	
	public static function hasStore() {
		$store = HoodApiConfigValues::gi()->getHasStore();
		return is_array($store) && isset($store['Info.ShopType']) && ($store['Info.ShopType'] != 'noShop');
	}
	
	public static function loadPriceSettings($mpId) {
		$mp = magnaGetMarketplaceByID($mpId);
		
		$config = array(
			'Auction' => array(
				'StartPrice' => array(
					'AddKind' => getDBConfigValue($mp.'.auction.startprice.addkind', $mpId, 'percent'),
					'Factor'  => (float)getDBConfigValue($mp.'.auction.startprice.factor', $mpId, 0),
					'Signal'  => getDBConfigValue($mp.'.auction.startprice.signal', $mpId, ''),
				),
				'BuyItNow' => array(
					'AddKind' => getDBConfigValue($mp.'.auction.buyitnowprice.addkind', $mpId, 'percent'),
					'Factor'  => (float)getDBConfigValue($mp.'.auction.buyitnowprice.factor', $mpId, 0),
					'Signal'  => getDBConfigValue($mp.'.auction.buyitnowprice.signal', $mpId, ''),
					'UseBuyItNow' => getDBConfigValue(array($mp.'.auction.buyitnowprice.active', 'val'), $mpId, false),
				),
			),
			'Fixed' => array(
				'AddKind' => getDBConfigValue($mp.'.fixed.price.addkind', $mpId, 'percent'),
				'Factor'  => (float)getDBConfigValue($mp.'.fixed.price.factor', $mpId, 0),
				'Signal'  => getDBConfigValue($mp.'.fixed.price.signal', $mpId, ''),
				'Group'   => getDBConfigValue($mp.'.fixed.price.group', $mpId, ''),
				'UseSpecialOffer' => getDBConfigValue(array($mp.'.fixed.price.usespecialoffer', 'val'), $mpId, false),
			),
		);
		$config['Auction']['StartPrice']['Group'] = $config['Auction']['BuyItNow']['Group'] =
			getDBConfigValue($mp.'.auction.price.group', $mpId, '');
		
		$config['Auction']['StartPrice']['UseSpecialOffer'] = $config['Auction']['BuyItNow']['UseSpecialOffer'] =
			getDBConfigValue(array($mp.'.auction.price.usespecialoffer', 'val'), $mpId, false);
		
		return $config;
	}
	
	public static function loadQuantitySettings($mpId) {
		$mp = magnaGetMarketplaceByID($mpId);
		
		$config = array(
			'Auction' => array(
				'Type'  => getDBConfigValue($mp.'.auction.quantity.type', $mpId, 'lump'),
				'Value' => (int)getDBConfigValue($mp.'.auction.quantity.value', $mpId, 0),
				'MaxQuantity' => (int)getDBConfigValue($mp.'.auction.quantity.maxquantity', $mpId, 0),
			),
			'Fixed' => array(
				'Type'  => getDBConfigValue($mp.'.fixed.quantity.type', $mpId, 'lump'),
				'Value' => (int)getDBConfigValue($mp.'.fixed.quantity.value', $mpId, 0),
				'MaxQuantity' => (int)getDBConfigValue($mp.'.fixed.quantity.maxquantity', $mpId, 0),
			),
		);
		
		return $config;
	}
	
	public static function calcQuantity($dbQuantity, $config) {
		if (!is_array($config) || !isset($config['Type']) || !isset($config['Value'])) {
			return $dbQuantity;
		}
		if (!isset($config['MaxQuantity'])) {
			$config['MaxQuantity'] = 0;
		}
		switch ($config['Type']) {
			case 'stocksub': {
				$dbQuantity -= $config['Value'];
				break;
			}
			case 'lump': {
				$dbQuantity = $config['Value'];
				break;
			}
		}
		if (($config['MaxQuantity'] > 0) && ($config['Type'] != 'lump')) {
			$dbQuantity = min($dbQuantity, $config['MaxQuantity']);
		}
		$dbQuantity = max($dbQuantity, 0); // make sure it is always >= 0
		return $dbQuantity;
	}
	
	public static function substituteTemplate($mpId, $pID, $template, $substitution) {
		if (($hp = magnaContribVerify('HoodSubstituteTemplate', 1)) !== false) {
			require($hp);
		}
		
		return substituteTemplate($template, $substitution);
	}
	
	public static function getSubstitutePictures($tmplStr, $pID, $imagePath) {
		$undo = ml_extractBase64($tmplStr);
		
		$pics = MLProduct::gi()->getAllImagesByProductsId($pID);
		$i = 1;
		# Ersetze alle Bilder
		foreach($pics as $pic) {
			$tmplStr = str_replace(
				'#PICTURE' . $i . '#',
				"<img src=\"" . $imagePath . $pic . "\" style=\"border:0;\" alt=\"\" title=\"\" />",
				preg_replace(
					'/(src|SRC|href|HREF|rev|REV)(\s*=\s*)(\'|")(#PICTURE' . $i . '#)/',
					'\1\2\3' . $imagePath . $pic,
					$tmplStr
				)
			);
			++$i;
		}
		# Uebriggebliebene #PICTUREx# loeschen
		$tmplStr = preg_replace('/<[^<]*(src|SRC|href|HREF|rev|REV)\s*=\s*(\'|")#PICTURE\d+#(\'|")[^>]*\/*>/', '', $tmplStr);
		$tmplStr = preg_replace('/#PICTURE\d+#/','', $tmplStr);
		$str = ml_restoreBase64($tmplStr, $undo);
		
		# ggf. leere image tags loeschen
		$str = preg_replace('/<img[^>]*src=(""|\'\')[^>]*>/i', '', $str);
		return $str;
	}
	
}
