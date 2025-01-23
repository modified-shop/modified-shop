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
 * (c) 2010 - 2024 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncInventory.php');
require_once(DIR_MAGNALISTER_MODULES.'etsy/EtsyHelper.php');

class EtsySyncInventory extends MagnaCompatibleSyncInventory {
    private $blHandleZeroStock = false;

    public function __construct($mpID, $marketplace, $limit = 100) {
         parent::__construct($mpID, $marketplace, $limit);
         $this->blMultiDimVariations = true;
    }

    protected function initMLProduct() {
        global $_MagnaSession;
        parent::initMLProduct();
        MLProduct::gi()->setPriceConfig(EtsyHelper::loadPriceSettings($this->mpID));
        MLProduct::gi()->setQuantityConfig(EtsyHelper::loadQuantitySettings($this->mpID));
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
        $stockSync = getDBConfigValue('etsy.stocksync.tomarketplace', $this->mpID, 'auto');
        if ($stockSync == 'auto_zero_stock') {
            $this->config['StockSync'] = 'auto';
            $this->blHandleZeroStock = true;
        }
        // like for eBay, not 'quantity.maxquantity'
        $iQuantityMax = (int)getDBConfigValue('etsy.maxquantity', $this->mpID, 0);
        if ($iQuantityMax) {
            $this->config['QuantityMax'] = $iQuantityMax;
        }
    }

    protected function updateCustomFields(&$data) {
        if (array_key_exists('NewQuantity', $data)) {
            $data['Quantity'] = $data['NewQuantity']['Value'];
            unset($data['NewQuantity']);
        }
        if (array_key_exists('Quantity', $data) || array_key_exists('Price', $data)) {
            $data['HandleZeroStock'] = $this->blHandleZeroStock;
        }
    }
    /* catch wrongly uploaded items */
    protected function identifySKU() {
        parent::identifySKU();
        if (    empty($this->cItem['pID'])
             && (getDBConfigValue('general.keytype', '0') != 'artNr')
             && is_numeric($this->cItem['SKU']) ) {
            $this->cItem['pID'] = $this->cItem['SKU'];
        }
    }
}
