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
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleCheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES.'otto/OttoHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'otto/classes/OttoProductSaver.php');

class OttoCheckinSubmit extends MagnaCompatibleCheckinSubmit {

    public function __construct($settings = array()) {
        $settings = array_merge(array(
            'itemsPerBatch' => 1,
            'keytype' => getDBConfigValue('general.keytype', '0'),
            'mlProductsUseLegacy' => false
        ), $settings);
        parent::__construct($settings);

        $this->summaryAddText = "<br />\n".ML_EBAY_SUBMIT_ADD_TEXT_ZERO_STOCK_ITEMS_REMOVED;
    }

    protected function generateRequestHeader() {
        return array(
            'ACTION' => 'AddItems',
            'SUBSYSTEM' => 'Otto',
            'MODE' => 'ADD'
        );
    }

    protected function setUpMLProduct() {
        parent::setUpMLProduct();
        MLProduct::gi()->setPriceConfig(OttoHelper::loadPriceSettings($this->mpID));
        MLProduct::gi()->setQuantityConfig(OttoHelper::loadQuantitySettings($this->mpID));
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
        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sPropertiesWhere = "products_model = '".MagnaDB::gi()->escape(MagnaDB::gi()->fetchOne("SELECT products_model FROM ".TABLE_PRODUCTS." WHERE products_id = '".$pID."'"))."'";
        } else {
            $sPropertiesWhere = "products_id = '".$pID."'";
        }
        $properties = MagnaDB::gi()->fetchRow("
            SELECT *
              FROM ".TABLE_MAGNA_OTTO_PREPARE."
             WHERE     ".$sPropertiesWhere."
                   AND mpID = '".$this->mpID."'
        ");

        $data['submit'] = array(
            'SKU' => '', // handled below
            'ProductName' => '', // handled below
            'StandardPrice' => $product['Price']['Fixed'],
            'Description' => $properties['Description'],
            'Currency' => $product['Currency']['Fixed'],
            'PrimaryCategoryName' => $properties['PrimaryCategoryName'],
            'DeliveryType' => $properties['DeliveryType'],
            'DeliveryTime' => $properties['DeliveryTime'],
            'MarketplaceAttributes' => $properties['ShopVariation'],
            'CategoryIndependentAttributes' => $properties['CategoryIndependentShopVariation'],
        );
        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $data['submit']['SKU'] = $properties['products_model'];
            $data['submit']['ProductName'] = $properties['products_model'];
        } else {
            $data['submit']['SKU'] = 'ML'.$properties['products_id'];
            $data['submit']['ProductName'] = 'ML'.$properties['products_id'];
        }

        $data['submit']['Price'] = $product['Price']['Fixed'];
        $configVAT = getDBConfigValue('otto.product.vat', $this->mpID);
        $data['submit']['VAT'] = isset($product['TaxClass'], $configVAT[$product['TaxClass']]) ? $configVAT[$product['TaxClass']] : 'no_tax_set';
        $images = json_decode($properties['Images'], true);
        if (!empty($images)) {
            $imagePath = getDBConfigValue('otto.imagepath', $this->mpID, '');
            if (empty($imagePath)) {
                $imagePath = SHOP_URL_POPUP_IMAGES;
            }
            $mainImage = $properties['MainImage'];
            $foundMainImageKey = 0;
            foreach ($images as $imgNo => $imgName) {
                // add path if it doesn't start with http
                if (strpos($imgName, 'http') !== 0) {
                    $images[$imgNo] = $imagePath.$imgName;
                }
                if ((function_exists('mb_strpos') && mb_strpos($imgName, $mainImage) !== false) || strpos($imgName, $mainImage) !== false) {
                    $foundMainImageKey = $imgNo;
                }
            }
            if ($foundMainImageKey > 0) {
                $imageToBeMoved = $images[$foundMainImageKey];
                unset($images[$foundMainImageKey]);
                $images = array_values($images);
                array_unshift($images, $imageToBeMoved);
            }

            $data['submit']['Images'] = $images;
        } else {
            $data['submit']['Images'] = array();
        }

        if (!array_key_exists('Variations', $product)
            || empty($product['Variations'])) {
            $data['submit']['MarketplaceAttributes'] = OttoHelper::gi()->convertMatchingToNameValue(
                json_decode($properties['ShopVariation'], true),
                $product
            );
            $data['submit']['CategoryIndependentAttributes'] = OttoHelper::gi()->convertMatchingToNameValue(
                json_decode($properties['CategoryIndependentShopVariation'], true),
                $product
            );
        } else {
            $this->getVariations($pID, $product, $data);
        }
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

