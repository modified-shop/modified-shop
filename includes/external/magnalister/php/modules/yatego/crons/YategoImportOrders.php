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

	protected function getPaymentClassForPaymentMethod($paymentMethod) {
		$paymentModules = explode(';', MODULE_PAYMENT_INSTALLED);
		$class = 'yatego';
		
		if ((stripos($paymentMethod, 'Vorauskasse') !== false) OR (stripos($paymentMethod, 'Vorkasse') !== false)) {
			# Vorkasse
			if (in_array('heidelpaypp.php', $paymentModules))
				$class = 'heidelpaypp';
			if (in_array('moneyorder.php', $paymentModules))
				$class = 'moneyorder';
			if (in_array('uos_vorkasse_modul.php', $paymentModules))
				$class = 'uos_vorkasse_modul';
			
		} else if (stripos($paymentMethod, 'Nachnahme') !== false) {
			# Nachnahme
			if (in_array('cod.php', $paymentModules))
				$class = 'cod';
			
		} else if (stripos($paymentMethod, 'Kreditkarte') !== false) {
			# Kreditkarte
			if (in_array('cc.php', $paymentModules))
				$class = 'cc';
			if (in_array('heidelpaycc.php', $paymentModules))
				$class = 'heidelpaycc';
			if (in_array('moneybookers_cc.php', $paymentModules))
				$class = 'moneybookers_cc';
			if (in_array('uos_kreditkarte_modul.php', $paymentModules))
				$class = 'uos_kreditkarte_modul';
		} else if ((stripos($paymentMethod, 'Bankeinzug') !== false) OR 
				   (stripos($paymentMethod, 'Lastschrift') !== false) OR 
				   (stripos($paymentMethod, 'ELV') !== false) OR 
				   (stripos($paymentMethod, 'LSV') !== false)
		) {
			# Lastschrift
			if (in_array('banktransfer.php', $paymentModules))
				$class = 'banktransfer';
			if (in_array('heidelpaydd.php', $paymentModules))
				$class = 'heidelpaydd';
			if (in_array('ipaymentelv.php', $paymentModules))
				$class = 'ipaymentelv';
			if (in_array('moneybookers_elv.php', $paymentModules))
				$class = 'moneybookers_elv';
			if (in_array('uos_lastschrift_de_modul.php', $paymentModules))
				$class = 'uos_lastschrift_de_modul';
			
		} else if (stripos($paymentMethod, 'paypal') !== false) {
            # PayPal
            if (in_array('paypal.php', $paymentModules))
                $class = 'paypal';
            else if (in_array('paypalng.php', $paymentModules))
                $class = 'paypalng';
            else if (in_array('paypal_ipn.php', $paymentModules))
                $class = 'paypal_ipn';
            else if (in_array('paypalexpress.php', $paymentModules))
                $class = 'paypalexpress';
            else if (in_array('paypal3.php', $paymentModules))
                $class = 'paypal3';
            else if (in_array('paypalclassic.php', $paymentModules))
                $class = 'paypalclassic';
            else if (in_array('paypalplus.php', $paymentModules))
                $class = 'paypalplus';
            else if (in_array('paypallink.php', $paymentModules))
                $class = 'paypallink';
            else if (in_array('paypalpluslink.php', $paymentModules))
                $class = 'paypalpluslink';
            else if (in_array('paypalgambio_alt.php', $paymentModules))
                $class = 'paypalgambio_alt';
            else if (in_array('wcp_paypal.php', $paymentModules))
                $class = 'wcp_paypal';
		} else if (stripos($paymentMethod, 'Rechnung') !== false) {
			# Auf Rechnung
			if (in_array('invoice.php', $paymentModules))
				$class = 'invoice';
		} else if ((stripos($paymentMethod, 'Bar') !== false) OR (stripos($paymentMethod, 'Cash') !== false)) {
			# Barzahlung
			if (in_array('cash.php', $paymentModules))
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
	
	protected function getMarketplaceOrderID() {
		return $this->o['orderInfo']['MShopOrderID'];
	}
	
	protected function getOrdersStatus() {
		return $this->config['OrderStatusOpen'];
	}
	
	protected function generateOrderComment($blForce = true) {
		if (!$blForce && !getDBConfigValue(array('general.order.information', 'val'), 0, true)) {
			return ''; 
		}
		return trim(
			sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP_SHORT, $this->marketplaceTitle)."\n".
			ML_LABEL_MARKETPLACE_ORDER_ID.': '.$this->o['orderInfo']['MShopOrderID'].' ('.$this->o['orderInfo']['MOrderID'].")\n\n".
			$this->comment
		);
	}
	
	protected function generateOrdersStatusComment() {
		return $this->generateOrderComment(true);
	}

	protected function doBeforeInsertOrder() {
		if ($this->config['PaymentMethod'] == 'matching') {
			$this->o['order']['payment_method'] = $this->getPaymentClassForPaymentMethod($this->o['orderInfo']['PaymentMethod']);
			if (SHOPSYSTEM != 'oscommerce') {
				$this->o['order']['payment_class'] = $this->o['order']['payment_method'];
			}
		}
	}

	protected function insertProduct() {
		if (isset($this->p['SKU'])) {
			$this->p['products_id'] = $this->p['products_model'] = $this->p['SKU'];
			unset($this->p['SKU']);
		}
		parent::insertProduct();
	}

	protected function addCurrentOrderToProcessed() {
		$this->processedOrders[] = array (
			'MOrderID' => $this->o['orderInfo']['MOrderID'],
			'ShopOrderID' => $this->cur['OrderID'],
		);
		$this->out($this->marketplace.' ('.$this->mpID.') order '.$this->o['orderInfo']['MOrderID'].' imported with No. '.$this->cur['OrderID']."\n");
	}

}
