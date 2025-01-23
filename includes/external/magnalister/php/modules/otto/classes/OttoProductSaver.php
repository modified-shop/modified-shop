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

require_once(DIR_MAGNALISTER_MODULES.'otto/prepare/OttoCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'otto/OttoHelper.php');
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class OttoProductSaver {
    const DEBUG = false;

    /**
     * @var array
     */
    public $aErrors = array();

    /**
     * @var array
     */
    protected $aMagnaSession = array();

    /**
     * @var mixed|string
     */
    protected $sMarketplace = '';

    /**
     * @var int
     */
    protected $sMpId = 0;

    /**
     * @var array
     */
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

        $aRow['Description'] = strip_tags($aItemDetails['Description'], '<p><ul><ol><li><span><br><b>');
        $aRow['DeliveryType'] = $aItemDetails['DeliveryType'];
        $aRow['DeliveryTime'] = $aItemDetails['DeliveryTime'];
        if (empty($aItemDetails['GalleryPictures'])
            || !isset($aItemDetails['GalleryPictures']['Images'])
            || empty($aItemDetails['GalleryPictures']['Images'])) {
            $aRow['Images'] = '';
            $this->aErrors['ML_RICARDO_ERROR_IMAGES'] = ML_RICARDO_ERROR_IMAGES;
        } else {
            $aRow['Images'] = array();
            foreach ($aItemDetails['GalleryPictures']['Images'] as $name => $checked) {
                if ($checked === 'true') $aRow['Images'][] = $imagePath.$name;
            }
        }
        if (empty($aRow['Images'])) {
            $this->aErrors['ML_RICARDO_ERROR_IMAGES'] = ML_RICARDO_ERROR_IMAGES;
        }
        $aRow['Images'] = json_encode($aRow['Images']);

        if (!empty($aItemDetails['MainImage'])) {
            $aRow['MainImage'] = $aItemDetails['MainImage'];
        }
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
            $m = new OttoCategoryMatching();
            $aRow['PrimaryCategoryName'] = $m->getOttoCategoryName($aItemDetails['PrimaryCategory']);
        }

        $aRow['ShopVariation'] = $aItemDetails['ShopVariation'];
        $aRow['CategoryIndependentShopVariation'] = $aItemDetails['CategoryIndependentShopVariation'];
        // Image -> depends if Single or Multi

        if (!empty($this->aErrors)) {
            $aRow['Verified'] = 'ERROR';
        }

        return $aRow;
    }

    protected function insertPrepareData($aData) {
        /* {Hook} "OttoInsertPrepareData": Enables you to modify the prepared product data before it will be saved.<br>
            Variables that can be used:
            <ul>
             <li><code>$aData</code>: The data of a product.</li>
             <li>$this->mpID</code>: The ID of the marketplace.</li>
            </ul>
        */
        if (($hp = magnaContribVerify('OttoInsertPrepareData', 1)) !== false) {
            require($hp);
        }
        if (self::DEBUG) {
            echo print_m($aData, __METHOD__);
            die();
        }
        #echo print_m($aData, __METHOD__.' '.__LINE__);
        MagnaDB::gi()->insert(TABLE_MAGNA_OTTO_PREPARE, $aData, true);
    }

    // TODO testen

    public function saveMultipleProductProperties($aProductIds, $aItemDetails) {
        $aProductDescData = MagnaDB::gi()->fetchArray('
			SELECT products_id, products_name, products_description
			  FROM '.TABLE_PRODUCTS_DESCRIPTION.'
			 WHERE products_id IN ('.implode(', ', $aProductIds).')
			   AND language_id = "'.getDBConfigValue('otto.lang', $this->mpId).'" 
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
        // don't allow more than 10 images (otherwise Otto rejects the item)
        foreach ($aProductIds as $iProductId) {
            if (count($aProductDescDataByPId[$iProductId]['images']) > 10) {
                $aProductDescDataByPId[$iProductId]['images'] = array_slice($aProductDescDataByPId[$iProductId]['images'], 0, 10);
            }
        }
        foreach ($aProductIds as $iProductId) {
            $aRow = $this->preparePropertiesRow($iProductId, $aItemDetails);
            $aRow['Description'] = strip_tags($aProductDescDataByPId[$iProductId]['products_description'], '<p><ul><ol><li><span><br><b>');
            // Filter JNH Tab
            if (getDBConfigValue('gambio.tabs.display', 0, 'h1') == 'none') {
                if (strpos($aRow['Description'], '[TAB:')) {
                    $aRow['Description'] = substr($aRow['Description'], 0, strpos($aRow['Description'], '[TAB:'));
                }
            } else {
                $aRow['Description'] = preg_replace('/\[TAB:([^\]]*)\]/', '<b>${1}</b>', $aRow['Description']);
            }

            $aRow['DeliveryType'] = $aItemDetails['DeliveryType'];
            $aRow['DeliveryTime'] = $aItemDetails['DeliveryTime'];
            $aRow['Images'] = json_encode($aProductDescDataByPId[$iProductId]['images']);
            $this->insertPrepareData($aRow);
        }
    }
}
