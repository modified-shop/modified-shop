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
require_once(DIR_MAGNALISTER_MODULES.'googleshopping/GoogleshoppingHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'googleshopping/classes/GoogleshoppingProductSaver.php');

class GoogleshoppingCheckinSubmit extends MagnaCompatibleCheckinSubmit {
    private $bVerify = false;
    private $oLastException = null;

    private $mainLanguage;
    private $currency;

    public function __construct($settings = array()) {
        $gLangs = GoogleshoppingApiConfigValues::gi()->getLanguages();

        $this->mainLanguage = isset($gLangs['MainLanguage']) ? $gLangs['MainLanguage'] : 'de';
        $this->currency = isset($gLangs['Currency']) ? $gLangs['Currency'] : 'EUR';

        $settings = array_merge(array(
            'language' => $this->mainLanguage,
            'additionalLanguages' => $gLangs['AvailableLanguages'],
            'currency' => $this->currency,
            'mlProductsUseLegacy' => false,
        ), $settings);

        $this->summaryAddText = "<br /><br />\n".ML_GOOGLESHOPPING_UPLOAD_EXPLANATION;
        parent::__construct($settings);
    }

    protected function generateRequestHeader() {
        return array(
            'ACTION' => ($this->bVerify ? 'VerifyAddItems' : 'AddItems'),
            'SUBSYSTEM' => 'googleshopping',
            'MODE' => isset($this->submitSession['mode']) ? $this->submitSession['mode'] : 'ADD',
        );
    }

    protected function processException($e) {
        $this->oLastException = $e;
    }

    public function getLastException() {
        return $this->oLastException;
    }

    protected function setUpMLProduct() {
        // Set the language
        MLProduct::gi()->setLanguage($this->settings['additionalLanguages']);
    }

    protected function appendAdditionalData($iPID, $aProduct, &$aData) {
        $aPropertiesRow = MagnaDB::gi()->fetchRow(
            '
			SELECT * FROM '.TABLE_MAGNA_GOOGLESHOPPING_PREPARE.'
			 WHERE products_id = "'.$iPID.'" AND mpID = '.$this->_magnasession['mpID']
        );

        if (empty($aPropertiesRow)) {
            $data['submit'] = array();
            return;
        }

        foreach (array('MarketplaceCategories') as $jsonKey) {
            $aPropertiesRow[$jsonKey] = json_decode($aPropertiesRow[$jsonKey], true);
        }

        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $aData['submit']['SKU'] = $aProduct['ProductsModel'];
        } else {
            $aData['submit']['SKU'] = 'ML'.$aProduct['ProductId'];
        }


        $query = sprintf(
            'SELECT `CategoryName` FROM %s WHERE `CategoryID`=%d AND `Language`="%s"',
            TABLE_MAGNA_GOOGLESHOPPING_CATEGORIES,
            $aPropertiesRow['Primarycategory'],
            $_SESSION['language_code']
        );

        $categoryName = MagnaDB::gi()->fetchOne($query);

        $aData['submit']['title'] = $aPropertiesRow['title'];
        $aData['submit']['description'] = $aPropertiesRow['description'];
        $aData['submit']['channel'] = 'online';
        $aData['submit']['contentLanguage'] = $aPropertiesRow['contentLanguage'];
        $aData['submit']['OfferId'] = (getDBConfigValue('general.keytype','0') === 'artNr') ? $aProduct['MarketplaceSku'] : $aProduct['ProductId'];
        $aData['submit']['targetCountry'] = getDBConfigValue($this->marketplace.'.targetCountry', $this->mpID);
        $aData['submit']['brand'] = $aProduct['Manufacturer'];
        $aData['submit']['condition'] = $aPropertiesRow['condition'];
        $aData['submit']['currency'] = $aPropertiesRow['currency'];
        $aData['submit']['link'] = $aProduct['ProductUrl'].'&language='.$aPropertiesRow['contentLanguage'];
        $aData['submit']['itemGroupId'] = $aData['submit']['SKU'];
        $aData['submit']['availability'] = $aPropertiesRow['availability'];
        $aData['submit']['Verified'] = 'OK';
        $aData['submit']['PreparedTS'] = $aPropertiesRow['PreparedTS'];
        $aData['submit']['Primarycategory'] = $aPropertiesRow['Primarycategory'];
        $aData['submit']['PrimaryCategoryName'] = $categoryName;
        $aData['submit']['ProductId'] = $aProduct['ProductId'];
        $aData['submit']['MasterSKU'] = $aData['submit']['SKU'];


        $categoryAttributes = '';
        if (!empty($aPropertiesRow['CategoryAttributes'])) {
            $categoryAttributes = GoogleshoppingHelper::gi()->convertMatchingToNameValue(
                json_decode($aPropertiesRow['CategoryAttributes'], true),
                $aProduct
            );
        }

        $aData['submit']['CategoryAttributes'] = $categoryAttributes;

        //Images
        $sImagePath = getDBConfigValue($this->marketplace.'.imagepath', $this->mpID, '');
        if (empty($sImagePath)) {
            $sImagePath = SHOP_URL_POPUP_IMAGES;
        }

        $aImages = array();

        if (!empty($aProduct['Images'])) {
            foreach ($aProduct['Images'] as $sImg) {
                $aImages[] = $sImagePath.$sImg;
            }
        }

        $aData['submit']['Image']['url'] = $aImages;
        $aData['submit']['additionalImages'] = json_encode($aImages, true);
        $aData['submit']['EAN'] = $aProduct['EAN'];
        $aData['submit']['MPN'] = $aProduct['ManufacturerPartNumber'];

        //Quantity
        if ($aData['quantity'] < 0) {
            $aData['quantity'] = 0;
        }
        $aData['submit']['Quantity'] = $aData['quantity'];

        //Price
        if (isset($aData['price']) && !empty($aData['price'])) {
            $aData['submit']['Price'] = $aData['price'];
        } else {
            $aData['submit']['Price'] = $aProduct['Price'];
        }

        //BasePrice
        if (!empty($aProduct['BasePrice'])) {
            $aData['submit']['BasePrice'] = array(
                'Unit' => $aProduct['BasePrice']['Unit'][MLProduct::gi()->languageIdToCode($this->settings['language'])],
                'Value' => $aProduct['BasePrice']['Value'],
            );
        }

        if (!$this->getGoogleShoppingVariations($aProduct, $aData, $sImagePath, json_decode($aPropertiesRow['CategoryAttributes'], true))) {
            return;
        }
    }

    protected function getGoogleShoppingVariations($product, &$data, $imagePath, $categoryAttributes) {
        if ($this->checkinSettings['Variations'] !== 'yes') {
            return true;
        }

        $variations = array();
        foreach ($product['Variations'] as $v) {
            $this->simpleprice->setPrice($v['Price']);
            $price = $this->simpleprice->roundPrice()->makeSignalPrice(
                getDBConfigValue($this->marketplace.'.price.signal', $this->mpID, '')
            )->getPrice();

            $vi = array(
                'SKU' => (getDBConfigValue('general.keytype', '0') === 'artNr') ? $v['MarketplaceSku'] : $v['MarketplaceId'],
                'Price' => $price,
                'Quantity' => ($this->quantityLumb === false)
                    ? max(0, $v['Quantity'] - (int)$this->quantitySub)
                    : $this->quantityLumb,
            );

            //implementing the base price
            if (isset($v['BasePrice']) && empty($v['BasePrice']) === false) {
                $vi['BasePrice']['Unit'] = $v['BasePrice']['Unit'];
                $vi['BasePrice']['Value'] = number_format((float)$v['BasePrice']['Value'], 2, '.', '');
            }

            $vi['CategoryAttributes'] = $this->fixVariationCategoryAttributes($categoryAttributes, $product, $v);

            $variations[] = $vi;
        }

        if (!empty($variations)) {
            $data['submit']['Variations'] = $variations;
        }

        return true;
    }

    private function fixVariationCategoryAttributes($aCatAttributes, $product, $variationDB) {
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

        $fixCatAttributes = GoogleshoppingHelper::gi()->convertMatchingToNameValue($aCatAttributes, $productDataForMatching);

        return $fixCatAttributes;
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

    protected function markAsFailed($sku) {
        $iPID = magnaSKU2pID($sku);
        $this->badItems[] = $iPID;
        unset($this->selection[$iPID]);
    }

    public function verifyOneItem($bEchoRequest = false) {
        $this->bVerify = true;
        MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
            'mpID' => $this->_magnasession['mpID'],
            'selectionname' => $this->settings['selectionName'].'Verify',
            'session_id' => session_id()
        ));
        $item = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID="'.$this->_magnasession['mpID'].'" AND
			       selectionname="'.$this->settings['selectionName'].'" AND
			       session_id="'.session_id().'"
			 LIMIT 1
		');
        if (empty($item)) {
            return false;
        }

        $oldSelectionName = $this->settings['selectionName'];
        $this->settings['selectionName'] = $this->settings['selectionName'].'Verify';
        $item['selectionname'] = $this->settings['selectionName'];
        MagnaDB::gi()->insert(TABLE_MAGNA_SELECTION, $item);

        $this->initSelection(0, 1);
        //echo print_m($this->selection, '$this->selection[1]');
        foreach ($this->selection as $pID => &$data) {
            if (!isset($data['quantity']) || ($data['quantity'] == 0)) {
                $data['quantity'] = 1; // hack to get verification of zero quantity items working
            }
        }

        $this->populateSelectionWithData();

        $aResult = array(
            'STATUS' => 'SUCCESS'
        );


        MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
            'mpID' => $this->_magnasession['mpID'],
            'selectionname' => $this->settings['selectionName'],
            'session_id' => session_id()
        ));

        // restore selection name
        $this->settings['selectionName'] = $oldSelectionName;

        $aSelectedPIDs = MagnaDB::gi()->fetchArray('
			SELECT DISTINCT pID
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID = "'.$this->_magnasession['mpID'].'"
			       AND selectionname = "'.$this->settings['selectionName'].'"
			       AND session_id = "'.session_id().'"
		');
        $sSelectedPIDsList = '';
        foreach ($aSelectedPIDs as $pIDsRow) {
            if (is_numeric($pIDsRow['pID'])) {
                $sSelectedPIDsList .= $pIDsRow['pID'].', ';
            }
        }
        $sSelectedPIDsList = trim($sSelectedPIDsList, ', ');
        MagnaDB::gi()->query('
			UPDATE '.TABLE_MAGNA_GOOGLESHOPPING_PREPARE.'
			   SET Verified = "'.(('SUCCESS' == $aResult['STATUS']) ? 'OK' : 'ERROR').'"
			 WHERE mpID = '.$this->_magnasession['mpID'].'
				   AND products_id IN ('.$sSelectedPIDsList.')
		');

        return $aResult;
    }

    protected function generateRedirectURL($state) {
        return toURL(array(
            'mp' => $this->realUrl['mp'],
            'mode' => 'listings',
        ), true);
    }

    protected function postSubmit() {
//        try {
//            MagnaConnector::gi()->submitRequest(array(
//                'ACTION' => 'UploadItems',
//            ));
//        } catch (MagnaException $e) {
//            $this->submitSession['api']['exception'] = $e;
//            $this->submitSession['api']['html'] = MagnaError::gi()->exceptionsToHTML();
//
//            $response = $e->getResponse();
//            $this->ajaxReply['state']['submmited'] -= count($response['ERRORS']);
//            $this->ajaxReply['state']['success'] -= count($response['ERRORS']);
//            $this->ajaxReply['state']['failed'] += count($response['ERRORS']);
//            $this->ajaxReply['redirect'] = $this->generateRedirectURL('fail');
//        }
    }
}
