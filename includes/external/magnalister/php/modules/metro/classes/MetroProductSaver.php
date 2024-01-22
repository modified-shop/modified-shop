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

require_once(DIR_MAGNALISTER_MODULES.'metro/prepare/MetroCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'metro/MetroHelper.php');
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class MetroProductSaver {
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

    public function saveSingleProductProperties($iProductId, $aItemDetails) {
        $aRow = $this->preparePropertiesRow($iProductId, $aItemDetails);
        $imagePath = getDBConfigValue($this->sMarketplace.'.imagepath', $this->mpId, '');
        if (empty($imagePath)) {
            $imagePath = SHOP_URL_POPUP_IMAGES;
        }
        $aRow['Title'] = $aItemDetails['Title'];
        $aRow['Description'] = MetroHelper::MetroStripTagsFromDescription($aItemDetails['Description']);
        $aRow['ShortDescription'] = MetroHelper::sanitizeDescription($aItemDetails['ShortDescription']);

        // Check for selected Images
        if (empty($aItemDetails['GalleryPictures'])
            || !isset($aItemDetails['GalleryPictures']['Images'])
            || empty($aItemDetails['GalleryPictures']['Images'])
        ) {
            $aRow['Images'] = '';
            $this->aErrors['ML_RICARDO_ERROR_IMAGES'] = ML_RICARDO_ERROR_IMAGES;
            // Without images product preparation is not successfull
            $aRow['Verified'] = 'ERROR';
        } else {
            $aRow['Images'] = array();
            foreach ($aItemDetails['GalleryPictures']['Images'] as $name => $checked) {
                if ($checked === 'true') $aRow['Images'][] = $imagePath.$name;
            }
        }
        if (empty($aRow['Images'])) {
            $this->aErrors['ML_RICARDO_ERROR_IMAGES'] = ML_RICARDO_ERROR_IMAGES;
            // Without images product preparation is not successfull
            $aRow['Verified'] = 'ERROR';
        }
        $aRow['Images'] = json_encode($aRow['Images']);
        $aRow['Manufacturer'] = $aItemDetails['Manufacturer'];
        $aRow['ManufacturerPartNumber'] = $aItemDetails['ManufacturerPartNumber'];
        $aRow['GTIN'] = $aItemDetails['GTIN'];
        $aRow['Brand'] = $aItemDetails['Brand'];
        $aRow['Feature'] = serialize($aItemDetails['Feature']);
        $this->insertPrepareData($aRow);
    }

    /**
     * Hilfsfunktion fuer SaveSingleProductProperties und SaveMultipleProductProperties
     * bereite die DB-Zeile vor mit allen Daten die sowohl fuer Single als auch Multiple inserts gelten
     */
    protected function preparePropertiesRow($iProductId, $aItemDetails) {
        $aRow = array();
        $aRow['mpID'] = $this->mpId;
        $aRow['products_id'] = $iProductId;
        $aRow['products_model'] = MagnaDB::gi()->fetchOne('
			SELECT products_model
			  FROM '.TABLE_PRODUCTS.'
			 WHERE products_id ='.$iProductId
        );

        $aRow['PreparedTS'] = date('Y-m-d H:i:s');
        $aRow['Verified'] = 'OK'; // MP & API provides no Verify Request
        // Title, Description -> depends if Single or Multi

        if (!isset($aItemDetails['PrimaryCategory']) || $aItemDetails['PrimaryCategory'] === '') {
            $this->aErrors['ML_RICARDO_ERROR_CATEGORY'] = ML_RICARDO_ERROR_CATEGORY;
        } else {
            $aRow['PrimaryCategory'] = $aItemDetails['PrimaryCategory'];
            $m = new MetroCategoryMatching();
            $aRow['PrimaryCategoryName'] = $m->getMetroCategoryPath($aItemDetails['PrimaryCategory']);
        }

        if (!isset($aItemDetails['Title']) || $aItemDetails['Title'] === '') {
            $this->aErrors['ML_RICARDO_ERROR_TITLE'] = ML_RICARDO_ERROR_TITLE;
        } else {
            $aRow['Title'] = $aItemDetails['Title'];
        }
        if (
            (
                !isset($aItemDetails['GTIN'])
                || $aItemDetails['GTIN'] === ''
                || !preg_match('/^\d+$/', $aItemDetails['GTIN'])
                || strlen($aItemDetails['GTIN']) > 14
            )
            && (empty($aItemDetails['Manufacturer']) || empty($aItemDetails['ManufacturerPartNumber']))
        ) {
            $this->aErrors['ML_METRO_ERROR_GTIN'] = ML_METRO_ERROR_GTIN;
        } else {
            $aRow['GTIN'] = $aItemDetails['GTIN'];
        }

        //$aRow['ShopVariation'] = $aItemDetails['CategoryAttributes'];
        // TODO Attributes Matching

        $aRow['ShopVariation'] = $aItemDetails['ShopVariation'];
        $aRow['ShippingProfile'] = $aItemDetails['ShippingProfile'];
        $aRow['ShippingGroup'] = $aItemDetails['ShippingGroup'];
        // Image -> depends if Single or Multi

        $aRow['ProcessingTime'] = $aItemDetails['ProcessingTime'];
        $aRow['MaxProcessingTime'] = $aItemDetails['MaxProcessingTime'];
        $aRow['BusinessModel'] = $aItemDetails['BusinessModel'];
        $aRow['FreightForwarding'] = $aItemDetails['FreightForwarding'];
        $msrp = (float)number_format(MetroHelper::str2float($aItemDetails['MSRP']), 2, '.', '');
        if ($msrp !== 0.0) {
            $aRow['MSRP'] = is_float($msrp) ? $msrp : null;
        }

        if (!empty($this->aErrors)) {
            $aRow['Verified'] = 'ERROR';
        }

        return $aRow;
    }

    protected function insertPrepareData($aData) {
        /* {Hook} "MetroInsertPrepareData": Enables you to modify the prepared product data before it will be saved.<br>
            Variables that can be used:
            <ul>
             <li><code>$aData</code>: The data of a product.</li>
             <li>$this->mpID</code>: The ID of the marketplace.</li>
            </ul>
        */
        if (($hp = magnaContribVerify('MetroInsertPrepareData', 1)) !== false) {
            require($hp);
        }
        if (self::DEBUG) {
            echo print_m($aData, __METHOD__);
            die();
        }
        #echo print_m($aData, __METHOD__.' '.__LINE__);
        MagnaDB::gi()->insert(TABLE_MAGNA_METRO_PREPARE, $aData, true);
    }

    // TODO testen

    public function saveMultipleProductProperties($aProductIds, $aItemDetails) {
        $sMoreDesc = '';
        if (MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION)) {
            $sMoreDesc .= ', products_short_description';
        }
        if (MagnaDB::gi()->columnExistsInTable('products_meta_description', TABLE_PRODUCTS_DESCRIPTION)) {
            $sMoreDesc .= ', products_meta_description';
        }
        $aProductDescData = MagnaDB::gi()->fetchArray('
			SELECT products_id, products_name, products_description'.$sMoreDesc.'
			  FROM '.TABLE_PRODUCTS_DESCRIPTION.'
			 WHERE products_id IN ('.implode(', ', $aProductIds).')
			   AND language_id = "'.getDBConfigValue('metro.lang', $this->mpId).'" 
		');
        $aProductMainImagesAndEANs = MagnaDB::gi()->fetchArray('
			SELECT products_id, products_image, products_ean
			  FROM '.TABLE_PRODUCTS.'
			 WHERE products_id IN ('.implode(', ', $aProductIds).')
		');
        $aProductImageData = MagnaDB::gi()->fetchArray('
			SELECT products_id, image_nr, image_name
			  FROM '.TABLE_PRODUCTS_IMAGES.'
			 WHERE products_id IN ('.implode(', ', $aProductIds).')
			 ORDER BY products_id
		');
        $imagePath = getDBConfigValue($this->sMarketplace.'.imagepath', $this->mpId, '');
        if (empty($imagePath)) {
            $imagePath = SHOP_URL_POPUP_IMAGES;
        }
        $aProductManufacturerData = MagnaDB::gi()->fetchArray('
			SELECT p.products_id, mf.manufacturers_name Manufacturer
			  FROM '.TABLE_PRODUCTS.' p, '.TABLE_MANUFACTURERS.' mf
			 WHERE p.products_id IN ('.implode(', ', $aProductIds).')
                           AND mf.manufacturers_id = p.manufacturers_id
			 ORDER BY products_id
		');
        if (MagnaDB::gi()->tableExists('products_item_codes')) {
            $aProductBrandData = MagnaDB::gi()->fetchArray('
			SELECT products_id, brand_name Brand
			  FROM products_item_codes
			 WHERE products_id IN ('.implode(', ', $aProductIds).')
			 ORDER BY products_id
		');
        }
        $aProductDescDataByPId = array();
        // products data by pID
        foreach ($aProductDescData as $pdd) {
            $aProductDescDataByPId[$pdd['products_id']] = $pdd;
            unset($aProductDescDataByPId[$pdd['products_id']]['products_id']);
            unset($aProductDescDataByPId[$pdd['products_id']]['products_image']);
            unset($aProductDescDataByPId[$pdd['products_id']]['products_ean']);
            unset($aProductDescDataByPId[$pdd['products_id']]['Manufacturer']);
        }
        // add main image and EAN
        foreach ($aProductMainImagesAndEANs as $pmi) {
            $aProductDescDataByPId[$pmi['products_id']]['images'] = array(0 => $imagePath.$pmi['products_image']);
            $aProductDescDataByPId[$pmi['products_id']]['products_ean'] = $pmi['products_ean'];
        }
        // add further images
        foreach ($aProductImageData as $imd) {
            $aProductDescDataByPId[$imd['products_id']]['images'][$imd['image_nr']] = $imagePath.$imd['image_name'];
        }
        // don't allow more than 10 images (otherwise Metro rejects the item)
        foreach ($aProductIds as $iProductId) {
            if (count($aProductDescDataByPId[$iProductId]['images']) > 10) {
                $aProductDescDataByPId[$iProductId]['images'] = array_slice($aProductDescDataByPId[$iProductId]['images'], 0, 10);
            }
        }
        // add manufacturer
        if (!empty($aProductManufacturerData)) {
            foreach ($aProductManufacturerData as $pmd) {
                $aProductDescDataByPId[$pmd['products_id']]['Manufacturer'] = $pmd['Manufacturer'];
            }
        }
        // add brand
        if (!empty($aProductBrandData)) {
            foreach ($aProductBrandData as $pbd) {
                $aProductDescDataByPId[$pbd['products_id']]['Brand'] = $pbd['Brand'];
            }
        }
        foreach ($aProductIds as $iProductId) {
            $aItemDetails['Title'] = $aProductDescDataByPId[$iProductId]['products_name'];
            $aItemDetails['GTIN'] = $aProductDescDataByPId[$iProductId]['products_ean'];
            $aRow = $this->preparePropertiesRow($iProductId, $aItemDetails);
            $aRow['BusinessModel'] = $aItemDetails['BusinessModel'];
            $aRow['Description'] = MetroHelper::MetroStripTagsFromDescription($aProductDescDataByPId[$iProductId]['products_description']);
            if(array_key_exists('products_short_description', $aProductDescDataByPId[$iProductId])) {
                $aRow['ShortDescription'] = MetroHelper::sanitizeDescription($aProductDescDataByPId[$iProductId]['products_short_description']);
            }
            if(array_key_exists('products_meta_description', $aProductDescDataByPId[$iProductId])) {
                $aRow['Feature'] = serialize(array_map('trim', array_slice(explode(',', ($aProductDescDataByPId[$iProductId]['products_meta_description'])), 0,5)));
            }
            $aRow['Images'] = json_encode($aProductDescDataByPId[$iProductId]['images']);
            if (!empty($aProductDescDataByPId[$iProductId]['Manufacturer'])) {
                $aRow['Manufacturer'] = $aProductDescDataByPId[$iProductId]['Manufacturer'];
            }
            if (!empty($aProductDescDataByPId[$iProductId]['Brand'])) {
                $aRow['Brand'] = $aProductDescDataByPId[$iProductId]['Brand'];
            }
            $this->insertPrepareData($aRow);
        }
    }
}
