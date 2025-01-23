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
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleCheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES.'metro/MetroHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'metro/classes/MetroProductSaver.php');

class MetroCheckinSubmit extends MagnaCompatibleCheckinSubmit {

    private $iHighestPID = 0; // needed for afterPopulateSelectionWithData()

    public function __construct($settings = array()) {
        $settings = array_merge(array(
            'itemsPerBatch' => 1,
            'keytype' => getDBConfigValue('general.keytype', '0'),
            'mlProductsUseLegacy' => false
        ), $settings);
        parent::__construct($settings);

        $this->summaryAddText = "<br />\n".ML_EBAY_SUBMIT_ADD_TEXT_ZERO_STOCK_ITEMS_REMOVED;
        $this->getHighestPID();
    }

    private function getHighestPID() {
        $this->iHighestPID = MagnaDB::gi()->fetchOne("SELECT MAX(products_id) FROM ".TABLE_PRODUCTS);
    }

    protected function generateRequestHeader() {
        return array(
            'ACTION' => 'AddItems',
            'SUBSYSTEM' => 'Metro',
            'MODE' => 'ADD'
        );
    }

    protected function setUpMLProduct() {
        parent::setUpMLProduct();
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

    /*
     * Take Variations from $product (as provided by the MLProduct class)
     * and add to $data[submit] in a proper way
     */

    protected function appendAdditionalData($pID, $product, &$data) {
        if ($data['quantity'] < 0) {
            $data['quantity'] = 0;
        }
        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sPropertiesWhere = "products_model = '".MagnaDB::gi()->escape(MagnaDB::gi()->fetchOne("SELECT products_model FROM ".TABLE_PRODUCTS." WHERE products_id = '".$pID."'"))."'";
        } else {
            $sPropertiesWhere = "products_id = '".$pID."'";
        }
        $properties = MagnaDB::gi()->fetchRow("
            SELECT *
              FROM ".TABLE_MAGNA_METRO_PREPARE."
             WHERE     ".$sPropertiesWhere."
                   AND mpID = '".$this->mpID."'
        ");
        $data['submit'] = array(
            'SKU' => '', // handled below
            'MasterSKU' => '', // handled below
            'Quantity' => $product['Quantity'],
            'GTIN' => $properties['GTIN'],
            'ShortDescription' => replaceNbsp($properties['ShortDescription']),
            'CategoryID' => $properties['PrimaryCategory'],
            'ProductPrice' => $product['Price']['Fixed'],
            'Manufacturer' => $properties['Manufacturer'],
            'ManufacturerPartNumber' => $properties['ManufacturerPartNumber'],
            'Brand' => $properties['Brand'],
            'Currency' => 'EUR',
            'ShippingProfile' => $properties['ShippingProfile'],
            'ShippingGroup' => MetroHelper::gi()->getShippingGroupName($properties['ShippingGroup']),
            'Verified' => 'OK',
            'ProductId' => $pID,
            'PreparedTS' => $properties['PreparedTS'],
            'MarketplaceAttributes' => $properties['ShopVariation'],
            'ProcessingTime' => $properties['ProcessingTime'],
            'MaxProcessingTime' => $properties['MaxProcessingTime'],
            'ManufacturersSuggestedRetailPrice' => $this->stringToFloat($properties['MSRP']),
            'BusinessModel' => $properties['BusinessModel'],
            'FreightForwarding' => ($properties['FreightForwarding'] === 'true'),
            'Title' => $properties['Title'],
            'Description' => MetroHelper::MetroStripTagsFromDescription($properties['Description'])
        );
        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $data['submit']['SKU'] = $properties['products_model'];
            $data['submit']['MasterSKU'] = $properties['products_model'];
        } else {
            $data['submit']['SKU'] = 'ML'.$properties['products_id'];
            $data['submit']['MasterSKU'] = 'ML'.$properties['products_id'];
        }

        $shippingPriceConfigValue = getDBConfigValue('metro.shippingprofile.cost', $this->mpID);
        MetroHelper::fixShippingprofileCost($shippingPriceConfigValue);
        $shippingProfilePrice = $shippingPriceConfigValue[$properties['ShippingProfile']];
        // Shipping Cost is stored as gross price
        $data['submit']['ShippingCost'] = (float)$shippingProfilePrice;

        // Product Gross Price
        $productGrossPrice = round(($product['Price']['Fixed'] * ((100 + (float)$product['TaxPercent']) / 100)), 2);
        $data['submit']['Price'] = (float)$productGrossPrice + (float)$shippingProfilePrice;

        // NetPrice + NetShippingCost
        $netShippingCost = round(((float)$shippingProfilePrice / ((100 + (float)$product['TaxPercent']) / 100)), 2);
        $data['submit']['NetShippingCost'] = $netShippingCost;
        $data['submit']['NetPrice'] = $product['Price']['Fixed'] + $netShippingCost;

        $data['submit']['Vat'] = $product['TaxPercent'];
        $images = json_decode($properties['Images'], true);
        if (!empty($images)) {
            $imagePath = getDBConfigValue('metro.imagepath', $this->mpID, '');
            if (empty($imagePath)) {
                $imagePath = SHOP_URL_POPUP_IMAGES;
            }
            foreach ($images as $imgNo => $imgName) {
                // add path if it doesn't start with http
                if (strpos($imgName, 'http') !== 0) {
                    $images[$imgNo] = $imagePath.$imgName;
                }
            }
            $data['submit']['Images'] = $images;
        } else {
            $data['submit']['Images'] = array();
        }
        $features = unserialize($properties['Feature']);
        $data['submit']['Features'] = !empty($features) ? $features : array();

        if (!array_key_exists('Variations', $product)
            || empty($product['Variations'])
        ) {
            $data['submit']['MarketplaceAttributes'] = MetroHelper::gi()->convertMatchingToNameValue(
                json_decode($properties['ShopVariation'], true),
                $product
            );
        } else {
            $this->getVariations($pID, $product, $data);
        }
    }

