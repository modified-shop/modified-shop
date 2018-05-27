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
require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/checkin/MagnaCompatibleCheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES . 'crowdfox/CrowdfoxHelper.php');

/**
 * TODO: Siehe appendAdditionalData()
 */
class CrowdfoxCheckinSubmit extends MagnaCompatibleCheckinSubmit {

    public function __construct($settings = array()) {
        global $_MagnaSession;
        $this->summaryAddText = "<br /><br />\n" . ML_CROWDFOX_UPLOAD_EXPLANATION;

        $settings = array_merge(array(
            'language' => getDBConfigValue($settings['marketplace'] . '.lang', $_MagnaSession['mpID'], ''),
            'currency' => getCurrencyFromMarketplace($_MagnaSession['mpID']),
            'keytype' => getDBConfigValue('general.keytype', '0'),
            'itemsPerBatch' => 100,
            'mlProductsUseLegacy' => false,
        ), $settings);

        parent::__construct($settings);
        $this->summaryAddText = "<br /><br />\n" . ML_CROWDFOX_UPLOAD_EXPLANATION;

        $this->settings['SyncInventory'] = array(
            'Price' => getDBConfigValue($settings['marketplace'] . '.inventorysync.price', $this->mpID, '') == 'auto',
            'Quantity' => getDBConfigValue($settings['marketplace'] . '.stocksync.tomarketplace', $this->mpID, '') == 'auto',
        );
    }

    public function init($mode, $items = -1) {
        parent::init($mode, $items);
    }

    protected function setUpMLProduct() {
        parent::setUpMLProduct();

        // Set Price and Quantity settings
        MLProduct::gi()->setPriceConfig(CrowdfoxHelper::loadPriceSettings($this->mpID));
        MLProduct::gi()->setQuantityConfig(CrowdfoxHelper::loadQuantitySettings($this->mpID));
        MLProduct::gi()->setOptions(array(
            'sameVariationsToAttributes' => false,
            'purgeVariations' => true,
            'useGambioProperties' => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties'),
        ));
    }

    protected function appendAdditionalData($pID, $product, &$data) {
        $defaultTitle = isset($product['Title']) ? $product['Title'] : '';
        $defaultDescription = isset($product['Description']) ? $product['Description'] : '';

        $prepare = MagnaDB::gi()->fetchRow('
			SELECT * FROM ' . TABLE_MAGNA_CROWDFOX_PREPARE . '
			 WHERE ' . ((getDBConfigValue('general.keytype', '0') == 'artNr') ? 'products_model=\'' .
                MagnaDB::gi()->escape($product['ProductsModel']) . '\'' : 'products_id=\'' . $pID . '\'') . ' 
				   AND mpID = ' . $this->_magnasession['mpID'] . '
		');

        if (is_array($prepare)) {
            $data['submit']['SKU'] = magnaPID2SKU($pID);
            $data['submit']['GTIN'] = isset($prepare['GTIN']) ? $prepare['GTIN'] : '';
            $data['submit']['ItemTitle'] = isset($prepare['Title']) ? $prepare['Title'] : CrowdfoxHelper::sanitizeTitle($defaultTitle,
                CrowdfoxHelper::$TITLE_MAX_LENGTH);
            $data['submit']['Description'] = isset($prepare['Description']) ? $prepare['Description'] : CrowdfoxHelper::sanitizeDescription($defaultDescription,
                CrowdfoxHelper::$DESC_MAX_LENGTH);

            $imagePath = getDBConfigValue($this->marketplace . '.imagepath', $this->_magnasession['mpID'], SHOP_URL_POPUP_IMAGES);
            $imagePath = trim($imagePath, '/ ') . '/';
            if (empty($prepare['Images']) === false) {
                $pictureUrls = json_decode($prepare['Images']);

                foreach ($pictureUrls as $image => $use) {
                    if ($use == 'true') {
                        $data['submit']['Images'][] = array(
                            'URL' => $imagePath . $image,
                        );
                    }
                }
            } else if (isset($product['Images'])) {
                foreach ($product['Images'] as $image) {
                    $data['submit']['Images'][] = array(
                        'URL' => $imagePath . $image,
                    );

                    if (count($data['submit']['Images']) == CrowdfoxHelper::$MAX_NUMBER_OF_IMAGES) {
                        break;
                    }
                }
            }

            $data['submit']['DeliveryTime'] = isset($prepare['DeliveryTime']) ? $prepare['DeliveryTime'] : '';
            $data['submit']['DeliveryCosts'] = isset($prepare['DeliveryCost']) ? $prepare['DeliveryCost'] : '';
            $data['submit']['ShippingMethod'] = isset($prepare['ShippingMethod']) ? $prepare['ShippingMethod'] : '';
            $data['submit']['Brand'] = isset($prepare['Brand']) ? $prepare['Brand'] : '';
            $data['submit']['ManufacturerNumber'] = isset($prepare['MPN']) ? $prepare['MPN'] : '';
            $data['submit']['AdditionalAttributes'] = '';
            if (!empty($prepare['ShopVariation'])) {
	            $data['submit']['AdditionalAttributes'] = CrowdfoxHelper::gi()->convertMatchingToNameValue(
	            	json_decode($prepare['ShopVariation'],true),
		            $product
	            );
            }
            $data['submit']['AdditionalAttributes'] = $this->prepareAttributesForMP($data['submit']['AdditionalAttributes']);
        }

        $data['submit']['Link'] = !empty($product['ProductUrl']) ? $product['ProductUrl'] : '';
        $data['submit']['CategoryPath'] = renderCategoryPath($pID, 'product', ' > ');

        $basePrice = '';
        $baseUnit = '';
        if (!empty($product['BasePrice'])) {
            $baseUnit = !empty($product['BasePrice']['Unit']) ? $product['BasePrice']['Unit'] : '';
            $basePrice = !empty($product['BasePrice']['Value']) ? $product['BasePrice']['Value'] : '';
            $this->simpleprice->setPrice($basePrice);
            $basePrice = $this->simpleprice->roundPrice()->makeSignalPrice(getDBConfigValue($this->marketplace . '.price.signal',
                $this->mpID, ''))->getPrice();
        }

        $data['submit']['ObligationInfo'] = sprintf(ML_CROWDFOX_OBLIGATION_INFO, $baseUnit, $basePrice);
        $data['submit']['Price'] = $data['price'];
        $data['submit']['Quantity'] = $data['quantity'] < 0 ? 0 : $data['quantity'];

        if (!$this->getCrowdfoxVariations($product, $data, $imagePath, json_decode($prepare['ShopVariation'], true))) {
            return;
        }
    }

