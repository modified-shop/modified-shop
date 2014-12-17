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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncInventory.php');
require_once(DIR_MAGNALISTER_MODULES.'bepado/BepadoHelper.php');

class BepadoSyncInventory extends MagnaCompatibleSyncInventory {
	protected $priceConfig = array();
	
	protected function initMLProduct() {
		parent::initMLProduct();
		
		// Set Price and Quantity settings
		$this->priceConfig = BepadoHelper::loadPriceSettings($this->mpID);
		MLProduct::gi()->setPriceConfig($this->priceConfig);
		MLProduct::gi()->setQuantityConfig(BepadoHelper::loadQuantitySettings($this->mpID));
	}


	protected function updatePurchasePrice() {
		if (!$this->syncPrice) {
			return false;
		}
		if ($this->cItem['PurchasePrice'] < 0) {
			return false;
		}
		
		$data = false;
		
		$price = $this->simplePrice
				->setFinalPriceFromDB($this->cItem['pID'], $this->mpID, $this->priceConfig['PurchasePrice'])
				->getPrice();

		if (($price > 0) && ((float)$this->cItem['PurchasePrice'] != $price)) {
			$this->log("\n\t".
				'PurchasePrice changed (old: '.$this->cItem['PurchasePrice'].'; new: '.$price.')'
			);
			$data = $price;
		} else {
			$this->log("\n\t".
				'PurchasePrice not changed ('.$price.')'
			);
		}
		return $data;
	}

	protected function updateCustomFields(&$data) {
		$pU = $this->updatePurchasePrice();
		if ($pU !== false) {
			$data['PurchasePrice'] = $pU;
		}
	}
	
}
