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
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleCheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES.'etsy/EtsyHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'etsy/classes/EtsyProductSaver.php');

class EtsyCheckinSubmit extends MagnaCompatibleCheckinSubmit {

    public function __construct($settings = array()) {
        $settings = array_merge(array(
            'itemsPerBatch' => 500,
            'keytype' => getDBConfigValue('general.keytype', '0'),
            'mlProductsUseLegacy' => false
        ), $settings);
        parent::__construct($settings);

        $this->summaryAddText = "<br />\n".ML_EBAY_SUBMIT_ADD_TEXT_ZERO_STOCK_ITEMS_REMOVED;
    }

    protected function generateRequestHeader() {
        return array(
            'ACTION' => 'AddItems',
            'SUBSYSTEM' => 'Etsy',
            'MODE' => 'ADD'
        );
    }

    protected function setUpMLProduct() {
        parent::setUpMLProduct();
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

    /*
     * Take Variations from $product (as provided by the MLProduct class)
     * and add to $data[submit] in a proper way
     */
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

        $this->filterOutZeroStockVariations($product['Variations'], $pID);
        $CategoryAttributesBySKU = $this->translateCategoryAttributesForVariations($masterData['CategoryAttributes'], $product['Variations'], $sSkuKey);
        $varNameAdditionyBySKU = $this->varNameAdditions($product['Variations'], $sSkuKey);
        $varImagesByVarId = $this->varImages($product);
        $i = 0;
        foreach ($product['Variations'] as $aVariation) {
            $data['submit'][$i] = array(
                'SKU' => $aVariation[$sSkuKey],
                'MasterSKU' => $masterData['MasterSKU'],
                'Images' => $masterData['Images'], // handled below, if any more
                'Quantity' => $aVariation['Quantity'],
                'Price' => $aVariation['Price']['Fixed'],
                'Whomade' => $masterData['Whomade'],
                'Whenmade' => $masterData['Whenmade'],
                'IsSupply' => $masterData['IsSupply'],
                'Language' => $masterData['Language'],
                'Currency' => $masterData['Currency'],
                'ShippingTemplate' => $masterData['ShippingTemplate'],
                'Primarycategory' => $masterData['Primarycategory'],
                'Verified' => $masterData['Verified'],
                'Description' => $masterData['MasterDescription'],
                'Title' => $masterData['MasterTitle'].(isset($varNameAdditionyBySKU[$aVariation[$sSkuKey]]) ? '('.$varNameAdditionyBySKU[$aVariation[$sSkuKey]].')' : ''),
                'ProductId' => $masterData['ProductId'],
                'PreparedTS' => $masterData['PreparedTS'],
                'CategoryAttributes' => $CategoryAttributesBySKU[$aVariation[$sSkuKey]],
                'MasterTitle' => $masterData['MasterTitle'],
                'MasterDescription' => $masterData['MasterDescription'],
            );
            if (array_key_exists($aVariation['VariationId'], $varImagesByVarId)) {
                array_unshift($data['submit'][$i]['Images'], array(
                        'URL' => $varImagesByVarId[$aVariation['VariationId']]
                    )
                );
            }
            $i++;
        }
    }

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
              FROM ".TABLE_MAGNA_ETSY_PREPARE."
             WHERE     ".$sPropertiesWhere."
                   AND mpID = '".$this->mpID."'
        ");

