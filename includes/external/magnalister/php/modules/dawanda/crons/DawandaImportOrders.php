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

class DawandaImportOrders extends MagnaCompatibleImportOrders {
	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);
	}

	protected function getConfigKeys() {
		$aConfigKeys = parent::getConfigKeys();
		$aConfigKeys['OrderStatusOpen'] = array (
			'key' => 'orderstatus.open',
			'default' => '2',
		);
		$aConfigKeys['PaymentMethod']['default'] = 'matching';
		return $aConfigKeys;
	}

	protected function getOrdersStatus() {
		return $this->config['OrderStatusOpen'];
	}

	protected function getPaymentMethod() {
		if ($this->config['PaymentMethod'] == 'matching') {
			return $this->getPaymentClassForDaWandaPaymentMethod($this->o['order']['payment_method']);
		}
		return $this->config['PaymentMethod'];
	}

	protected function getPaymentClassForDaWandaPaymentMethod($paymentMethod) {
		$paymentModules = explode(';', MODULE_PAYMENT_INSTALLED);
		$class = 'marketplace';
		/*
			'BankTransfer',+
			'CashOnDelivery',+
			'PayPal',+
			'Cash',+
			'DaWandaVoucher',
			'CreditCard',+
			'SofortUeberweisung',+
			'Maestro',
			'iDEAL',
			'EPS',
			'Przelewy24'
			TODO bei den letzten 4 schauen wie die module heissen (hamma ned anscheinend)
		*/
	
		if ('BankTransfer' == $paymentMethod) {
			# money order / Zahlungsanweisung / Vorkasse
			if (in_array('heidelpaypp.php', $paymentModules))
				$class = 'heidelpaypp';
			else if (in_array('moneyorder.php', $paymentModules))
				$class = 'moneyorder';
			else if (in_array('uos_vorkasse_modul.php', $paymentModules))
				$class = 'uos_vorkasse_modul';
		} else if ('CashOnDelivery' == $paymentMethod) {
			# Nachnahme
			if (in_array('cod.php', $paymentModules))
				$class = 'cod';
		} else if ('PayPal' == $paymentMethod) {
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
		} else if ('Cash' == $paymentMethod) {
			# Barzahlung
			if (in_array('cash.php', $paymentModules))
				$class = 'cash';
		} else if ('CreditCard' == $paymentMethod) {
			# Kreditkarte
			if (in_array('cc.php', $paymentModules))
				$class = 'cc';
			else if (in_array('heidelpaycc.php', $paymentModules))
				$class = 'heidelpaycc';
			else if (in_array('moneybookers_cc.php', $paymentModules))
				$class = 'moneybookers_cc';
			else if (in_array('uos_kreditkarte_modul.php', $paymentModules))
				$class = 'uos_kreditkarte_modul';
		} else if ('SofortUeberweisung' == $paymentMethod) {
			# SofortUeberweisung
			if (in_array('sofortueberweisung_direct.php', $paymentModules))
				$class = 'sofortueberweisung_direct';
			else if (in_array('pn_sofortueberweisung.php', $paymentModules))
				$class = 'pn_sofortueberweisung';
			else if (in_array('sofort_sofortueberweisung.php', $paymentModules))
				$class = 'sofort_sofortueberweisung';
		}
	
		return $class;
	}

}
