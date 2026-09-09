<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleImportOrders.php');

class TemuImportOrders extends MagnaCompatibleImportOrders {

	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);

		$sOptions = getDBConfigValue('general.options', '0', 'old');
		if (strpos($sOptions, 'multivariation') !== false) {
			$this->multivariationsEnabled = true;
		}
		if (strpos($sOptions, 'gambioproperties') !== false) {
			$this->gambioPropertiesEnabled = true;
		}
	}

	protected function getConfigKeys() {
		return array_merge(parent::getConfigKeys(), array(
			'OrderStatusOpen' => array('key' => 'orderstatus.open', 'default' => '2'),
			'OrderStatusShipped' => array('key' => 'orderstatus.shipped', 'default' => ''),
			'OrderStatusCancelled' => array('key' => 'orderstatus.cancelled', 'default' => ''),
			'StockSync' => array('key' => 'stocksync.frommarketplace', 'default' => 'no'),
			'Mail' => array('key' => 'mail.send', 'default' => 'true'),
			'TemuPromotionsDiscountTemuSKU' => array('key' => 'orderimport.temupromotionsdiscount.temu_sku', 'default' => '__TEMU_DISCOUNT__'),
			'TemuPromotionsDiscountSellerSKU' => array('key' => 'orderimport.temupromotionsdiscount.seller_sku', 'default' => '__SELLER_DISCOUNT__'),
		));
	}

	/**
	 * Temu Promotion discounts: Temu emits discount line items with the default SKUs
	 * "__TEMU_DISCOUNT__" (funded by Temu) and "__SELLER_DISCOUNT__" (funded by the seller).
	 * Remap them to the item numbers configured in "Temu Promotion" so the discount is
	 * imported as a separate order item (mirrors AmazonImportOrders::insertProduct()).
	 */
	protected function insertProduct() {
		if ($this->p['products_model'] == '__TEMU_DISCOUNT__') {
			$this->p['products_model'] = $this->config['TemuPromotionsDiscountTemuSKU'];
		}
		if ($this->p['products_model'] == '__SELLER_DISCOUNT__') {
			$this->p['products_model'] = $this->config['TemuPromotionsDiscountSellerSKU'];
		}

		parent::insertProduct();
	}

	protected function getBaseRequest() {
		$aRequest = parent::getBaseRequest();

		$bImportPending = getDBConfigValue('temu.orders.import.pending', $this->mpID, false);
		if ($bImportPending) {
			$aRequest['DATA']['parentOrderStatusList'] = array(1, 2, 3);
		} else {
			$aRequest['DATA']['parentOrderStatusList'] = array(2, 3);
		}

		return $aRequest;
	}

	protected function manipulateOrderData(&$aOrderData) {
		$bBlacklisting = getDBConfigValue('temu.orderimport.blacklisting', $this->mpID, false);
		if ($bBlacklisting) {
			if (isset($aOrderData['AddressSets']) && is_array($aOrderData['AddressSets'])) {
				foreach ($aOrderData['AddressSets'] as &$aAddress) {
					if (isset($aAddress['EMail']) && strpos($aAddress['EMail'], 'blacklisted-') !== 0) {
						$aAddress['EMail'] = 'blacklisted-' . $aAddress['EMail'];
					}
				}
				unset($aAddress);
			}
		}
	}

	protected function hasReduceStock() {
		return true;
	}

    protected function getOrdersStatus()
    {
        return $this->config['OrderStatusOpen'];
    }
}
