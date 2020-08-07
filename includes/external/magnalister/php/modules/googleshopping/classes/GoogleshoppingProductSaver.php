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

class GoogleshoppingProductSaver {
    const DEBUG = false;
    public $aErrors = array();
    public $aMissingFields = array();

    protected $aMagnaSession = array();
    protected $sMarketplace = '';
    protected $sMpId = 0;

    protected $fPrice = null;

    protected $aConfig = array();


    public function __construct($magnaSession) {
        $this->aMagnaSession = &$magnaSession;
        $this->sMarketplace = $this->aMagnaSession['currentPlatform'];
        $this->mpId = $this->aMagnaSession['mpID'];

        $this->fPrice = new SimplePrice(null, getDBConfigValue($this->sMarketplace.'.currency', $this->sMpId));

        $this->aConfig['keytype'] = getDBConfigValue('general.keytype', '0');

        $this->aConfig['lang'] = getDBConfigValue($this->sMarketplace.'.shop.language', $this->sMpId, $_SESSION['languages_id']);
        $this->aConfig['hasShortDesc'] = MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION);

        $this->aConfig['imagepath'] = rtrim(getDBConfigValue($this->sMarketplace.'.imagepath', $this->sMpId), '/').'/';
    }

    protected function insertPrepareData($aData) {
        MagnaDB::gi()->insert(TABLE_MAGNA_GOOGLESHOPPING_PREPARE, $aData, true);
    }

    protected function getManufacturerPartNumber(&$row) {
        $mfrmd = getDBConfigValue($this->marketplace.'.checkin.manufacturerpartnumber.table', $this->mpId, false);
        if (is_array($mfrmd) && !empty($mfrmd['column']) && !empty($mfrmd['table'])) {
            $pIDAlias = getDBConfigValue($this->marketplace.'.checkin.manufacturerpartnumber.alias', $this->mpId);
            if (empty($pIDAlias)) {
                $pIDAlias = 'products_id';
            }
            $row['ManufacturerPartNumber'] = MagnaDB::gi()->fetchOne('
				SELECT `'.$mfrmd['column'].'`
				  FROM `'.$mfrmd['table'].'`
				 WHERE `'.$pIDAlias.'`="'.MagnaDB::gi()->escape($row['products_id']).'"
				 LIMIT 1
			');
        }
    }


    protected function preparePropertiesRow($iProductId, $aItemDetails) {

        $aRow = array();
        $lang = getDBConfigValue($this->sMarketplace.'.lang', $this->mpId);
        $prod = MLProduct::gi()->setLanguage($lang)->getProductById($iProductId);

        if (isset($aItemDetails['Title']) === false) {
            $aItemDetails['Title'] = $prod['Title'];
            $aItemDetails['Description'] = $prod['Description'];
            $aItemDetails['Price'] = $prod['Price'];
        }
        
        if (!isset($aItemDetails['PrimaryCategory']) || $aItemDetails['PrimaryCategory'] === '') {
            $this->aErrors['ML_GOOGLESHOPPING_ERROR_CATEGORY'] = ML_GOOGLESHOPPING_ERROR_CATEGORY;
        } else {
            $aRow['PrimaryCategory'] = $aItemDetails['PrimaryCategory'];
        }

        if (!isset($aItemDetails['Images'])
            || empty($aItemDetails['Images'])  ) {
            $aRow['Image'] = '';
        } else {
            $aRow['Image'] = array();
            foreach ($aItemDetails['Images'] as $name => $checked) {
                if ($checked === 'true') $aRow['Image'][] = $name;
            }
        }
        unset($aItemDetails['Images'][0]);
        $aRow['mpID'] = $this->mpId;
        $aRow['products_id'] = $iProductId;
        $aRow['products_model'] = $prod['ProductsModel'];
        $aRow['Verified'] = 'OK';
        $aRow['title'] = $aItemDetails['Title'];
        $aRow['description'] = ($aItemDetails['Description'] !== null) ? $aItemDetails['Description'] : '';
        $aRow['contentLanguage'] = getDBConfigValue($this->sMarketplace.'.lang.match.googleshopping', $this->mpId);
        $aRow['targetCountry'] = getDBConfigValue($this->sMarketplace.'.targetCountry', $this->mpId);
        $aRow['Price'] = $aItemDetails['Price'];
        $aRow['currency'] = getDBConfigValue($this->sMarketplace.'.currency', $this->mpId);
        $aRow['PrepareType'] = !empty($aItemDetails['PrepareType']) ? $aItemDetails['PrepareType'] : 'apply';
        $aRow['CustomAttributes'] = $aItemDetails['CategoryAttributes'];
        $aRow['brand'] = !empty($prod['Manufacturer']) ? $prod['Manufacturer'] : '';
        $aRow['condition'] = GoogleshoppingHelper::GetConditionTypes()[$aItemDetails['condition_id']];
        $aRow['channel'] = !empty($aItemDetails['Channel']) ? $aItemDetails['Channel'] : 'online';
        $aRow['Image'] = json_encode(!empty($aRow['Image']) ? $aRow['Image'] : array());
        $aRow['availability'] = $this->getAvailability($iProductId, $aItemDetails);
        $aRow['adult'] = $prod['IsFSK18'];

        if (!empty($this->aErrors)) {
            $aRow['Verified'] = 'ERROR';
        }

        return $aRow;
    }

    public function saveSingleProductProperties($iProductId, $aItemDetails, $prepareType) {
        $this->saveMultipleProductProperties(array($iProductId), $aItemDetails, $prepareType);
    }

    public function saveMultipleProductProperties($iProductIds, $aItemDetails, $prepareType) {
        if ($prepareType === 'match') {
            $this->insertMatchProduct($aItemDetails);
            return;
        }

        $preparedTs = date('Y-m-d H:i:s');
        foreach ($iProductIds as $iProductId) {
            $aRow = $this->preparePropertiesRow($iProductId, $aItemDetails);
            $aRow['PreparedTs'] = $preparedTs;
            if (count($iProductIds) > 1) {
                $aRow['Image'] = json_encode(MLProduct::gi()->setLanguage(1)->getProductById($iProductId)['Images']);
            }
            $this->insertPrepareData($aRow);
        }
    }

    private function insertMatchProduct($itemDetails) {
        foreach ($itemDetails['model'] as $pId => $productModel) {
            $productId = $itemDetails['match'][$pId];

            if ($productId === 'false') {
                continue;
            }

            $matchedProduct = array(
                'mpID' => $this->mpId,
                'products_id' => $pId,
                'products_model' => $productModel,
                'Title' => $itemDetails['matching'][$pId]['title'],
                'EAN' => $itemDetails['matching'][$pId]['ean'],
                'ConditionType' => $itemDetails['unit']['condition_id'],
                'ShippingTime' => $itemDetails['unit']['shippingtime'],
                'Location' => $itemDetails['unit']['deliverycountry'],
                'Comment' => $itemDetails['unit']['comment'],
                'PrepareType' => 'Match',
                'Verified' => 'OK',
                'PreparedTs' => date('Y-m-d H:i:s'),
            );

            MagnaDB::gi()->insert(TABLE_MAGNA_GOOGLESHOPPING_PREPARE, $matchedProduct, true);
        }
    }

    private function getAvailability($iProductId, $aItemDetails) {
        if ($this->isPreorder($aItemDetails)) {
            return 'preorder';
        }

        return $this->isInStock($iProductId) ? 'in stock' : 'out of stock';
    }

    private function isPreorder($aItemDetails) {
        if (!array_key_exists('Preorder', $aItemDetails)) {
            return false;
        }

        return 0 === stripos('on', $aItemDetails['Preorder']);
    }

    private function isInStock($iProductId) {
        $query = sprintf('SELECT products_quantity FROM %s WHERE products_id=%d', TABLE_PRODUCTS, $iProductId);
        $quantity = (int)MagnaDB::gi()->fetchOne($query);

        return $quantity > 0;
    }
}
