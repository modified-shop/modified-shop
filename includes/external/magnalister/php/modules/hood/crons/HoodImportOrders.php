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

class HoodImportOrders extends MagnaCompatibleImportOrders {

	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);
		$this->gambioPropertiesEnabled = (getDBConfigValue('general.options', '0', 'old') === 'gambioProperties');
	}

	protected function getConfigKeys() {
		$keys = parent::getConfigKeys();
		$keys['OrderStatusOpen'] = array (
			'key' => 'orderstatus.open',
			'default' => '2',
		);
//		$keys['OrderStatusUnpaid'] = array (
//			'key' => 'orderstatus.unpaid',
//			'default' => '1',
//		);
//		$keys['ImportUnpaid'] = array (
//			'key' => 'import.unpaid',
//			'default' => 'false',
//		);
		return $keys;
	}
	
	protected function processSingleOrder() {
//		if (($this->config['ImportUnpaid'] != 'true') && !$this->o['orderInfo']['PaymentCompleted']) {
//			/* Skip unpaid orders. */
//			if ($this->verbose) echo 'Skip Order '.$this->getMarketplaceOrderID().': Payment incomplete.'."\n";
//			return;
//		}
		parent::processSingleOrder();
	}
	
	protected function getMarketplaceOrderID() {
		return $this->o['orderInfo']['MOrderID'];
	}
	
	protected function getOrdersStatus() {
//		return $this->o['orderInfo']['PaymentCompleted'] ? $this->config['OrderStatusOpen'] : $this->config['OrderStatusUnpaid'];
		return $this->config['OrderStatusOpen'];
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
	
	/**
	 * Returs the payment method for the current order.
	 * @return string
	 */
	protected function getPaymentMethod() {
		if ($this->config['PaymentMethod'] == 'matching') {
			$paymentMethod = $this->o['order']['payment_method'];
			$paymentModules = explode(';', MODULE_PAYMENT_INSTALLED);
			
			$class = 'marketplace';
			if (stripos($paymentMethod, 'sofortueberweisung') !== false) {
				if (in_array('pn_sofortueberweisung.php', $paymentModules))
					$class = 'pn_sofortueberweisung';
				if (in_array('moneybookers_sft.php', $paymentModules))
					$class = 'moneybookers_sft';
				
			} else if (stripos($paymentMethod, 'Moneybookers') !== false) {
				if (in_array('moneybookers.php', $paymentModules))
					$class = 'moneybookers';
				if (in_array('amoneybookers.php', $paymentModules))
					$class = 'amoneybookers';
				
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
				
			} else if ((stripos($paymentMethod, 'Barzahlung') !== false)) {
				# Barzahlung
				if (in_array('cash.php', $paymentModules))
					$class = 'cash';
				
			} else if (stripos($paymentMethod, 'billSafe') !== false) {
				# PayPal
				if (in_array('billsafe_2.php', $paymentModules))
					$class = 'billsafe_2';
				if (in_array('billsafe_3_invoice.php', $paymentModules))
					$class = 'billsafe_3_invoice';
				if (in_array('billsafe_3_installment.php', $paymentModules))
					$class = 'billsafe_3_installment';
			
			} else if (stripos($paymentMethod, 'Amazon') !== false) {
				# Amazon Pay
				if (in_array('amazonadvpay.php', $paymentModules))
					$class = 'amazonadvpay';
			
			} else if (stripos($paymentMethod, 'Bezahlung per Nachnahme') !== false) {
				# Nachnahme
				if (in_array('cod.php', $paymentModules))
					$class = 'cod';
			
			} else if (stripos($paymentMethod, 'Bezahlung per Überweisung') !== false) {
				# Vorkasse
				if (in_array('moneyorder.php', $paymentModules))
					$class = 'moneyorder';
				if (in_array('uos_vorkasse_modul.php', $paymentModules))
					$class = 'uos_vorkasse_modul';
				
			} else if (stripos($paymentMethod, 'Kauf auf Rechnung') !== false) {
				# Rechnung
				if (in_array('invoice.php', $paymentModules))
					$class = 'invoice';
				
			}
			return $class;
		}
		return $this->config['PaymentMethod'];
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
