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

class LafeoImportOrders extends MagnaCompatibleImportOrders {

	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);
	}
	
	protected function getConfigKeys() {
		$keys = parent::getConfigKeys();
		$keys['OrderStatusOpen'] = array (
			'key' => 'orderstatus.open',
			'default' => '2',
		);
		$keys['OrderStatusUnpaid'] = array (
			'key' => 'orderstatus.unpaid',
			'default' => '1',
		);
		$keys['ImportUnpaid'] = array (
			'key' => 'import.unpaid',
			'default' => 'false',
		);
		return $keys;
	}
	
	protected function processSingleOrder() {
		if (($this->config['ImportUnpaid'] != 'true') && !$this->o['orderInfo']['PaymentCompleted']) {
			/* Skip unpaid orders. */
			if ($this->verbose) echo 'Skip Order '.$this->getMarketplaceOrderID().': Payment incomplete.'."\n";
			return;
		}
		parent::processSingleOrder();
	}
	
	protected function getMarketplaceOrderID() {
		return $this->o['orderInfo']['MOrderID'];
	}
	
	protected function getOrdersStatus() {
		return $this->o['orderInfo']['PaymentCompleted'] ? $this->config['OrderStatusOpen'] : $this->config['OrderStatusUnpaid'];
	}
	
	protected function generateOrderComment($blForce = false) {
		if (!$blForce && !getDBConfigValue(array('general.order.information', 'val'), 0, true)) {
			return ''; 
		}
		return trim(
			sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP_SHORT, $this->marketplaceTitle)."\n".
			ML_LABEL_MARKETPLACE_ORDER_ID.': '.$this->getMarketplaceOrderID()."\n\n".
			$this->comment
		);
	}
	
	protected function generateOrdersStatusComment() {
		return $this->generateOrderComment(true);
	}
	
	protected function doBeforeInsertMagnaOrder() {
		$sDay = '';
		
		$this->o['orderInfo']['ShippingTimeMin'] = (int)$this->o['orderInfo']['ShippingTimeMin'];
		$this->o['orderInfo']['ShippingTimeMax'] = (int)$this->o['orderInfo']['ShippingTimeMax'];
		
		if ($this->o['orderInfo']['ShippingTimeMax'] > 0) {
			if (   ($this->o['orderInfo']['ShippingTimeMin'] > 0) 
				&& ($this->o['orderInfo']['ShippingTimeMin'] != $this->o['orderInfo']['ShippingTimeMax'])
			) {
				$sDay = $this->o['orderInfo']['ShippingTimeMin'].' - ';
			}
			$sDay .= $this->o['orderInfo']['ShippingTimeMax'];
		}
		
		if (!empty($sDay)) {
			$this->o['magnaOrders']['ML_LABEL_MARKETPLACE_SHIPPING_TIME'] = sprintf(
				ML_LABEL_MARKETPLACE_SHIPPING_TIME_VALUE, $sDay
			);
		}
		return array();
	}

	/**
	 * Converts the tax value to an ID
	 *
	 * @parameter mixed $tax	Something that represents a tax value
	 * @return float			The actual tax value
	 * @TODO: Save the ID2Tax Array somewhere more globally or ask the allmigty API for it.
	 */
	protected function getTaxValue($tax) {
		if ($tax < 0) return (float)$this->config['MwStFallback'];
		return $tax;
	}

}
