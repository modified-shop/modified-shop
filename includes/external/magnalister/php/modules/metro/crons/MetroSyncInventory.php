<?php
/*
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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncInventory.php');

class MetroSyncInventory extends MagnaCompatibleSyncInventory {

    /** @var bool|float NetPrice of current product */
    private $cProductNetPrice = false;

    protected function initMLProduct() {
        global $_MagnaSession;
        parent::initMLProduct();
        MLProduct::gi()->setPriceConfig(MetroHelper::loadPriceSettings($this->mpID));
        MLProduct::gi()->setQuantityConfig(MetroHelper::loadQuantitySettings($this->mpID));
        MLProduct::gi()->useMultiDimensionalVariations(true);
        MLProduct::gi()->setOptions(array(
            'includeVariations' => true,
            'sameVariationsToAttributes' => false,
            'purgeVariations' => true,
            'useGambioProperties' => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties')
        ));
    }

    protected function initConfig() {
        parent::initConfig();
        $stockSync = getDBConfigValue('metro.stocksync.tomarketplace', $this->mpID, 'auto');
        if ($stockSync == 'auto_zero_stock') {
            $this->config['StockSync'] = 'auto';
        }
        // like for eBay, not 'quantity.maxquantity'
        $iQuantityMax = (int)getDBConfigValue('metro.maxquantity', $this->mpID, 0);
        if ($iQuantityMax) {
            $this->config['QuantityMax'] = $iQuantityMax;
        }
    }

    protected function updateCustomFields(&$data) {
        if (array_key_exists('NewQuantity', $data)) {
            $data['Quantity'] = $data['NewQuantity']['Value'];
            unset($data['NewQuantity']);
        }

        if ($this->cProductNetPrice !== false) {
            $data['NetPrice'] = $this->cProductNetPrice;
        }
    }

    /* catch wrongly uploaded items */
    protected function identifySKU() {
        parent::identifySKU();
        if (empty($this->cItem['pID'])
            && (getDBConfigValue('general.keytype', '0') != 'artNr')
            && is_numeric($this->cItem['SKU'])) {
            $this->cItem['pID'] = $this->cItem['SKU'];
        }
    }

    protected function updatePrice() {
        if (!$this->syncPrice) return false;

        // preset as default "false"
        $data = false;
        $this->cProductNetPrice = false;

        // cItem pID will be found out during process and will be not submitted by API so we can use it here
        $masterSKU = magnaPID2SKU($this->cItem['pID']);

        $preparedProductData = MagnaDB::gi()->fetchRow("
            SELECT *
              FROM ".TABLE_MAGNA_METRO_PREPARE."
             WHERE ".((getDBConfigValue('general.keytype', '0') === 'artNr')
                ? "products_model = '".MagnaDB::gi()->escape($masterSKU)."'"
                : "products_id = '".$this->cItem['pID']."'"
            )
        );
        $priceConfigValue = getDBConfigValue('metro.shippingprofile.cost', $this->mpID);
        MetroHelper::fixShippingprofileCost($priceConfigValue);

        // If NetPrice dont use GrossPrice
        $marketplacePrice = (float)$this->cItem['Price'];

        // Net Price Calculation
        if (isset($this->cItem['NetPrice'])) {
            $marketplaceNetPrice = (float)$this->cItem['NetPrice'];

            //need to load price config to set IncludeTax to false
            $loadPriceConfig = MetroHelper::loadPriceSettings($this->mpID);

            $productNetPrice = $this->simplePrice
                ->setPriceFromDB($this->cItem['pID'], $this->mpID, $loadPriceConfig['Fixed'])
                ->addAttributeSurcharge($this->cItem['aID'])
                ->finalizePrice($this->cItem['pID'], $this->mpID, $loadPriceConfig['Fixed'])
                ->getPrice();
            $productNetPrice += round(($priceConfigValue[$preparedProductData['ShippingProfile']] / ((100 + (float)SimplePrice::getTaxByPID($this->cItem['pID'])) / 100)), 2);

            if (($productNetPrice > 0) && ($marketplaceNetPrice != $productNetPrice)) {
                $this->cProductNetPrice = $productNetPrice;
                $this->log("\n\t".
                    'Price (net) changed (old: '.$marketplaceNetPrice.'; new: '.$this->cProductNetPrice.')'
                );
            } else {
                $this->log("\n\t".
                    'Price (net) not changed ('.$this->cProductNetPrice.')'
                );
            }
            // data will be written in updateCustomFields() function
        }


        // Gross Price calculation
        $price = $this->simplePrice
            ->setPriceFromDB($this->cItem['pID'], $this->mpID)
            ->addAttributeSurcharge($this->cItem['aID'])
            ->finalizePrice($this->cItem['pID'], $this->mpID)
            ->getPrice();
        $price += $priceConfigValue[$preparedProductData['ShippingProfile']];

        if (($price > 0) && ($marketplacePrice != $price)) {
            $this->log("\n\t".
                'Price changed (old: '.$marketplacePrice.'; new: '.$price.')'
            );
            $data = $price;
        } else {
            $this->log("\n\t".
                'Price not changed ('.$price.')'
            );
        }

        return $data;
    }

    /**
     * Returns the marketplace Product title
     *
     * @return mixed|string
     */
    protected function getProductTitle() {
        $productData = unserialize($this->cItem['ProductData']);
        return $productData['Title'];
    }


}
