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

class RicardoSyncInventory extends MagnaCompatibleSyncInventory {

	// process every item only once (all variations are checked each time)
	protected $itemsProcessed = array();

	protected function identifySKU() {
		if (!empty($this->cItem['MasterSKU'])) {
			$this->cItem['pID'] = (int)magnaSKU2pID($this->cItem['MasterSKU'], true);
		} else {
			$this->cItem['pID'] = (int)magnaSKU2pID($this->cItem['SKU']);
		}
	}

	protected function initMLProduct() {
		parent::initMLProduct();
		MLProduct::gi()->useMultiDimensionalVariations(true);
		MLProduct::gi()->setOptions(array(
			'sameVariationsToAttributes' => false,
			'purgeVariations' => true,
			'useGambioProperties' => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties')
		));
	}

	protected function updateItem() {
		$this->cItem['SKU'] = trim($this->cItem['SKU']);
		if (empty($this->cItem['SKU'])) {
			$this->log("\nItemID " . $this->cItem['ItemID'] . ' has an emtpy SKU.');
			return;
		}

		@set_time_limit(180);
		$this->identifySKU();

		$title = isset($this->cItem['Title']) ? $this->cItem['Title'] : 'unknown';

		if ((int)$this->cItem['pID'] <= 0) {
			$this->log("\n" . $title . ' not found');
			return;
		} else {
			$this->log("\n" . $title . ' found (pID: ' . $this->cItem['pID'] . ')');
		}

		if (in_array($this->cItem['pID'], $this->itemsProcessed)) {
			$this->log("\n" . $title . ' already processed.');
			return;
		}

		// Get lang
		$langs = getDBConfigValue($this->marketplace.'.lang', $this->mpID);

		// Prepare product
		MLProduct::gi()->setLanguage(reset($langs));
		MLProduct::gi()->setPriceConfig(RicardoHelper::loadPriceSettings($this->mpID));
		MLProduct::gi()->setQuantityConfig(RicardoHelper::loadQuantitySettings($this->mpID));

		$product = MLProduct::gi()->getProductById($this->cItem['pID']);
		arrayEntitiesToUTF8($product);

		$bSyncStock = ($this->config['StockSync'] != 'no');
		$bSyncPrice = ($this->config['PriceSync'] != 'no');
		$bLeadTimeToShipSync = ($this->config['LeadTimeToShipSync'] === true);
		$data['Process'] = false;

		$data = array();
		// Copied from eBay. Is there any reason you don't use $this->cItem['SKU'] as SKU?
		$data['SKU'] = magnaPID2SKU($product['ProductId']);

		if ($bSyncStock) {
            if (   ($this->config['StatusMode'] === 'true')
                && ($product['Status'] == 0)
            ) {
                $product['QuantityTotal'] = 0;
                $product['Quantity'] = 0;
            }
			// Check Quantity variants or master. QuantityTotal is only set if product has variants
			if ((isset($this->cItem['Variations']) && isset($product['Variations'])) && isset($product['QuantityTotal'])) {
				$data['IncreaseQuantity'] = false;
				$data['NewQuantity'] = $product['QuantityTotal'];
				if ($product['QuantityTotal'] > $this->cItem['Quantity'] && $this->config['StockSync'] === 'auto_reduce') {
					$data['IncreaseQuantity'] = true;
				}
			} else {
				// If quantity is lower, update it
				if (isset($this->cItem['Quantity']) && $product['Quantity'] != $this->cItem['Quantity']) {
					$data['IncreaseQuantity'] = false;
					$data['NewQuantity'] = $product['Quantity'];
					$data['Process'] = true;
					if ($product['Quantity'] > $this->cItem['Quantity'] && $this->config['StockSync'] === 'auto_reduce') {
						$data['IncreaseQuantity'] = true;
					}
				}
			}
		}

		$productTax = SimplePrice::getTaxByPID($this->cItem['pID']);
		$taxFromConfig = getDBConfigValue($this->marketplace . '.checkin.mwst', $this->mpID);
		$priceSignalConfig = getDBConfigValue($this->marketplace . '.price.signal', $this->mpID);
		if ($bSyncPrice) {
			// Check Price master
			if (isset($this->cItem['Variations']) === false) {
				$price = $product['Price']['Price'];

				// If PriceReduced is set use this one
				if (isset($product['PriceReduced']['Price'])) {
					$price = $product['PriceReduced']['Price'];
				}
				
				if (isset($taxFromConfig) && $taxFromConfig !== '') {
                    $price = $price * 100 / (100 + $productTax);
                    $price = round($price * (($taxFromConfig + 100) / 100), 2);
					$price = $this->makeSignalPrice($price, $priceSignalConfig);
                }

                //Check if last digit (second decimal) is 0 or 5. If not set 5 as default last digit
                $price =
                    ((int)($price * 100) % 5) == 0
                        ? $price
                        : ((int)($price * 10) / 10) + 0.05
                ;

				$price = round($price, 2);
				// If price is lower, update it
				if (isset($price) && (float)$price != (float)$this->cItem['Price']) {
					$data['Price'] = $price;
					$data['Process'] = true;
					$data['IncreasePrice'] = false;
					if ($price > $this->cItem['Price'] && $this->config['PriceSync'] === 'auto_reduce') {
						$data['IncreasePrice'] = true;
					}
				}
			}
		}

		if (isset($this->cItem['Variations']) && isset($product['Variations'])) {
			$data['Variations'] = array();
			foreach ($product['Variations'] as $variantData) {
				$variant = array(
					'Process' => false
				);
				$variationSpecifics = array();
				foreach ($variantData['Variation'] as $specific) {
					$variationSpecifics[] = array(
						'Name' => $specific['Name'],
						'Value' => $specific['Value'],
					);
				}
				$variant['SKU'] = (getDBConfigValue('general.keytype', '0') == 'artNr') ? $variantData['MarketplaceSku'] : $variantData['MarketplaceId'];
				$cVariation = array();
				foreach ($this->cItem['Variations'] as $cVariation){
					if ($cVariation['SKU'] == $variant['SKU']) {
						break;
					}
				}

				if ($bSyncStock) {
                    if (   ($this->config['StatusMode'] === 'true')
                        && ($product['Status'] == 0)
                    ) {
                        $variantData['Quantity'] = 0;
                    }
					$variant['Quantity'] = $variantData['Quantity'];
					$variant['Process'] = true;
					$variant['IncreaseQuantity'] = false;
					if ((int)$variantData['Quantity'] > (int)$cVariation['Quantity'] && $this->config['StockSync'] === 'auto_reduce') {
						$variant['IncreaseQuantity'] = true;
					}
				}

				if ($bSyncPrice) {
					$price = $variantData['Price']['Price'];

					// If PriceReduced is set use this one
					if (isset($variantData['PriceReduced']['Price'])) {
						$price = $variantData['PriceReduced']['Price'];
					}
					
					if (isset($taxFromConfig) && $taxFromConfig !== '') {
						$price = $price * 100 / (100 + $productTax);
						$price = round($price * (($taxFromConfig + 100) / 100), 2);
						$price = $this->makeSignalPrice($price, $priceSignalConfig);
					}

                    //Check if last digit (second decimal) is 0 or 5. If not set 5 as default last digit
                    $price =
                        ((int)($price * 100) % 5) == 0
                            ? $price
                            : ((int)($price * 10) / 10) + 0.05
                    ;

					$price = round($price, 2);
					if ((float)$price !== (float)$cVariation['Price']) {
						$variant['Price'] = $price;
						$variant['Process'] = true;
						$variant['IncreasePrice'] = false;
						if ($price > $cVariation['Price'] && $this->config['PriceSync'] === 'auto_reduce') {
							$variant['IncreasePrice'] = true;
						}
					}
				}

				$variant['Variation'] = $variationSpecifics;
				$data['Variations'][] = $variant;
			}

		}

		if (isset($data['NewQuantity']) === true) {
			$this->log(
				"\n\tRicardo Quantity: " . $this->cItem['Quantity'] .
				"\n\tShop Main Quantity: " . $data['NewQuantity']
			);
		} else {
			$this->log("\n\t".
				'Quantity not changed (' . $this->cItem['Quantity'] . ')'
			);
		}

		if (isset($data['Price']) === true) {
			$this->log(
				"\n\tRicardo Price: " . $this->cItem['Price'] .
				"\n\tShop Price: " . $data['Price']
			);
		} else {
			$this->log("\n\t".
				'Price not changed (' . $this->cItem['Price'] . ')'
			);
		}

		// Log Variations
		if (isset($this->cItem['Variations']) && isset($product['Variations'])) {
			$this->log(
				"\n\tVariations:"
			);
			foreach ($this->cItem['Variations'] as $ricardoVariation) {
				foreach ($product['Variations'] as $aShopVariantData) {
					if ($ricardoVariation['SKU'] == $aShopVariantData[((getDBConfigValue('general.keytype', '0') == 'artNr') ? 'MarketplaceSku' : 'MarketplaceId')]) {
						$price = (isset($aShopVariantData['PriceReduced']['Price']) ? $aShopVariantData['PriceReduced']['Price'] : $aShopVariantData['Price']['Price']);
						if (isset($taxFromConfig) && $taxFromConfig !== '') {
							$price = $price * 100 / (100 + $productTax);
							$price = round($price * (($taxFromConfig + 100) / 100), 2);
						}
						
						$this->log(
							"\n\t\tVariation SKU: " . $ricardoVariation['SKU']
						);

						if (isset($aShopVariantData['Quantity']) === true) {
							$this->log(
								"\n\tRicardo Quantity: " . $ricardoVariation['Quantity'] .
								"\n\tShop Main Quantity: " . $aShopVariantData['Quantity']
							);
						} else {
							$this->log("\n\t".
								'Quantity not changed (' . $ricardoVariation['Quantity'] . ')'
							);
						}

						if (isset($price) === true) {
							$this->log(
								"\n\tRicardo Price: " . $ricardoVariation['Price'] .
								"\n\tShop Main Price: " . $price
							);
						} else {
							$this->log("\n\t".
								'Price not changed (' . $ricardoVariation['Price'] . ')'
							);
						}

						break;
					}
				}
			}
		}
		
		if (isset($this->cItem['Variations']) === true) {
			// Variation product update
			$variationsToUpdate = array();
			foreach($data['Variations'] as $variation) {
				if ($variation['Process'] === true) {
					unset($variation['Process']);
					unset($variation['Variation']);
					$variation['ParentSKU'] = $data['SKU'];
					foreach ($product['Variations'] as $aShopVariantData) {
						if ($bLeadTimeToShipSync && $variation['SKU'] == $aShopVariantData[((getDBConfigValue('general.keytype', '0') == 'artNr') ? 'MarketplaceSku' : 'MarketplaceId')]) {
							$variation['ShippingTime'] = $this->getShippingTime($product['ShippingTimeId']);
						}
					}
					$variationsToUpdate[] = $variation;
				}
			}

			if (count($variationsToUpdate) > 0) {
				$this->updateItems($variationsToUpdate);
			}
		} else if ($data['Process'] === true) {
			// Simple product update
			unset($data['Process']);
			if ($bLeadTimeToShipSync) {
                $data['ShippingTime'] = $this->getShippingTime($product['ShippingTimeId']);
			}
			$this->updateItems(array($data));
		}
		$this->itemsProcessed[] = $this->cItem['pID'];
	}
	