        $CategoryAttributesBySKU = $this->translateCategoryAttributesForVariations(
            $masterData['MarketplaceAttributes'],
            $product['Variations'],
            $sSkuKey
        );
        $CategoryIndependentAttributesBySKU = $this->translateCategoryAttributesForVariations(
            $masterData['CategoryIndependentAttributes'],
            $product['Variations'],
            $sSkuKey
        );
        $varImagesByVarId = $this->varImages($product);
        $i = 0;

        foreach ($product['Variations'] as $aVariation) {
            $data['submit'][$i] = array(
                'SKU' => $aVariation[$sSkuKey],
                'ProductName' => $masterData['ProductName'],
                'StandardPrice' => $aVariation['Price']['Fixed'],
                'Description' => $masterData['Description'],
                'Currency' => $masterData['Currency'],
                'PrimaryCategoryName' => $masterData['PrimaryCategoryName'],
                'DeliveryType' => $masterData['DeliveryType'],
                'DeliveryTime' => $masterData['DeliveryTime'],
                'Images' => $masterData['Images'], // handled below, if any more
                'VAT' => $masterData['VAT'],
                'MarketplaceAttributes' => array_merge(OttoHelper::gi()->convertMatchingToNameValue(
                    json_decode($masterData['MarketplaceAttributes'], true),
                    $product
                ), $CategoryAttributesBySKU[$aVariation[$sSkuKey]]),
                'CategoryIndependentAttributes' => array_merge(OttoHelper::gi()->convertMatchingToNameValue(
                    json_decode($masterData['CategoryIndependentAttributes'], true),
                    $product
                ), $CategoryIndependentAttributesBySKU[$aVariation[$sSkuKey]]),
            );
            if (array_key_exists($aVariation['VariationId'], $varImagesByVarId)) {
                $data['submit'][$i]['Images'] = $varImagesByVarId[$aVariation['VariationId']];
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
        $aShopCodesForCategoryAttributes = array_map(function ($attr) {
            return $attr['Code'];
        }, $aCategoryAttributes);

        $res = $freetext = array();
        foreach ($aCategoryAttributes as $key => $matched) {
            if ($matched['Code'] === 'freetext') {
                $freetext[$matched['AttributeName']] = $matched['Values'];
                unset($aCategoryAttributes[$key]);
            }
        }
        foreach ($aVariations as $aVariation) {
            $ean = array();

            //get the ean from the variations
            if (in_array('ean', $aShopCodesForCategoryAttributes) && $aVariation['EAN'] != '') {
                foreach ($aCategoryAttributes as $value) {
                    if ($value['Code'] == 'ean') {
                        $ean[$value['AttributeName']] = $aVariation['EAN'];
                    }
                }
            }

            $res[$aVariation[$sSkuKey]] = array();
            foreach ($aVariation['Variation'] as $variant) {
                foreach ($aCategoryAttributes as $attr => $matchedAttributes) {
                    if (// We need to either match the attribute name or the code
                        (  $variant['Name'] !== $matchedAttributes['AttributeName']
                           && $variant['NameId'] !== $matchedAttributes['Code']
                        )
                        // The matched attribute values needs to be an array
                        || !is_array($matchedAttributes['Values'])
                    ) {
                        continue;
                    }

                    foreach ($matchedAttributes['Values'] as $matched) {
                        if (// only if the value from the shop is matched by the attribute value
                               !is_array($matched)
                            || !array_key_exists('Shop', $matched)
                            || !is_array($matched['Shop'])
                            || !array_key_exists('Value', $matched['Shop'])
                            || $matched['Shop']['Value'] !== $variant['Value']
                        ) {
                            continue;
                        }

                        $res[$aVariation[$sSkuKey]][$attr] = $matched['Marketplace']['Value'];
                    }
                }
            }
            $res[$aVariation[$sSkuKey]] = array_merge($res[$aVariation[$sSkuKey]], $freetext, $ean);
        }

        return $res;
    }

    private function varImages($product) {
        if (getDBConfigValue('general.options', '0', 'old') != 'gambioProperties')
            return array();
        if (!array_key_exists('VariationPictures', $product))
            return array();
        if (empty($product['VariationPictures']))
            return array();
        $VarImagePath = HTTP_CATALOG_SERVER . '/';
        $res = array();
        // VariationPictures don't have keys but only IDs
        foreach ($product['VariationPictures'] as $aPictureData) {
            if (!empty($aPictureData['Images'])) {
                foreach ($aPictureData['Images'] as $image) {
                    $res[$aPictureData['VariationId']][] = $VarImagePath.$image;
                }
            }
        }
        unset($aPictureData);
        return $res;
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

    protected function preSubmit(&$request) {
        parent::preSubmit($request);
        foreach ($request['DATA'] as $product) {
            if (!isset($product['SKU'])) {
                $request["DATA"] = $request['DATA'][0];
            }
            break;
        }
    }
}
