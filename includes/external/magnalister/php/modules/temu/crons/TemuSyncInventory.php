<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncInventory.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/TemuHelper.php');

class TemuSyncInventory extends MagnaCompatibleSyncInventory {

	public function __construct($mpID, $marketplace, $limit = 100) {
		parent::__construct($mpID, $marketplace, $limit);
	}

	protected function calcNewQuantity() {
		if ($this->config['QuantityType'] == 'infinity') {
			return -1;
		}
		return parent::calcNewQuantity();
	}

	public function process() {
		parent::process();
	}

	/**
	 * Temu requires the NET price. The base updatePrice() calls
	 * SimplePrice::finalizePrice() without a config, which defaults to IncludeTax=true (GROSS).
	 * Override to pass the Temu price config (IncludeTax=false) as the $extra argument so the
	 * synced price is NET — same value the AddItems/VerifyAddItems payload sends. Mirrors the
	 * MetroSyncInventory net pattern.
	 */
	protected function updatePrice() {
		if (!$this->syncPrice) return false;

		$data = false;
		$aNetConfig = TemuHelper::loadPriceSettings($this->mpID); // valid config, IncludeTax=false → NET

		if ($this->blMultiDimVariations && $this->config['VarType'] == 'old') {
			if ($this->cItem['aID'] > 0) {
				if (!in_array($this->cItem['pID'], $this->aPidVariationsCalculated)) {
					setProductVariations($this->cItem['pID'], getDBConfigValue($this->marketplace.'.lang', $this->mpID));
					$this->aPidVariationsCalculated[] = $this->cItem['pID'];
				}
				$varPriceAdd = MagnaDB::gi()->fetchOne('
					SELECT variation_price FROM '.TABLE_MAGNA_VARIATIONS.'
					WHERE products_id = \''.$this->cItem['pID'].'\'
					'.(($this->config['KeyType'] == 'artNr')
					? 'AND marketplace_sku = \''.$this->cItem['SKU'].'\''
					: 'AND marketplace_id = \''.$this->cItem['SKU'].'\'')
				);
			} else {
				$varPriceAdd = 0;
			}
			$price = $this->simplePrice
				->setPriceFromDB($this->cItem['pID'], $this->mpID)
				->addLump($varPriceAdd)
				->finalizePrice($this->cItem['pID'], $this->mpID, $aNetConfig)
				->getPrice();
		} else {
			$price = $this->simplePrice
				->setPriceFromDB($this->cItem['pID'], $this->mpID)
				->addAttributeSurcharge($this->cItem['aID'])
				->finalizePrice($this->cItem['pID'], $this->mpID, $aNetConfig)
				->getPrice();
		}

		if (($price > 0) && ((float)$this->cItem['Price'] != $price)) {
			$this->log("\n\t".'Price (net) changed (old: '.$this->cItem['Price'].'; new: '.$price.')');
			$data = $price;
		} else {
			$this->log("\n\t".'Price (net) not changed ('.$price.')');
		}
		return $data;
	}

	/**
	 * The base sync writes the price into $data['Price']; Temu's UpdateInventory API expects it
	 * under `BasePrice` (net), consistent with the AddItems payload. Rename it here (Temu-only,
	 * no change to the shared base class).
	 */
	protected function updateCustomFields(&$data) {
		if (array_key_exists('Price', $data)) {
			$data['BasePrice'] = $data['Price'];
			unset($data['Price']);
		}
	}

	protected function uploadItems() {
		/* Do nothing - upload handled by checkin only */
	}
}