	protected function getShippingTime ($iShippingTimeId) {	
		$iDefaultShippingTime = getDBConfigValue($this->marketplace.'.checkin.availability', $this->mpID, false);
		return
			getDBConfigValue(array($this->marketplace.'.leadtimetoshipmatching.prefer', 'val'), $this->mpID, false)
			? getDBConfigValue(
				array($this->marketplace.'.leadtimetoshipmatching.values', $iShippingTimeId),
				$this->mpID,
				$iDefaultShippingTime
			)
			: $iDefaultShippingTime
		;
	}
	
	protected function getConfigKeys() {
        $aParent = parent::getConfigKeys();
        $aParent['LeadTimeToShipSync'] = array(
            'key' => array('inventorysync.leadtimetoship', 'val'),
            'default' => false,
        );
        return $aParent;
    }
    
	protected function isAutoSyncEnabled() {
		$this->syncStock = ($this->config['StockSync'] == 'auto') || ($this->config['StockSync'] == 'auto_reduce')  || ($this->config['StockSync'] == 'auto_fast');
		$this->syncPrice = ($this->config['PriceSync'] == 'auto') || ($this->config['PriceSync'] == 'auto_reduce');
		
		//$this->syncStock = $this->syncPrice = true;

		if (!($this->syncStock || $this->syncPrice)) {
			$this->log('== '.$this->marketplace.' ('.$this->mpID.'): no autosync =='."\n");
			return false;
		}
		$this->log(
			'== '.$this->marketplace.' ('.$this->mpID.'): '.
			'Sync stock: '.($this->syncStock ? 'true' : 'false').'; '.
			'Sync price: '.($this->syncPrice ? 'true' : 'false')." ==\n"
		);
		return true;
	}

	private function makeSignalPrice($price, $decimalDigits) {
		if (empty($decimalDigits)) {
			return $price;
		}

		//If price signal is single digit then just add price signal as last digit
		if (strlen((string)$decimalDigits) == 1) {
			$price = (0.1 * (int)($price * 10)) + ($decimalDigits / 100);
		} else {
			$price = ((int)$price) + ($decimalDigits / 100);
		}

		return $price;
	}
}
