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

class RicardoImportOrders extends MagnaCompatibleImportOrders {
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
		if ($this->config['PaymentMethod'] == 'matching') {
			return $this->getPaymentClassForRicardoPaymentMethod($this->o['order']['payment_method']);
		}

		return $this->config['PaymentMethod'];
	}

	/**
	 * Converts the tax value to an ID
	 *
	 * @parameter mixed $tax	Something that represents a tax value
	 * @return float			The actual tax value
	 * @TODO: Save the ID2Tax Array somewhere more globally or ask the allmigty API for it.
	 */
	protected function getTaxValue($tax) {
		if ($tax < 0) {
			return (float)$this->config['MwStFallback'];
		}

		return $tax;
	}

	private function getPaymentClassForRicardoPaymentMethod($paymentMethod) {
		$PaymentModules = explode(';', MODULE_PAYMENT_INSTALLED);
		$class = 'marketplace';

		if ((strpos($paymentMethod, 'Kreditkarte') === 0)
			|| ('Visa' == $paymentMethod) || ('AmericanExpress' == $paymentMethod)) {
			# Kreditkarte
			if (in_array('cc.php', $PaymentModules)){
				$class = 'cc';
			} else if (in_array('heidelpaycc.php', $PaymentModules)) {
				$class = 'heidelpaycc';
			} else if (in_array('moneybookers_cc.php', $PaymentModules)) {
				$class = 'moneybookers_cc';
			} else if (in_array('uos_kreditkarte_modul.php', $PaymentModules)) {
				$class = 'uos_kreditkarte_modul';
			} else if (in_array('SI_cc.php', $PaymentModules)) {
				$class = 'SI_cc';
			} else if (in_array('sinternetkasse_ccfs.php', $PaymentModules)) {
				$class = 'sinternetkasse_ccfs';
			}
		} else if (    (strpos($paymentMethod, 'Voraus') !== false) 
		            || (strpos($paymentMethod, 'Vorkasse') !== false)) {
			# Vorkasse
			if (in_array('moneyorder.php', $PaymentModules)) {
				$class = 'moneyorder';
			}
		} else if ('Moneybookers' == $paymentMethod) {
			# Moneybookers
			if (in_array('moneybookers.php', $PaymentModules)) {
				$class = 'moneybookers';
			}
		} else if (    (strpos($paymentMethod, 'Barzahlung') !== false)
		            || (strpos($paymentMethod, 'bei Abholung') !== false)) {
			# Barzahlung
			if (in_array('cash.php', $PaymentModules)) {
				$class = 'cash';
			}
		} else if (!empty($paymentMethod)) {
			$class = $paymentMethod;
		}

		return $class;
	}
}