    private function stringToFloat($str) {
        $str = preg_replace('/[^0-9,.\/]/','',$str);
        $str = str_replace(",",".",$str);
        $str = preg_replace('/\.(?=.*\.)/', '', $str);

        return (float) $str;
    }

    protected function getVariations($pID, $product, &$data) {
        if (!array_key_exists('Variations', $product)
            || empty($product['Variations'])
        ) {
            return;
        }
        $masterData = $data['submit'];
        $data['submit'] = array();

        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sSkuKey = 'MarketplaceSku';
        } else {
            $sSkuKey = 'MarketplaceId';
        }

        $CategoryAttributesBySKU = $this->translateCategoryAttributesForVariations($masterData['MarketplaceAttributes'], $product['Variations'], $sSkuKey);
        $varNameAdditionyBySKU = $this->varNameAdditions($product['Variations'], $sSkuKey);
        $varImagesByVarId = $this->getVariationImages($product);
        $i = 0;
        foreach ($product['Variations'] as $aVariation) {
            // Product Gross Price
            $productGrossPrice = round(($aVariation['Price']['Fixed'] * ((100 + (float)$masterData['Vat']) / 100)), 2);

            // MPN must not be the same for variations
            if (!empty($masterData['ManufacturerPartNumber'])) {
                $aVariation['ManufacturerPartNumber'] = $masterData['ManufacturerPartNumber'] . str_replace($masterData['MasterSKU'], '', $aVariation[$sSkuKey]);
            } else {
                $aVariation['ManufacturerPartNumber'] = '';
            }

            $data['submit'][$i] = array(
                'SKU' => $aVariation[$sSkuKey],
                'MasterSKU' => $masterData['MasterSKU'],
                'Quantity' => $aVariation['Quantity'],
                'GTIN' => (!empty($aVariation['EAN']) ? $aVariation['EAN'] : $masterData['GTIN']),
                'ShortDescription' => $masterData['ShortDescription'],
                'CategoryID' => $masterData['CategoryID'],
                'ProductPrice' => $aVariation['Price']['Fixed'],
                'Manufacturer' => $masterData['Manufacturer'],
                'ManufacturerPartNumber' => $aVariation['ManufacturerPartNumber'],
                'Brand' => $masterData['Brand'],
                'Currency' => $masterData['Currency'],
                'ShippingProfile' => $masterData['ShippingProfile'],
                'ShippingGroup' => $masterData['ShippingGroup'],
                'Verified' => $masterData['Verified'],
                'ProductId' => $masterData['ProductId'],
                'PreparedTS' => $masterData['PreparedTS'],
                'MarketplaceAttributes' => $CategoryAttributesBySKU[$aVariation[$sSkuKey]],
                'ManufacturersSuggestedRetailPrice' => (float)$masterData['ManufacturersSuggestedRetailPrice'],
                'ProcessingTime' => $masterData['ProcessingTime'],
                'MaxProcessingTime' => $masterData['MaxProcessingTime'],
                'BusinessModel' => $masterData['BusinessModel'],
                'FreightForwarding' => $masterData['FreightForwarding'],
                'Title' => $masterData['Title'].(isset($varNameAdditionyBySKU[$aVariation[$sSkuKey]]) ? '('.$varNameAdditionyBySKU[$aVariation[$sSkuKey]].')' : ''),
                'Description' => $masterData['Description'],
                'Images' => $masterData['Images'], // handled below, if any more
                'Features' => $masterData['Features'],
                'Vat' => $masterData['Vat'],
                'ShippingCost' => $masterData['ShippingCost'],
                'NetShippingCost' => $masterData['NetShippingCost'],
                'Price' => $productGrossPrice + $masterData['ShippingCost'],
                'NetPrice' => $aVariation['Price']['Fixed'] + $masterData['NetShippingCost'],
            );
            if (array_key_exists($aVariation['VariationId'], $varImagesByVarId)) {
                if (is_array($varImagesByVarId[$aVariation['VariationId']])) {
                    foreach ($varImagesByVarId[$aVariation['VariationId']] as $variationImage) {
                        array_unshift($data['submit'][$i]['Images'], $variationImage);
                    }
                } else if (is_string($varImagesByVarId[$aVariation['VariationId']]) && !empty($varImagesByVarId[$aVariation['VariationId']])) {
                    array_unshift($data['submit'][$i]['Images'], $varImagesByVarId[$aVariation['VariationId']]);
                }
            }
            $i++;
        }
    }

    /*
     * Map matched variation attributes to be exported in 'MarketplaceAttributes'
     * upload request payload. Only existing and matched product attribute values should be exported.
     */

    private function translateCategoryAttributesForVariations($jCategoryAttributes, $aVariations, $sSkuKey) {

        $aCategoryAttributes = json_decode($jCategoryAttributes, true);
        $aShopNamesForCategoryAttributes = array_map(function ($attr) {
            return $attr['AttributeName'];
        }, $aCategoryAttributes);
        $aShopCodesForCategoryAttributes = array_map(function ($attr) {
            return $attr['Code'];
        }, $aCategoryAttributes);

        $res = $freetext = array();
        foreach ($aCategoryAttributes as $key => $matched) {
            if ($matched['Code'] === 'freetext' || $matched['Code'] === 'attribute_value') {
                $freetext[$key] = $matched['Values'];
                unset($aCategoryAttributes[$key]);
            }
        }
        foreach ($aVariations as $i => $aVariation) {
            $variantAttributes = array();

            foreach ($aVariation['Variation'] as $key => $variant) {
                if (in_array($variant['Name'], $aShopNamesForCategoryAttributes) || in_array($variant["NameId"], $aShopCodesForCategoryAttributes)) {
                    $variantAttributes[$variant['Name']] = $variant['Value'];
                }
            }

            foreach ($variantAttributes as $key => $vattr) {

                foreach ($aCategoryAttributes as $attr => $matchedAttributes) {
                    if (is_array($matchedAttributes['Values'])) {
                        foreach ($matchedAttributes['Values'] as $matched) {
                            if ($matched['Shop']['Value'] === $vattr) {
                                $res[$aVariation[$sSkuKey]][$attr] = $matched['Marketplace']['Value'];
                            }
                        }
                    }
                }
            }
            $res[$aVariation[$sSkuKey]] = array_merge(
                empty($res[$aVariation[$sSkuKey]]) ? array() : $res[$aVariation[$sSkuKey]],
                $freetext);
        }

        return $res;
    }

    /*
     * get variation properties like 'Size: M'
     * to add to variation titles
     */

    private function varNameAdditions($aVariations, $sSkuKey) {
        $aRes = array();
        foreach ($aVariations as $aVariation) {
            $sCurrKey = $aVariation[$sSkuKey];
            $aRes[$sCurrKey] = '';
            $sAddition = '';
            foreach ($aVariation['Variation'] as $aNameValue) {
                $sAddition .= $aNameValue['Name'].': '.$aNameValue['Value'].', ';
            }
            $aRes[$sCurrKey] = trim($sAddition, ', ');
        }
        return $aRes;
    }

    /**
     * Return the variation images in as an array
     *
     * @param $product
     * @return array
     */
    private function getVariationImages($product) {
        // in case of attributes or no variation images exists just return
        if (   getDBConfigValue('general.options', '0', 'old') != 'gambioProperties'
            || !array_key_exists('VariationPictures', $product)
            || empty($product['VariationPictures'])
        ){
            return array();
        }

        // We only use path before Gambio 4.1 (since in Gambio 4.1 the image path is included in the Database)
        if (version_compare(ML_GAMBIO_VERSION, '4.1', '>=')) {
            $imagePathVariations = HTTP_CATALOG_SERVER.DIR_WS_CATALOG;
        } else {
            $imagePathVariations = getDBConfigValue($this->_magnasession['currentPlatform'].'.imagepath.variations', $this->_magnasession['mpID'], HTTP_CATALOG_SERVER.DIR_WS_CATALOG.DIR_WS_IMAGES.'product_images/properties_combis_images/');
        }

        $return = array();

        // VariationPictures don't have keys but only IDs
        foreach ($product['VariationPictures'] as $aPictureData) {
            // Support for one Variation Image (if shop not support multiple variation images)
            if (empty($aPictureData['Images']) && !empty($aPictureData['Image'])) {
                $return[$aPictureData['VariationId']] = $imagePathVariations.$aPictureData['Image'];
            }

            // Support for Multiple Variation Images - see Fallback above if shop supports only one variation image
            if (!empty($aPictureData['Images'])) {
                foreach ($aPictureData['Images'] as $varImage) {
                    if (!empty($varImage)
                        && !in_array($imagePathVariations.$varImage, $return[$aPictureData['VariationId']])
                    ) {
                        $return[$aPictureData['VariationId']][] = (preg_match('/http(s{0,1}):\/\//', $varImage) ? '' : $imagePathVariations ).$varImage;
                    }
                }
            }
        }
        unset($aPictureData);
        return $return;
    }

    /* change the data format so that every Variation is an Item */
    protected function afterPopulateSelectionWithData() {
        $aNewSelection = array();
        $blChanged = false;
        foreach ($this->selection as $i => $item) {
            if (array_key_exists('SKU', $item['submit'])) {
                $aNewSelection[$i] = $item;
                continue;
            }
            $blChanged = true;

            // One of the items must have the original key $i,
            // so that after submitting, it's removed from the selection table
            // and the processing can go on with the next item.
            // The others get keys above the products_id range,
            // so that they're not confused with anything else
            $blKeyUsed = false;
            foreach ($item['submit'] as $j => $aVarItem) {
                if (!$blKeyUsed) {
                    $sCurrKey = $i;
                    $blKeyUsed = true;
                } else {
                    $sCurrKey = $this->iHighestPID + 1 + $j;
                    $this->variationCount += 1;
                }
                $aNewSelection[$sCurrKey] = array(
                    'quantity' => $aVarItem['Quantity'],
                    'price' => $aVarItem['Price'],
                    'submit' => $aVarItem
                );
            }
        }
        if ($blChanged) {
            $this->selection = $aNewSelection;
        }
    }

    /*
     * set the number of items correctly
     * (count MasterSKU's, so that we don't get "10 of 3 Items submitted")
     */
    protected function afterSendRequest() {
        if ($this->submitSession['state']['success'] > $this->submitSession['state']['total']) {
            $aMasterSKUs = array();
            foreach ($this->selection as $item) {
                $aMasterSKUs[] = $item['submit']['MasterSKU'];
            }
            $iCountItems = count($aMasterSKUs);
            $aMasterSKUs = array_unique($aMasterSKUs);
            $iCountMasterSKUs = count($aMasterSKUs);
            $this->submitSession['state']['success'] = $this->submitSession['state']['success'] + $iCountMasterSKUs - $iCountItems;
        }
    }

    /*
     * 'listings', not 'inventory'
     */
    protected function generateRedirectURL($state) {
        return toURL(array(
            'mp' => $this->realUrl['mp'],
            'mode' => ($state == 'fail') ? 'errorlog' : 'listings'
        ), true);
    }
}
