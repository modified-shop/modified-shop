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

class Check24SyncInventory extends MagnaCompatibleSyncInventory {
	protected function identifySKU() {
		if (!empty($this->cItem['MasterSKU'])) {
			$this->cItem['pID'] = (int)magnaSKU2pID($this->cItem['MasterSKU'], true);
		} else {
			$this->cItem['pID'] = (int)magnaSKU2pID($this->cItem['SKU']);
		}
	}

	protected function updateItem() {
		$this->cItem['SKU'] = trim($this->cItem['SKU']);
		if (empty($this->cItem['SKU'])) {
			$this->log("\nItemID " . $this->cItem['ItemID'] . ' has an emtpy SKU.');
			return;
		}

		@set_time_limit(180);
		$this->identifySKU();

		if (($hp = magnaContribVerify('Check24SyncInventory_PreUpdateItem', 1)) !== false) {
			require($hp);
		}

		$title = isset($this->cItem['Title']) ? $this->cItem['Title'] : 'unknown';

		if ((int)$this->cItem['pID'] <= 0) {
            $this->log("\n".
                'SKU: '.$this->cItem['SKU'].' ('.$title.') not found (Marketplace: '.$this->marketplaceTitle.')'
            );
            return;
		} else {
            $this->log("\n".
                'SKU: '.$this->cItem['SKU'].' ('.$title.') found ('.
                'pID: '.$this->cItem['pID'].'; aID: '.$this->cItem['aID'].'; Marketplace: '.$this->marketplaceTitle.')'
            );
		}

		// Prepare product
		MLProduct::gi()->setLanguage(getDBConfigValue($this->marketplace . '.lang', $this->mpID));
		MLProduct::gi()->setPriceConfig(Check24Helper::loadPriceSettings($this->mpID));
		MLProduct::gi()->setQuantityConfig(Check24Helper::loadQuantitySettings($this->mpID));
        MLProduct::gi()->useMultiDimensionalVariations(true);
        MLProduct::gi()->setOptions(array(
            'sameVariationsToAttributes' => false,
            'purgeVariations' => true,
            'useGambioProperties' => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties')
        ));

		$product = MLProduct::gi()->getProductById($this->cItem['pID']);
		arrayEntitiesToUTF8($product);

		$bSyncStock = ($this->config['StockSync'] != 'no');
		$bSyncPrice = ($this->config['PriceSync'] != 'no');
		$data['Process'] = false;

		$data = array();
		// Copied from eBay. Is there any reason you don't use $this->cItem['SKU'] as SKU?
		$data['SKU'] = magnaPID2SKU($product['ProductId']);

		if ($bSyncStock) {
			// Check Quantity variants or master. QuantityTotal is only set if product has variants
			if ((isset($this->cItem['Variations']) && isset($product['Variations'])) && isset($product['QuantityTotal'])) {
				$data['NewQuantity'] = $product['QuantityTotal'];
			} else {
				$data['NewQuantity'] = $product['Quantity'];
			}

            if ($this->config['StatusMode'] == 'true' && $product['Status'] == 0) {
                $data['NewQuantity'] = 0;
            }

			$data['Process'] = ($data['Process'] || (isset($this->cItem['Quantity']) && ($this->cItem['Quantity'] != $data['NewQuantity'])));
		}

		if ($bSyncPrice) {
			// Check Price master
			if (isset($this->cItem['Variations']) === false) {
				$data['Price'] = $product['Price']['Price'];

				// If PriceReduced is set use this one
				if (isset($product['PriceReduced']['Price'])) {
					$data['Price'] = $product['PriceReduced']['Price'];
				}

				// Master price is empty for variation items, therefore check if isset
				$data['Process'] = ($data['Process'] || (isset($data['Price']) && (float)$this->cItem['Price'] != (float)$data['Price']));
			}
		}

		if (isset($this->cItem['Variations']) && isset($product['Variations'])) {
			$data['Variations'] = array();
			foreach ($product['Variations'] as $variantData) {
				$variant['Process'] = false;
				$variant = array();
				$variationSpecifics = array();
				foreach ($variantData['Variation'] as $specific) {
					$variationSpecifics[] = array(
						'Name' => $specific['Name'],
						'Value' => $specific['Value'],
					);
				}
				#$variant['SKU'] = (getDBConfigValue('general.keytype', '0') == 'artNr') ? $variantData['MarketplaceSku'] : $variantData['MarketplaceId'];
				$variant['SKU'] = $variantData['MarketplaceSku'];
				$cVariation = array();
				foreach ($this->cItem['Variations'] as $cVariation){
					if ($cVariation['SKU'] == $variant['SKU']) {
						break;
					}
				}

				if ($bSyncStock) {
					$variant['Quantity'] = $variantData['Quantity'];

                    if ($this->config['StatusMode'] == 'true' && $product['Status'] == 0) {
                        $variant['Quantity'] = 0;
                    }

					$variant['Process'] = ($variant['Process'] || ($cVariation['Quantity'] != $variant['Quantity']));
				}

				if ($bSyncPrice) {
					$variant['Price'] = $variantData['Price']['Price'];

					// If PriceReduced is set use this one
					if (isset($variantData['PriceReduced']['Price'])) {
						$variant['Price'] = $variantData['PriceReduced']['Price'];
					}

					$variant['Process'] = ($variant['Process'] || ((float)$cVariation['Price'] != (float)$variant['Price']));
				}

				$variant['Variation'] = $variationSpecifics;
				$data['Variations'][] = $variant;
			}

		}

		$this->log(
			"\n\tCheck24 Quantity: " . $this->cItem['Quantity'] .
			"\n\tShop Main Quantity: " . $data['NewQuantity']
		);

		if (isset($this->cItem['Price']) === true) {
			$this->log(
				"\n\tCheck24 Price: " . $this->cItem['Price'] .
				"\n\tShop Price: " . $product['Price']['Price']
			);
		}

		// Log Variations
		if (isset($this->cItem['Variations']) && isset($product['Variations'])) {
			$this->log(
				"\n\tVariations:"
			);
			foreach ($this->cItem['Variations'] as $check24Variation) {
				foreach ($product['Variations'] as $aShopVariantData) {
					if ($check24Variation['SKU'] == $aShopVariantData[((getDBConfigValue('general.keytype', '0') == 'artNr') ? 'MarketplaceSku' : 'MarketplaceId')]) {
						$this->log(
							"\n\t\tVariation SKU: " . $check24Variation['SKU'] .
							"\n\t\tCheck24 Quantity: " . $check24Variation['Quantity'] .
							"\n\t\tShop Main Quantity: " . $aShopVariantData['Quantity'] .
							"\n\t\tCheck24 Price: " . $check24Variation['Price'] .
							"\n\t\tShop Price: " . (isset($aShopVariantData['PriceReduced']['Price']) ? $aShopVariantData['PriceReduced']['Price'] : $aShopVariantData['Price']['Price']) .
							"\n"
						);
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
					$variation['MasterSKU'] = $data['SKU'];
					$variationsToUpdate[] = $variation;
				}
			}

			if (count($variationsToUpdate) > 0) {
				$this->updateItems($variationsToUpdate);
			}
		} else if ($data['Process'] === true) {
			// Simple product update
			unset($data['Process']);
			$this->updateItems(array($data));
		}
	}
}
