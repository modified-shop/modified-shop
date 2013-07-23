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

class YategoImportOrders extends MagnaCompatibleImportOrders {

	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);
	}
	
	protected function getPaymentClassForPaymentMethod($paymentMethod) {
		$PaymentModules = explode(';', MODULE_PAYMENT_INSTALLED);
	    $class = 'yatego';
	
	    if ((stripos($paymentMethod, 'Vorauskasse') !== false) OR (stripos($paymentMethod, 'Vorkasse') !== false)) {
	        # Vorkasse
	        if (in_array('heidelpaypp.php', $PaymentModules))
	            $class = 'heidelpaypp';
	        if (in_array('moneyorder.php', $PaymentModules))
	            $class = 'moneyorder';
	        if (in_array('uos_vorkasse_modul.php', $PaymentModules))
	            $class = 'uos_vorkasse_modul';
	        
	    } else if (stripos($paymentMethod, 'Nachnahme') !== false) {
	        # Nachnahme
	        if (in_array('cod.php', $PaymentModules))
	            $class = 'cod';
	        
	    } else if (stripos($paymentMethod, 'Kreditkarte') !== false) {
	        # Kreditkarte
	        if (in_array('cc.php', $PaymentModules))
	            $class = 'cc';
	        if (in_array('heidelpaycc.php', $PaymentModules))
	            $class = 'heidelpaycc';
	        if (in_array('moneybookers_cc.php', $PaymentModules))
	            $class = 'moneybookers_cc';
	        if (in_array('uos_kreditkarte_modul.php', $PaymentModules))
	            $class = 'uos_kreditkarte_modul';
	    } else if ((stripos($paymentMethod, 'Bankeinzug') !== false) OR 
	               (stripos($paymentMethod, 'Lastschrift') !== false) OR 
	               (stripos($paymentMethod, 'ELV') !== false) OR 
	               (stripos($paymentMethod, 'LSV') !== false)
	    ) {
	        # Lastschrift
	        if (in_array('banktransfer.php', $PaymentModules))
	            $class = 'banktransfer';
	        if (in_array('heidelpaydd.php', $PaymentModules))
	            $class = 'heidelpaydd';
	        if (in_array('ipaymentelv.php', $PaymentModules))
	            $class = 'ipaymentelv';
	        if (in_array('moneybookers_elv.php', $PaymentModules))
	            $class = 'moneybookers_elv';
	        if (in_array('uos_lastschrift_de_modul.php', $PaymentModules))
	            $class = 'uos_lastschrift_de_modul';
	        
	    } else if (stripos($paymentMethod, 'paypal') !== false) {
	        # PayPal
	        if (in_array('paypal.php', $PaymentModules))
	            $class = 'paypal';
	        
	    } else if (stripos($paymentMethod, 'Rechnung') !== false) {
	        # Auf Rechnung
	        if (in_array('invoice.php', $PaymentModules))
	            $class = 'invoice';
	    } else if ((stripos($paymentMethod, 'Bar') !== false) OR (stripos($paymentMethod, 'Cash') !== false)) {
	        # Barzahlung
	        if (in_array('cash.php', $PaymentModules))
	            $class = 'cash';
	    }
	
	    return $class;
	}

	
	protected function getConfigKeys() {
		$keys = parent::getConfigKeys();
		$keys['OrderStatusOpen'] = array (
			'key' => 'orderstatus.open',
			'default' => '2',
		);
		$keys['PaymentMethod']['default'] = 'matching';
		return $keys;
	}
	
	protected function processSingleOrder() {
		parent::processSingleOrder();
	}
	
	protected function getMarketplaceOrderID() {
		return $this->o['orderInfo']['MShopOrderID'];
	}
	
	protected function getOrdersStatus() {
		return $this->config['OrderStatusOpen'];
	}
	
	protected function generateOrderComment() {
		return trim(
			sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP_SHORT, $this->marketplaceTitle)."\n".
			ML_LABEL_MARKETPLACE_ORDER_ID.': '.$this->o['orderInfo']['MShopOrderID'].' ('.$this->o['orderInfo']['MOrderID'].")\n\n".
			$this->comment
		);
	}
	
	protected function generateOrdersStatusComment() {
		return $this->generateOrderComment();
	}

	protected function doBeforeInsertOrder() {
		if ($this->config['PaymentMethod'] == 'matching') {
			$this->o['order']['payment_method'] = $this->getPaymentClassForPaymentMethod($this->o['orderInfo']['PaymentMethod']);
			if (SHOPSYSTEM != 'oscommerce') {
				$this->o['order']['payment_class'] = $this->o['order']['payment_method'];
			}
		}
	}	

	protected function doBeforeInsertMagnaOrder() {	
		return array();
	}

	protected function insertProduct() {
		if (isset($this->p['SKU'])) {
			$this->p['products_id'] = $this->p['products_model'] = $this->p['SKU'];
			unset($this->p['SKU']);
		}
		parent::insertProduct();
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

	protected function addCurrentOrderToProcessed() {
		$this->processedOrders[] = array (
			'MOrderID' => $this->o['orderInfo']['MOrderID'],
			'ShopOrderID' => $this->cur['OrderID'],
		);
	}

}