    protected function preSubmit(&$request) {
        $request['DATA'] = array();
        foreach ($this->selection as $iProductId => &$aProduct) {
            if (empty($aProduct['submit']['Variations'])) {
                $request['DATA'][] = $aProduct['submit'];
                continue;
            }

            foreach ($aProduct['submit']['Variations'] as $aVariation) {
                $aVariationData = $aProduct;
                unset($aVariationData['submit']['Variations']);
                foreach ($aVariation as $sParameter => $mParameterValue) {
                    $aVariationData['submit'][$sParameter] = $mParameterValue;
                }

                $request['DATA'][] = $aVariationData['submit'];
            }
        }

        arrayEntitiesToUTF8($request['DATA']);
    }

    protected function filterItem($pID, $data) {
        return array();
    }

    protected function filterSelection() {
        $b = parent::filterSelection();

        $shitHappend = false;
        $missingFields = array();
        foreach ($this->selection as $pID => &$data) {
            if ($data['submit']['Price'] <= 0) {
                // Loesche das Feld, um eine Fehlermeldung zu erhalten
                unset($data['submit']['Price']);
            }

            $mfC = array();

            $this->requirementsMet($data['submit'], $this->initSession['RequiredFileds'], $mfC);
            $mfC = array_merge($mfC, $this->filterItem($pID, $data['submit']));

            if (!empty($mfC)) {
                foreach ($mfC as $key => $field) {
                    $mfC[$key] = $field;
                }
                $sku = magnaPID2SKU($pID);
                //echo print_m($mfC, $sku);
                //*
                MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, array(
                    'mpID' => $this->mpID,
                    'errormessage' => json_encode(array(
                        'MissingFields' => $mfC,
                    )),
                    'dateadded' => gmdate('Y-m-d H:i:s'),
                    'additionaldata' => serialize(array(
                        'SKU' => $sku,
                    )),
                ));
                //*/
                $shitHappend = true;
                $this->badItems[] = $pID;
                unset($this->selection[$pID]);
            }
        }
        $this->badItems = array_unique($this->badItems);