        $data['submit'] = array(
            'SKU' => '', // handled below
            'MasterSKU' => '', // handled below
            'Images' => '', // handled below
            'Quantity' => $product['Quantity'],
            'Price' => $product['Price']['Fixed'],
            'Whomade' => $properties['Whomade'],
            'Whenmade' => $properties['Whenmade'],
            'IsSupply' => $properties['IsSupply'],
            'Language' => getDBConfigValue('etsy.shop.language', $this->mpID),
            'Currency' => getDBConfigValue('etsy.currency', $this->mpID),
            'ShippingTemplate' => $properties['ShippingTemplate'],
            'Primarycategory' => $properties['Primarycategory'],
            'Verified' => 'OK',
            'ProductId' => $pID,
            'PreparedTS' => $properties['PreparedTS'],
            'CategoryAttributes' => $properties['ShopVariation'],
            'MasterTitle' => $properties['Title'],
            'MasterDescription' => $properties['Description']
        );
        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $data['submit']['SKU'] = $properties['products_model'];
            $data['submit']['MasterSKU'] = $properties['products_model'];
        } else {
            $data['submit']['SKU'] = 'ML'.$properties['products_id'];
            $data['submit']['MasterSKU'] = 'ML'.$properties['products_id'];
        }
        $data['submit']['Images'] = array();
        $images = json_decode($properties['Image'], true);
        if (!empty($images)) {
            foreach ($images as $sImg) {
                $data['submit']['Images'][] = array(
                    'URL' => getDBConfigValue('etsy.imagepath', $this->mpID).$sImg
                );
            }
        }
        if (!array_key_exists('Variations', $product)
            || empty($product['Variations'])) {
            $data['submit']['CategoryAttributes'] = $this->translateCategoryAttributes($properties['ShopVariation']);
        } else {
            $this->getVariations($pID, $product, $data);
        }
    }

    private function translateCategoryAttributesForVariations($jCategoryAttributes, $aVariations, $sSkuKey) {

        $aCategoryAttributes = json_decode($jCategoryAttributes, true);

        // determine used variation names and values
        $aVariationNames = array();
        foreach ($aVariations as $aVariation) {
            foreach ($aVariation['Variation'] as $nvl) {
                if (!in_array($nvl['Name'], $aVariationNames))
                    $aVariationNames[] = $nvl['Name'];
            }
            unset($nvl);
        }
        unset($aVariation);

        // determine variation IDs
        $sVariationNameList = "'".implode("','", $aVariationNames)."'";
        if (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties') {
            // for gambio properties
            $aVariationNamesFromDB = MagnaDB::gi()->fetchArray('
                    SELECT properties_id, properties_name
                      FROM properties_description
                     WHERE properties_name IN ('.$sVariationNameList.')
                       AND language_id = \''.getDBConfigValue('etsy.lang', $this->mpID).'\''
            );
            $aVariationNamesByCode = array();
            foreach ($aVariationNamesFromDB as $aVarNamesRow) {
                $aVariationNamesByCode[$aVarNamesRow['properties_id']] = $aVarNamesRow['properties_name'];
            }
        } else {
            // for the "old-style" attributes
            $aVariationNamesFromDB = MagnaDB::gi()->fetchArray('
                    SELECT products_options_id, products_options_name
                      FROM '.TABLE_PRODUCTS_OPTIONS.'
                     WHERE products_options_name IN ('.$sVariationNameList.')
                       AND language_id = \''.getDBConfigValue('etsy.lang', $this->mpID).'\''
            );
            $aVariationNamesByCode = array();
            foreach ($aVariationNamesFromDB as $aVarNamesRow) {
                $aVariationNamesByCode[$aVarNamesRow['products_options_id']] = $aVarNamesRow['products_options_name'];
            }
        }
	// must be utf8 for json_encode to work
	// here, not earlier, would break the comparision with DB entries
	arrayEntitiesToUTF8($aVariations);
	arrayEntitiesToUTF8($aVariationNamesByCode);

        // determine the variation name and value matching shop -> etsy
        $aVarValuesShop2Etsy = array();
        $aVarValuesShop2KeysEtsy = array();
        foreach ($aVariationNamesByCode as $iShopVarCode => $sShopVarName) {
            foreach ($aCategoryAttributes as $key => $aAttr) {
                if (($aAttr['Kind'] == 'Matching')
                    && ($aAttr['Code'] == $iShopVarCode)
                ) {
                    // Etsy optional attribute
                    if (!is_array($aVarValuesShop2KeysEtsy[$sShopVarName])) {
                        $aVarValuesShop2KeysEtsy[$sShopVarName] = array();
                    }
                    foreach ($aAttr['Values'] as $aAVal) {
                        $aVarValuesShop2KeysEtsy[$sShopVarName][$aAVal['Shop']['Value']] = $aAVal['Marketplace']['Key'];
                    }
                    unset($aAVal);
                } else if (($aAttr['Kind'] == 'FreeText')
                    && (in_array($key, array('Custom1', 'Custom2')))
                ) {
                    // Etsy attribute Custom1 and Custom2
                    foreach ($aAttr['Values'] as $aAVal) {
                        $aVarValuesShop2Etsy[$sShopVarName][$aAVal['Shop']['Value']] = $aAVal['Marketplace']['Value'];
                    }
                    unset($aAVal);
                }
            }
            unset($aAttr);
        }
        unset($sShopVarName);

        // merge everything together
        $aRes = array();
        $res = array();
        foreach ($aVariations as $aVariation) {
            $countCustomAttribute = array();

            $sCurrKey = $aVariation[$sSkuKey];
            $aRes[$sCurrKey] = array();
            foreach ($aVariation['Variation'] as $i => $aNameValue) {
                if (array_key_exists($aNameValue['Name'], $aVarValuesShop2KeysEtsy)
                    && array_key_exists($aNameValue['Value'], $aVarValuesShop2KeysEtsy[$aNameValue['Name']])
                ) {
                    $aProperty = explode('-', $aVarValuesShop2KeysEtsy[$aNameValue['Name']][$aNameValue['Value']]);
                    $aRes[$sCurrKey]['property_values'][$i] = array(
                        'property_id' => $aProperty[0],
                        'value_ids' => array($aProperty[1]),
                        'property_name' => '',
                        'values' => array(0 => '')
                    );
                } else if (array_key_exists($aNameValue['Name'], $aVarValuesShop2Etsy)
                    && array_key_exists($aNameValue['Value'], $aVarValuesShop2Etsy[$aNameValue['Name']])
                ) {
                    if (!array_key_exists($sCurrKey, $countCustomAttribute)) {
                        $countCustomAttribute[$sCurrKey] = 1;
                    } else {
                        $countCustomAttribute[$sCurrKey]++;
                    }
                    // max 2 custom attributes - if more then skip
                    if ($countCustomAttribute[$sCurrKey] > 2) {
                        continue;
                    }

                    $aRes[$sCurrKey]['property_values'][$i] = array(
                        'property_id' => ($countCustomAttribute[$sCurrKey] === 1) ? 513 : 514,
                        'value_ids' => array(),
                        'property_name' => $aNameValue['Name'],
                        'values' => array($aVarValuesShop2Etsy[$aNameValue['Name']][$aNameValue['Value']])
                    );
                }
            }
            $res[$sCurrKey] = json_encode($aRes[$sCurrKey]);
        }
        return $res;
    }

    /*
     * for simple Items
     */
    function translateCategoryAttributes($jShopVariation) {
        $aShopVariation = json_decode($jShopVariation, true);
        if (empty($aShopVariation))
            return json_encode(array());
        $pv = array();

        foreach ($aShopVariation as $i => $prop) {
            $pv[$i]['value_ids'] = array();
            $pv[$i]['values'] = array();
            if ('Matching' == $prop['Kind']) {
                $value = $prop['Values'];
                if (is_array($prop['Values']) && array_key_exists('Marketplace', current($prop['Values']))) {
                    $m = current($prop['Values']);
                    $value = $m['Marketplace']['Key'];
                }
                $aValues = (explode('-', $value));
                $pv[$i]['property_id'] = $aValues[0];
                $pv[$i]['value_ids'] = array($aValues[1]);
                $pv[$i]['property_name'] = '';
                $pv[$i]['values'][0] = '';
            } else {
                $pv[$i]['property_id'] = 513;
                $pv[$i]['property_name'] = $prop['AttributeName'];
                $pv[$i]['values'] = array($prop['Values']);
            }
        }
        $pv = array_values($pv);
        return json_encode(array('property_values' => $pv));
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

    private function filterOutZeroStockVariations(&$aVariations, $iProductId) {
        foreach ($aVariations as $i => $aVariation) {
            if ($aVariation['Quantity'] <= 0) {
                unset($aVariations[$i]);
            }
        }

        if (empty($aVariations)) {
            $this->disabledItems[] = $iProductId;
            $this->ajaxReply['ignoreErrors'] = true;
        }
    }

    private function varImages($product) {
        if (getDBConfigValue('general.options', '0', 'old') != 'gambioProperties')
            return array();
        if (!array_key_exists('VariationPictures', $product))
            return array();
        if (empty($product['VariationPictures']))
            return array();
        $VarImagePath = HTTP_CATALOG_SERVER.DIR_WS_CATALOG.DIR_WS_IMAGES.'product_images/properties_combis_images/';
        $res = array();
        // VariationPictures don't have keys but only IDs
        foreach ($product['VariationPictures'] as $aPictureData) {
            if (empty($aPictureData['Image']))
                continue;
            $res[$aPictureData['VariationId']] = $VarImagePath.$aPictureData['Image'];
        }
        unset($aPictureData);
        return $res;
    }

    /* change the data format so that every Variation is an Item */
    protected function afterPopulateSelectionWithData() {
        $aNewSelection = array();
        $blChanged = false;
        foreach ($this->selection as $i => $item) {
            if (array_key_exists('SKU', $item['submit'])) {
                $aNewSelection[] = $item;
                continue;
            }
            $blChanged = true;
            foreach ($item['submit'] as $j => $aVarItem) {
                $aNewSelection[] = array(
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
                $aMasterSKUs[] = $item['MasterSKU'];
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
