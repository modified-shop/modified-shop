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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleImportOrders.php');

class TradoriaImportOrders extends MagnaCompatibleImportOrders {

	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);
	}
	
	protected function getConfigKeys() {
		$keys = parent::getConfigKeys();
		$keys['OrderStatusOpen'] = array (
			'key' => 'orderstatus.open',
			'default' => '',
		);
		return $keys;
	}
	
	protected function getMarketplaceOrderID() {
		return $this->o['orderInfo']['MOrderID'];
	}
	
	protected function getOrdersStatus() {
		return $this->config['OrderStatusOpen'];
	}
	
	protected function generateOrderComment() {
		return trim(
			sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP_SHORT, $this->marketplaceTitle)."\n".
			ML_LABEL_MARKETPLACE_ORDER_ID.': '.$this->getMarketplaceOrderID()."\n\n".
			$this->comment
		);
	}
	
	/**
	 * Converts the tradoria tax id to a tax value
	 *
	 * @parameter mixed $tax	Something that represents a tax value
	 * @return float			The actual tax value
	 * @TODO: Save the ID2Tax Array somewhere more globally or ask the allmigty API for it.
	 */
	protected function getTaxValue($tax) {
		if ($tax < 0) return (float)$this->config['MwStFallback'];
		return $tax;
	}

	protected function getItemTax($pID, $product, &$data) {
		$taxMatch = getDBConfigValue($this->marketplace.'.checkin.taxmatching', $this->mpID, array());
		if (is_array($taxMatch) && array_key_exists($product['products_tax_class_id'], $taxMatch)) {
			return $taxMatch[$product['products_tax_class_id']];
		}
		/* Fallback. This represents 19%. Should be make configureable in a datastructure. */
		return '1';
	}
	
	protected function generateOrdersStatusComment() {
		return $this->generateOrderComment();
	}
	
}
