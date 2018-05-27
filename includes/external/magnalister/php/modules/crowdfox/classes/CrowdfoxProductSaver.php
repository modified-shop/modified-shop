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
 * $Id: $
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class CrowdfoxProductSaver {
    const DEBUG = false;
    public $aErrors = array();

    protected $aMagnaSession = array();
    protected $sMarketplace = '';
    protected $sMpId = 0;

    protected $aConfig = array();

    public function __construct($magnaSession) {
        $this->aMagnaSession = &$magnaSession;
        $this->sMarketplace = $this->aMagnaSession['currentPlatform'];
        $this->mpId = $this->aMagnaSession['mpID'];

        $this->aConfig['keytype'] = getDBConfigValue('general.keytype', '0');
    }

    protected function insertPrepareData($aData) {
        if (($hp = magnaContribVerify('CrowdfoxInsertPrepareData', 1)) !== false) {
            require($hp);
        }
        if (self::DEBUG) {
            echo print_m($aData, __METHOD__);
            die();
        }
        MagnaDB::gi()->insert(TABLE_MAGNA_CROWDFOX_PREPARE, $aData, true);
    }

    /**
     * Hilfsfunktion fuer SaveHoodSingleProductProperties und SaveHoodMultipleProductProperties
     * bereite die DB-Zeile vor mit allen Daten die sowohl fuer Single als auch Multiple inserts gelten
     */
    protected function preparePropertiesRow($iProductId, $aItemDetails) {
        $aRow = array();
        $bVerifiedError = false;
        $aRow['mpID'] = $this->mpId;
        $aRow['products_id'] = $iProductId;
        $result = MagnaDB::gi()->fetchArray('
			SELECT products_model, products_ean
			  FROM ' . TABLE_PRODUCTS . '
			 WHERE products_id =' . $iProductId);

        $aRow['products_model'] = $result[0]['products_model'];
        $aRow['PrepareType'] = 'Apply';
        $aRow['Verified'] = 'OK';

        // If Title is not set multi prepare is used so Title and Description should be used from product.
        if (isset($aItemDetails['ItemTitle']) === false) {
            $lang = getDBConfigValue($this->sMarketplace . '.lang', $this->mpId);

            $prod = MagnaDB::gi()->fetchArray('
				SELECT 
					p.products_id,
					p.products_model,
					p.products_ean as EAN,
					p.products_image as PictureUrl,
					p.manufacturers_id as Brand,
					pd.products_name as ItemTitle,
					pd.products_description as Description
				FROM ' . TABLE_PRODUCTS . ' p
				LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON pd.products_id = p.products_id AND pd.language_id = "' .
                $lang . '"
				WHERE p.products_id = ' . $iProductId);

            CrowdfoxHelper::getTitleDescriptionEan($prod, $this->mpId);
            $aItemDetails['ItemTitle'] = $prod[0]['ItemTitle'];
            $aItemDetails['Description'] = $prod[0]['Description'];
            $aItemDetails['GTIN'] = $prod[0]['GTIN'];
            $aItemDetails['MPN'] = CrowdfoxHelper::getManufacturerPartNumber($prod[0]['products_id'], $this->marketplace,
                $this->mpId);
            if ($prod[0]['Brand'] > 0) {
                $aItemDetails['Brand'] = (string)MagnaDB::gi()->fetchOne('SELECT manufacturers_name FROM ' . TABLE_MANUFACTURERS .
                    ' WHERE manufacturers_id=\'' . $prod[0]['Brand'] . '\'');
            } else {
                $aItemDetails['Brand'] = '';
            }
        }

        if (isset($aItemDetails['Images'])) {
            $aImages = (array)$aItemDetails['Images'];
            if (in_array('false', $aImages)) {
                array_shift($aImages);
            }

            $aPictureURL = array();
            if (!empty($aImages)) {
                foreach ($aImages as $key => $value) {
                    $aPictureURL[urldecode($key)] = $value;
                }
            }

            if (count($aPictureURL) === 0) {
                $this->aErrors['ML_CROWDFOX_IMAGES_REQUIRE_ERROR'] = ML_CROWDFOX_IMAGES_REQUIRE_ERROR;
                $bVerifiedError = true;
            } else if (count($aPictureURL) > CrowdfoxHelper::$MAX_NUMBER_OF_IMAGES) {
                $this->aErrors['ML_CROWDFOX_MAX_IMAGES_ERROR'] = ML_CROWDFOX_MAX_IMAGES_ERROR;
                $bVerifiedError = true;
            } else {
                $aRow['Images'] = json_encode($aPictureURL);
            }
        } else {
            $aProduct = MLProduct::gi()->setLanguage(getDBConfigValue($this->sMarketplace . '.lang', $this->mpId))
                ->getProductById($iProductId);

            $images = array();

            $numberOfImages = 0;
            foreach ($aProduct['Images'] as $image) {
                $images[$image] = 'true';
                $numberOfImages++;
                if ($numberOfImages == CrowdfoxHelper::$MAX_NUMBER_OF_IMAGES) {
                    break;
                }
            }

            if (count($images) === 0) {
                $this->aErrors['ML_CROWDFOX_IMAGES_REQUIRE_ERROR'] = ML_CROWDFOX_IMAGES_REQUIRE_ERROR;
                $bVerifiedError = true;
            } else {
                $aRow['Images'] = json_encode($images);
            }
        }

        $aRow['ItemTitle'] = CrowdfoxHelper::sanitizeTitle($aItemDetails['ItemTitle'], CrowdfoxHelper::$TITLE_MAX_LENGTH);
        if (!isset($aRow['ItemTitle']) || $aRow['ItemTitle'] === '') {
            $this->aErrors['ML_CROWDFOX_ERROR_TITLE'] = ML_CROWDFOX_ERROR_TITLE;
            $bVerifiedError = true;
        }

        // In case if client directly on prepare form entered new line
        $aRow['Description'] = str_replace("\r", ' ', $aItemDetails['Description']);
        $aRow['Description'] = str_replace("\n", ' ', $aRow['Description']);
        if (!isset($aRow['Description']) || $aRow['Description'] === '') {
            $this->aErrors['ML_CROWDFOX_ERROR_DESCRIPTION'] = ML_CROWDFOX_ERROR_DESCRIPTION;
            $bVerifiedError = true;
        }

        $aRow['GTIN'] = $aItemDetails['GTIN'];
        $aRow['DeliveryTime'] = empty($aItemDetails['deliverytime']) ? '' : $aItemDetails['deliverytime'];
        if (empty($aItemDetails['deliverytime'])) {
            $this->aErrors['ML_CROWDFOX_ERROR_SHIPPING_TIME'] = ML_CROWDFOX_ERROR_SHIPPING_TIME;
            $bVerifiedError = true;
        }

        $aRow['DeliveryCost'] =$aItemDetails['deliverycost'];
        if (!is_numeric($aRow['DeliveryCost'])) {
            $this->aErrors['ML_CROWDFOX_ERROR_SHIPPING_COST'] = ML_CROWDFOX_ERROR_SHIPPING_COST;
            $bVerifiedError = true;
        }

        $aRow['ShippingMethod'] = empty($aItemDetails['shippingmethod_id']) ? '' : $aItemDetails['shippingmethod_id'];
        $aRow['Brand'] = empty($aItemDetails['Brand']) ? '' : $aItemDetails['Brand'];
        $aRow['MPN'] = empty($aItemDetails['MPN']) ? '' : $aItemDetails['MPN'];
        $aRow['ShopVariation'] = empty($aItemDetails['CategoryAttributes']) ? '' : $aItemDetails['CategoryAttributes'];

        if ($bVerifiedError) {
            $aRow['Verified'] = 'ERROR';
        }

        return $aRow;
    }

    public function saveSingleProductProperties($iProductId, $aItemDetails, $prepareType, $isAjax = false) {
        //No SingleProductSave at this Time so use Multi
        $this->saveMultipleProductProperties(array($iProductId), $aItemDetails, $prepareType, $isAjax);
    }

    public function saveMultipleProductProperties($iProductIds, $aItemDetails, $prepareType, $isAjax = false) {
        $preparedTs = date('Y-m-d H:i:s');
        foreach ($iProductIds as $iProductId) {
            $aRow = $this->preparePropertiesRow($iProductId, $aItemDetails);
            $aRow['PreparedTs'] = $preparedTs;
            $this->insertPrepareData($aRow);
        }
    }
}
