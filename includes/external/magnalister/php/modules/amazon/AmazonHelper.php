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

class AmazonHelper extends MagnaCompatibleHelper {
	public static function processCheckinErrors($result, $mpID) {
		// Empty is ok, the API has a method to fetch the error log later.
	}

	public static function loadPriceSettings($mpId) {
		$mp = magnaGetMarketplaceByID($mpId);

		$config = array(
			'AddKind' => getDBConfigValue($mp.'.price.addkind', $mpId, 'percent'),
			'Factor'  => (float)getDBConfigValue($mp.'.price.factor', $mpId, 0),
			'Signal'  => getDBConfigValue($mp.'.price.signal', $mpId, ''),
			'Group'   => getDBConfigValue($mp.'.price.group', $mpId, ''),
			'UseSpecialOffer' => getDBConfigValue(array($mp.'.price.usespecialoffer', 'val'), $mpId, false),
			'Currency' => getCurrencyFromMarketplace($mpId),
			'ConvertCurrency' => getDBConfigValue(array($mp.'.exchangerate', 'update'), $mpId, false),
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

}