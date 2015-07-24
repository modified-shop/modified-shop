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

class Ayn24ImportOrders extends MagnaCompatibleImportOrders {

	protected $multivariationsEnabled = true;

	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);
	}
	
	protected function getConfigKeys() {
		$keys = parent::getConfigKeys();
		$keys['OrderStatusOpen'] = array (
			'key' => 'orderstatus.open',
			'default' => '',
		);
		
		$keys['ShippingMethodName']['default'] = 'dhlayn24';
		$keys['PaymentMethodName']['default'] = 'Ayn24';
		
		return $keys;
	}
	
	protected function initConfig() {
		parent::initConfig();
		$this->config['FallbackCountryID'] = MagnaDB::gi()->fetchOne('
			SELECT countries_id FROM '.TABLE_COUNTRIES.' LIMIT 1
		');
	}
	
	protected function getOrdersStatus() {
		return $this->config['OrderStatusOpen'];
	}
	
	protected function getCountryByISOCode($code, $fallbackName = '') {
		$country = parent::getCountryByISOCode($code, $fallbackName);
		if ($country['ID'] == 0) {
			$country['ID'] = $this->config['FallbackCountryID'];
		}
		return $country;
	}
	
}
