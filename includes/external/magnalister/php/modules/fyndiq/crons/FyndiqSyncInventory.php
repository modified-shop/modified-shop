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

class FyndiqSyncInventory extends MagnaCompatibleSyncInventory {

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

        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $identifier = 'Sku';
        } else {
            $identifier = 'Id';
        }

        @set_time_limit(180);
        $this->identifySKU();

        $title = isset($this->cItem['ItemTitle']) ? $this->cItem['ItemTitle'] : 'unknown';

        if ((int)$this->cItem['pID'] <= 0) {
            $this->log("\n" . $title . ' not found');
            return;
        } else {
            $this->log("\n" . $title . ' found (pID: ' . $this->cItem['pID'] . ')');
        }

        // Get lang
        $lang = getDBConfigValue($this->marketplace.'.lang', $this->mpID);

        // Prepare product
        MLProduct::gi()->setLanguage($lang);
        MLProduct::gi()->setPriceConfig(FyndiqHelper::loadPriceSettings($this->mpID));
        MLProduct::gi()->setQuantityConfig(FyndiqHelper::loadQuantitySettings($this->mpID));

        $product = MLProduct::gi()->getProductById($this->cItem['pID']);
        arrayEntitiesToUTF8($product);

        $bSyncStock = ($this->config['StockSync'] != 'no');
        $bSyncPrice = ($this->config['PriceSync'] != 'no');
        $data['Process'] = false;

        $data = array();
        // Copied from eBay. Is there any reason you don't use $this->cItem['SKU'] as SKU?
        $data['SKU'] = magnaPID2SKU($product['ProductId']);

        if ($bSyncStock) {
            if (isset($product['Variations']) && !empty($product['Variations'])) {
                foreach($product['Variations'] as $variation) {
                    if ($variation['Marketplace' . $identifier] === $this->cItem['ArticleSKU']) {
                        $data['SKU'] = $variation['Marketplace' . $identifier];
                        $product['Quantity'] = $variation['Quantity'];
                        break;
                    }
                }
            }

            if (isset($this->cItem['Quantity']) && $product['Quantity'] != $this->cItem['Quantity']) {
                $data['Quantity'] = $product['Quantity'];
                $data['Process'] = true;
            }
        }

        $productTax = SimplePrice::getTaxByPID($this->cItem['pID']);
        $taxFromConfig = getDBConfigValue($this->marketplace . '.checkin.mwst', $this->mpID);
        if ($bSyncPrice) {
            if (isset($product['Variations']) && !empty($product['Variations'])) {
                foreach($product['Variations'] as $variation) {
                    if ($variation['Marketplace' . $identifier] === $this->cItem['ArticleSKU']) {
                        $data['SKU'] = $variation['Marketplace' . $identifier];
                        $product = $variation;
                        break;
                    }
                }
            }

            $price = $product['Price']['Price'];

            // If PriceReduced is set use this one
            if (isset($product['PriceReduced']['Price'])) {
                $price = $product['PriceReduced']['Price'];
            }

            if (isset($taxFromConfig) && $taxFromConfig !== '') {
                $price = $price * 100 / (100 + $productTax);
                $price = round($price * (($taxFromConfig + 100) / 100), 2);
            }

            // If price is lower, update it
            if (isset($price) && (float)$price != (float)$this->cItem['Price']) {
                $data['Price'] = $price;
                $data['Process'] = true;
            }
        }

        if (isset($data['Quantity']) === true) {
            $this->log(
                "\n\tFyndiq Quantity: " . $this->cItem['Quantity'] .
                "\n\tShop Main Quantity: " . $data['Quantity']
            );
        } else {
            $this->log("\n\t".
                'Quantity not changed (' . $this->cItem['Quantity'] . ')'
            );
        }

        if (isset($data['Price']) === true) {
            $this->log(
                "\n\tFyndiq Price: " . $this->cItem['Price'] .
                "\n\tShop Price: " . $data['Price']
            );
        } else {
            $this->log("\n\t".
                'Price not changed (' . $this->cItem['Price'] . ')'
            );
        }

        // Simple product update
        unset($data['Process']);
        $this->updateItems(array($data));
    }

}