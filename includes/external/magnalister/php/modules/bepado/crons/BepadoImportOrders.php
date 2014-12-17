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

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleImportOrders.php');

class BepadoImportOrders extends MagnaCompatibleImportOrders {
	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);
	}

	protected function getConfigKeys() {
		$aConfigKeys = parent::getConfigKeys();
		$aConfigKeys['OrderStatusOpen'] = array (
			'key' => 'orderstatus.open',
			'default' => '2',
		);
		$aConfigKeys['PaymentMethod']['default'] = 'marketplace';
		return $aConfigKeys;
	}

	protected function getOrdersStatus() {
		return $this->config['OrderStatusOpen'];
	}

	protected function getPaymentMethod() {
		return $this->config['PaymentMethod'];
	}

	/* Bepado privides ISO3 country codes */
	protected function getCountryByISOCode($code, $fallbackName = '') {
		$c = MagnaDB::gi()->fetchRow('
			SELECT countries_id as ID, countries_name AS Name
			  FROM '.TABLE_COUNTRIES.'
			 WHERE countries_iso_code_3="'.$code.'" 
			 LIMIT 1
		');
		if (!is_array($c)) {
			$c = array (
				'ID' => 0,
				'Name' => empty($fallbackName) ? $code : $fallbackName,
			);
		}
		return $c;
	}

	/* replace ISO3 codes by ISO2 for the order table */
	protected function doBeforeInsertOrder() {
		$this->o['order']['billing_country_iso_code_2'] = $this->countryIso3ToIso2($this->o['order']['billing_country_iso_code_3']);
		unset($this->o['order']['billing_country_iso_code_3']);
		$this->o['order']['delivery_country_iso_code_2'] = $this->countryIso3ToIso2($this->o['order']['delivery_country_iso_code_3']);
		unset($this->o['order']['delivery_country_iso_code_3']);
	}

	private function countryIso3ToIso2($iso3) {
		return MagnaDB::gi()->fetchOne('
			SELECT countries_iso_code_2
			  FROM '.TABLE_COUNTRIES.'
			 WHERE countries_iso_code_3="'.$iso3.'" 
			 LIMIT 1
		');
	}

}