        return $b || $shitHappend;
    }

    protected function generateRedirectURL($state) {
        return toURL(array(
            'mp' => $this->realUrl['mp'],
            'mode' => ($state == 'fail') ? 'errorlog' : 'listings',
        ), true);
    }

    protected function generateRequestHeader() {
        return array(
            'ACTION' => 'AddItems',
            'MODE' => $this->submitSession['mode'],
        );
    }

    protected function getCrowdfoxVariations($product, &$data, $imagePath, $categoryAttributes) {
        if ($this->checkinSettings['Variations'] != 'yes') {
            return true;
        }

        $variations = array();
        foreach ($product['Variations'] as $v) {
            $this->simpleprice->setPrice($v['Price']['Price']);
            $price = $this->simpleprice->roundPrice()->makeSignalPrice(getDBConfigValue($this->marketplace . '.price.signal',
                $this->mpID, ''))->getPrice();

            $vi = array(
                'SKU' => ($this->settings['keytype'] == 'artNr') ? $v['MarketplaceSku'] : $v['MarketplaceId'],
                'Price' => $price,
                'Currency' => $this->settings['currency'],
                'Quantity' => ($this->quantityLumb === false) ? max(0,
                    $v['Quantity'] - (int)$this->quantitySub) : $this->quantityLumb,
            );

            $gtinColumnConfigTable = getDBConfigValue($this->marketplace . '.prepare.gtincolumn.dbmatching.table', $this->mpID);
            $gtinColumnConfigAlias = getDBConfigValue($this->marketplace . '.prepare.gtincolumn.dbmatching.alias', $this->mpID);
            $gtinColumnConfigAlias = empty($gtinColumnConfigAlias) ? 'products_id' : $gtinColumnConfigAlias;
            $vi['GTIN'] = CrowdfoxHelper::getDataFromConfig($v['VariationId'], $gtinColumnConfigTable, $gtinColumnConfigAlias);

            if (empty($vi['GTIN'])) {
                $gtinColumnConfigTable['table'] = 'products_properties_combis';
                $gtinColumnConfigTable['column'] = str_replace('products', 'combi', $gtinColumnConfigTable['column']);
                $vi['GTIN'] = CrowdfoxHelper::getDataFromConfig($v['VariationId'], $gtinColumnConfigTable,
                    'products_properties_combis_id');
            }

            $basePrice = '';
            $baseUnit = '';
            if (!empty($v['BasePrice'])) {
                $baseUnit = !empty($v['BasePrice']['Unit']) ? $v['BasePrice']['Unit'] : '';
                $basePrice = !empty($v['BasePrice']['Value']) ? $v['BasePrice']['Value'] : '';
                $this->simpleprice->setPrice($basePrice);
                $basePrice = $this->simpleprice->roundPrice()->makeSignalPrice(getDBConfigValue($this->marketplace . '.price.signal',
                    $this->mpID, ''))->getPrice();
            }

            $vi['ObligationInfo'] = sprintf(ML_CROWDFOX_OBLIGATION_INFO, $baseUnit, $basePrice);

            $vi['ItemTitle'] = $data['submit']['ItemTitle'];
            foreach ($v['Variation'] as $varAttribute) {
                $vi['ItemTitle'] .= ' ' . $varAttribute['Name'] . ' - ' . $varAttribute['Value'];
            }

            if (empty($v['Images'])) {
                $vi['Images'] = $data['submit']['Images'];
            } else {
                foreach ($v['Images'] as $image) {
                    $vi['Images'][] = array(
                        'URL' => $imagePath . $image,
                        'id' => $image,
                    );

                    if (count($vi['Images']) == CrowdfoxHelper::$MAX_NUMBER_OF_IMAGES) {
                        break;
                    }
                }
            }

            $vi['AdditionalAttributes'] = $this->fixVariationCategoryAttributes($categoryAttributes, $product, $v, $vi);
            $vi['AdditionalAttributes'] = $this->prepareAttributesForMP($vi['AdditionalAttributes']);
            $variations[] = $vi;
        }

        $data['submit']['Variations'] = $variations;

        return true;
    }

    protected function processSubmitResult($result) {
        parent::processSubmitResult($result);
        if (array_key_exists('ERRORS', $result) && is_array($result['ERRORS']) && !empty($result['ERRORS'])) {
            foreach ($result['ERRORS'] as $err) {
                if (isset($err['ERRORDATA']['SKU'])) {
                    $SKU = $err['ERRORDATA']['SKU'];
                    foreach ($this->selection as $pID => &$data) {
                        if ($data['submit']['SKU'] === $SKU) {
                            $this->badItems[] = $pID;
                            unset($this->selection[$pID]);
                            break;
                        }
                    }
                }
            }
        }
    }

    private function fixVariationCategoryAttributes($aCatAttributes, $product, $variationDB, $variation) {
	    $productDataForMatching = array_merge($product, $variationDB);
	    $productDataForMatching['ProductId'] = $variationDB['VariationId'];
	    $productDataForMatching['ProductsModel'] = $variationDB['MarketplaceSku'];

	    if (!isset($variationDB['Weight']['Value'])) {
		    $productDataForMatching['Weight'] = $product['Weight'];
	    }

	    if (!isset($variationDB['BasePrice']['Value'])) {
		    $productDataForMatching['BasePrice'] = $product['BasePrice'];
	    }

	    // Since variation attributes are not set directly on product and their key is number, we should prefix them for
	    // standard AM conversion because otherwise variation attributes are no different from any other shop attribute
	    foreach ($variationDB['Variation'] as $variationAttribute) {
		    $productDataForMatching["variant_{$variationAttribute['NameId']}"] = $variationAttribute['ValueId'];
	    }

	    $fixCatAttributes = CrowdfoxHelper::gi()->convertMatchingToNameValue($aCatAttributes, $productDataForMatching);

	    return $fixCatAttributes;
    }

    private function prepareAttributesForMP($additionalAttributes) {
        if (empty($additionalAttributes) || !is_array($additionalAttributes)) {
            return '';
        }

        $preparedAttributes = array();
        foreach ($additionalAttributes as $key => $value) {
            if ((!empty($key)) && (isset($value) && $value != '' && $value != null)) {
                $preparedAttributes[] = array('key' => $key, 'value' => strip_tags($value));
            }
        }

        return empty($preparedAttributes) ? '' : $preparedAttributes;
    }

}
