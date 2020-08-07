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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/AttributesMatchingHelper.php');

class GoogleshoppingHelper extends AttributesMatchingHelper {

    protected $numberOfMaxAdditionalAttributes = self::UNLIMITED_ADDITIONAL_ATTRIBUTES;

    private static $instance;
    protected $marketplaceTitle = 'Google Shopping';

    public static function gi() {
        if (self::$instance === null) {
            self::$instance = new GoogleshoppingHelper();
        }

        return self::$instance;
    }

    public static function checkProductSaveJsonArray($aCheckArray) {
        foreach ($aCheckArray as $sKey => &$sEntry) {
            if (empty($sEntry)) {
                unset($aCheckArray[$sKey]);
            }
        }

        if (0 < count($aCheckArray)) {
            return json_encode($aCheckArray);
        } else {
            return '';
        }
    }

    public static function GetConditionTypes() {
        global $_MagnaSession;

        $mpID = $_MagnaSession['mpID'];

        $types['values'] = array();

        if (isset($_MagnaSession[$mpID]['ConditionTypes'])
            && !empty($_MagnaSession[$mpID]['ConditionTypes'])
        ) {
            return $_MagnaSession[$mpID]['ConditionTypes'];
        }

        $typesData['DATA'] = array(
            'new',
            'refurbished',
            'used',
        );
        $_MagnaSession[$mpID]['ConditionTypes'] = $typesData['DATA'];
        return $typesData['DATA'];
    }

    protected function isProductPrepared($category, $prepare = false) {
        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sKeyType = 'products_model';
        } else {
            $sKeyType = 'products_id';
        }

        return MagnaDB::gi()->recordExists(TABLE_MAGNA_GOOGLESHOPPING_PREPARE, array(
            'mpID' => $this->mpId,
            $sKeyType => $prepare,
        ));
    }

    protected function getPreparedData($category, $prepare = false, $customIdentifier = '') {
        if (!$prepare) {
            return array();
        }

        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sSQLAnd = ' AND products_model = "'.$prepare.'"';
        } else {
            $sSQLAnd = ' AND products_id = "'.$prepare.'"';
        }

        $availableCustomConfigs = json_decode(MagnaDB::gi()->fetchOne(eecho('
            SELECT CustomAttributes
            FROM '.TABLE_MAGNA_GOOGLESHOPPING_PREPARE.'
            WHERE mpID = '.$this->mpId.'
                '.$sSQLAnd.'
        ', false)), true);

        return $availableCustomConfigs;
    }

    /**
     * Gets prepared attributes data for products prepared for given category.
     *
     * @param string $category
     * @return array|null
     */
    protected function getPreparedProductsData($category) {
        $dataFromDB = MagnaDB::gi()->fetchArray(eecho('
				SELECT `CategoryAttributes`
				FROM '.TABLE_MAGNA_GOOGLESHOPPING_PREPARE.'
				WHERE mpID = '.$this->mpId.'
					AND MarketplaceCategories = "'.$category.'"
			', false), true);

        if ($dataFromDB) {
            $result = array();
            foreach ($dataFromDB as $preparedData) {
                if ($preparedData) {
                    $result[] = json_decode($preparedData, true);
                }
            }

            return $result;
        }

        return null;
    }

    protected function getAttributesFromMP($category, $additionalData = null, $customIdentifier = '') {
        $targetCountry = !empty($additionalData['targetCountry']) ? $additionalData['targetCountry'] : getDBConfigValue($this->marketplace.'.targetCountry', $this->mpId);
        $request = array(
            'ACTION' => 'GetCategoryDetails',
            'SUBSYSTEM' => $this->marketplace,
            'MARKETPLACEID' => $this->mpId,
            'DATA' => array(
                'categoryId' => $category,
                'targetCountry' => $targetCountry,
            )
        );

        try {
            $data = MagnaConnector::gi()->submitRequest($request);
            return $data['DATA'];
        } catch (\Exception $e) {
            return array();
        }
    }

    protected function fixHTMLUTF8Entities($code) {
        return $code;
    }

    protected function setMatchingTableTranslations() {
        return array(
            'mpTitle' => str_replace('%marketplace%', $this->marketplaceTitle, ML_GENERAL_VARMATCH_TITLE),
            'mpAttributeTitle' => str_replace('%marketplace%', $this->marketplaceTitle, ML_GENERAL_VARMATCH_MP_ATTRIBUTE),
            'mpOptionalAttributeTitle' => str_replace('%marketplace%', $this->marketplaceTitle, ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE),
            'mpCustomAttributeTitle' => str_replace('%marketplace%', $this->marketplaceTitle, ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE),
        );
    }
}
