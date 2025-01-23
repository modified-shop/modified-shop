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
 * (c) 2010 - 2023 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/AttributesMatchingHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'otto/classes/OttoApiConfigValues.php');
require_once(DIR_MAGNALISTER_MODULES.'otto/prepare/OttoIndependentAttributes.php');

class OttoHelper extends AttributesMatchingHelper {

    private static $instance;

    /**
     * @var string
     */
    protected $marketplaceTitle = 'Otto';

    /**
     * @var int
     */
    protected $numberOfMaxAdditionalAttributes = self::UNLIMITED_ADDITIONAL_ATTRIBUTES;

    public static function gi() {
        if (self::$instance === null) {
            self::$instance = new OttoHelper();
        }

        return self::$instance;
    }

    public static function loadPriceSettings($mpId) {
        $mp = magnaGetMarketplaceByID($mpId);

        $config = array(
            'Fixed' => array(
                'AddKind' => getDBConfigValue($mp.'.price.addkind', $mpId, 'percent'),
                'Factor' => (float)getDBConfigValue($mp.'.price.factor', $mpId, 0),
                'Signal' => getDBConfigValue($mp.'.price.signal', $mpId, ''),
                'Group' => getDBConfigValue($mp.'.price.group', $mpId, ''),
                'UseSpecialOffer' => getDBConfigValue(array($mp.'.price.usespecialoffer', 'val'), $mpId, false),
            ),
        );
        return $config;
    }

    public static function loadQuantitySettings($mpId) {
        $mp = magnaGetMarketplaceByID($mpId);

        $config = array(
            'Type' => getDBConfigValue($mp.'.quantity.type', $mpId, 'lump'),
            'Value' => (int)getDBConfigValue($mp.'.quantity.value', $mpId, 0),
            'MaxQuantity' => (int)getDBConfigValue($mp.'.maxquantity', $mpId, 0),
        );

        return $config;
    }

    public static function getQuantityForOtto($iProductsQuantity, $mpID) {
        $sCalcMethod = getDBConfigValue('otto.quantity.type', $mpID);
        $iQuantityValue = getDBConfigValue('otto.quantity.value', $mpID);
        $iMaxQuantity = getDBConfigValue('otto.maxquantity', $mpID, 0);
        switch ($sCalcMethod) {
            case ('stocksub'):
                {
                    $iQuantity = (int)($iProductsQuantity - $iQuantityValue);
                    break;
                }
            case ('lump'):
                {
                    // here, maxquantity is not relevant
                    return (int)$iQuantityValue;
                    break;
                }
            case ('stock'):
            default:
                {
                    $iQuantity = (int)$iProductsQuantity;
                    break;
                }
        }
        if (empty($iMaxQuantity)) return $iQuantity;
        else return min($iQuantity, $iMaxQuantity);
    }

    public function getVarMatchTranslations() {
        $translations = parent::getVarMatchTranslations();
        $translations['mpValue'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_MP_VALUE);
        $translations['attributeChangedOnMp'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_ATTRIBUTE_CHANGED_ON_MP);
        $translations['attributeDeletedOnMp'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_ATTRIBUTE_DELETED_ON_MP);
        $translations['attributeValueDeletedOnMp'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_ATTRIBUTE_VALUE_DELETED_ON_MP);;
        $translations['categoryWithoutAttributesInfo'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_CATEGORY_WITHOUT_ATTRIBUTES_INFO);
        $translations['matchingTable'] = ML_OTTO_LABEL_MATCHED_VALUES;
        return $translations;
    }

    /**
     * @param string $category
     * @param bool $prepare
     * @param bool $getDate Set to <b>TRUE</b> if modification date should be returned
     * @param mixed $additionalData Use this parameter for additional handling if needed.
     * @return array
     *
     * copied from parent class, the only difference is we don't use fixHTMLUTF8Entities for utf8Code
     * (it broke the javascript functionality)
     */
    public function getMPVariations($category, $prepare = false, $getDate = false, $additionalData = null, $customIdentifier = '') {
        $mpData = $this->getAttributesFromMP($category, $additionalData, $customIdentifier);
        $dbData = $this->getPreparedData($category, $prepare, $customIdentifier, 'ShopVariation');
        $tableName = $this->getVariationMatchingTableName();
        $shopAttributes = $this->flatShopVariations();

        // load default values from Attributes Matching tab (global matching)
        $usedGlobal = false;
        $globalMatching = $this->getCategoryMatching($category, $customIdentifier);

        if (!$this->isProductPrepared($category, $prepare)) {
            $dbData = $globalMatching;
            $usedGlobal = true;
        }

        arrayEntitiesToUTF8($mpData);
        $attributes = array();
        foreach ($mpData['attributes'] as $code => $value) {
            $utf8Code = $code;
            $attributes[$utf8Code] = array(
                'AttributeCode' => $utf8Code,
                'AttributeName' => html_entity_decode($value['title']),
                'AllowedValues' => isset($value['values']) ? $value['values'] : array(),
                'AttributeDescription' => isset($value['desc']) ? html_entity_decode($value['desc']) : '',
                'CurrentValues' => isset($dbData[$utf8Code]) ? $dbData[$utf8Code] : array(),
                'ChangeDate' => isset($value['changed']) ? $value['changed'] : false,
                'Required' => isset($value['mandatory']) ? $value['mandatory'] : false,
                'DataType' => isset($value['type']) ? $value['type'] : 'text',
            );

            if (isset($value['limit'])) {
                $attributes[$utf8Code]['Limit'] = $value['limit'];
            }

            if (isset($dbData[$utf8Code])) {
                if (!isset($dbData[$utf8Code]['Required'])) {
                    $dbData[$utf8Code]['Required'] = isset($value['mandatory']) ? $value['mandatory'] : true;
                    $dbData[$utf8Code]['Code'] = !empty($value['values']) ? 'attribute_value' : 'freetext';
                    $dbData[$utf8Code]['AttributeName'] = $value['title'];
                }

                $attributes[$utf8Code]['CurrentValues'] = $dbData[$utf8Code];
            }
        }

        if ($this->getNumberOfMaxAdditionalAttributes() > 0) {
            $this->addAdditionalAttributesMP($attributes, $dbData);
        }

        $hasDifferentlyPreparedProducts = false;
        if (!$usedGlobal && !empty($globalMatching)) {
            $this->detectChanges($globalMatching, $attributes);
        } else if (!$prepare && !empty($globalMatching)) {
            // on variation matching tab. Check whether some products are prepared differently
            $hasDifferentlyPreparedProducts = $this->areProductsDifferentlyPrepared($category, $globalMatching, $customIdentifier);
        }

        arrayEntitiesToUTF8($dbData);

        // If there are saved values but they were removed either from Marketplace or Shop, display warning to user.
        if (is_array($dbData)) {
            foreach ($dbData as $utf8Code => $value) {
                $isAdditionalAttribute = strpos($utf8Code, 'additional_attribute_') !== false;
                if (!isset($attributes[$utf8Code]) && !$isAdditionalAttribute) {
                    $attributes[$utf8Code] = array(
                        'Deleted' => true,
                        'AttributeCode' => $utf8Code,
                        'AttributeName' => !empty($value['AttributeName']) ? $value['AttributeName'] : $utf8Code,
                        'AllowedValues' => array(),
                        'AttributeDescription' => '',
                        'CurrentValues' => array('Values' => array()),
                        'ChangeDate' => '',
                        'Required' => isset($value['mandatory']) ? $value['mandatory'] : false,
                        'DataType' => 'text',
                    );
                } else {
                    if ($isAdditionalAttribute && $this->getNumberOfMaxAdditionalAttributes() <= 0) {
                        continue;
                    }

                    $attributes[$utf8Code]['WarningMessage'] = '';
                    $attributes[$utf8Code]['IsDeletedOnShop'] = $this->detectIfAttributeIsDeletedOnShop($shopAttributes, $value, $attributes[$utf8Code]['WarningMessage']);
                }
            }
        }

        if ($getDate) {
            $modificationDate = MagnaDB::gi()->fetchOne(eecho('
					SELECT ModificationDate
					FROM '.$tableName.'
					WHERE MpId = '.$this->mpId.'
						AND MpIdentifier = "'.MagnaDB::gi()->escape($category).'"
						AND CustomIdentifier = "'.MagnaDB::gi()->escape($customIdentifier).'"
				', false));

            $variationThemeData = array();
            if (!empty($mpData['variation_details'])) {
                $variationThemeData['variation_details'] = $mpData['variation_details'];
                $variationThemeData['variation_theme_code'] = $this->getSavedVariationThemeCode($category, $prepare);
            }

            if (!empty($mpData['variation_details_blacklist'])) {
                $variationThemeData['variation_details_blacklist'] = $mpData['variation_details_blacklist'];
            }

            $result = array_merge(
                array(
                    'Attributes' => $attributes,
                    'ModificationDate' => $modificationDate,
                    'DifferentProducts' => $hasDifferentlyPreparedProducts,
                ), $variationThemeData
            );
            // encode to utf8 for json encode
            arrayEntitiesToUTF8($result);

            return $result;
        }

        arrayEntitiesToUTF8($attributes);
        return $attributes;
    }

    public function getAttributesFromMP($category, $additionalData = null, $customIdentifier = '') {
#echo print_m(func_get_args(), __METHOD__);

        $data = OttoApiConfigValues::gi()->getVariantConfigurationDefinition($category, null);
#echo print_m($data, __LINE__.' '.__METHOD__);

        if (!is_array($data) || !isset($data['attributes'])) {
            $data = array();
        }

        if (empty($data['attributes'])) {
            $data['attributes'] = array();
        }

        return $data;
    }

    protected function getPreparedIndependentData($prepare = false, $customIdentifier = '', $dbField = 'NULL') {
        $availableCustomConfigs = array();

        if ($_GET['where'] == 'varmatchView') {
            $availableCustomConfigs = json_decode(MagnaDB::gi()->fetchOne(eecho('
                    SELECT ShopVariation
                      FROM '.TABLE_MAGNA_OTTO_VARIANTMATCHING.'
                     WHERE     MpId = '.$this->mpId.'
                     AND MpIdentifier = "category_independent_attributes"
                ', false), true), true);

            return !$availableCustomConfigs ? array() : $availableCustomConfigs;
        }

        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sSQLAnd = ' AND products_model = "'.$prepare.'"';
        } else {
            $sSQLAnd = ' AND products_id = "'.$prepare.'"';
        }

        if ($prepare) {
            $availableCustomConfigs = json_decode(MagnaDB::gi()->fetchOne(eecho('
                SELECT '.$dbField.'
                  FROM '.TABLE_MAGNA_OTTO_PREPARE.'
                 WHERE     MpId = '.$this->mpId.'
                      '.$sSQLAnd.'
            ', false), true), true);
        }

        return !$availableCustomConfigs ? array() : $availableCustomConfigs;
    }

    protected function getPreparedData($category, $prepare = false, $customIdentifier = '', $dbField = 'ShopVariation') {
        $availableCustomConfigs = array();

        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sSQLAnd = ' AND products_model = "'.$prepare.'"';
        } else {
            $sSQLAnd = ' AND products_id = "'.$prepare.'"';
        }

        if ($prepare) {
            $availableCustomConfigs = json_decode(MagnaDB::gi()->fetchOne(eecho('
                SELECT '.$dbField.'
                  FROM '.TABLE_MAGNA_OTTO_PREPARE.'
                 WHERE     MpId = '.$this->mpId.'
                       AND PrimaryCategory = "'.MagnaDB::gi()->escape($category).'"
                      '.$sSQLAnd.'
            ', false), true), true);
        }

        return !$availableCustomConfigs ? array() : $availableCustomConfigs;
    }

    /* brauchma ned (checken ob verwendet) */

    protected function isProductPrepared($category, $prepare = false) {
        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sKeyType = 'products_model';
        } else {
            $sKeyType = 'products_id';
        }

        return MagnaDB::gi()->recordExists(TABLE_MAGNA_OTTO_PREPARE, array(
            'MpId' => $this->mpId,
            $sKeyType => $prepare,
            'PrimaryCategory' => $category,
        ));
    }

    protected function isProductPreparedForIndependent($prepare = false) {
        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sKeyType = 'products_model';
        } else {
            $sKeyType = 'products_id';
        }

        return MagnaDB::gi()->recordExists(TABLE_MAGNA_OTTO_PREPARE, array(
            'MpId' => $this->mpId,
            $sKeyType => $prepare
        ));
    }

    /**
     * Gets prepared attributes data for products prepared for given category.
     *
     * @param string $category
     * @param string $customIdentifier
     * @return array|null
     */
    protected function getPreparedProductsData($category, $customIdentifier = '') {
        $dataFromDB = MagnaDB::gi()->fetchArray(eecho('
            SELECT `ShopVariation`
              FROM '.TABLE_MAGNA_OTTO_PREPARE.'
             WHERE     mpID = '.$this->mpId.'
                   AND PrimaryCategory = "'.MagnaDB::gi()->escape($category).'"
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

    protected function setMatchingTableTranslations() {
        return array(
            'mpTitle' => str_replace('%marketplace%', strtoupper($this->marketplaceTitle), ML_GENERAL_VARMATCH_TITLE),
            'mpAttributeTitle' => str_replace('%marketplace%', strtoupper($this->marketplaceTitle), ML_GENERAL_VARMATCH_MP_ATTRIBUTE),
            'mpOptionalAttributeTitle' => str_replace('%marketplace%', strtoupper($this->marketplaceTitle), ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE),
            'mpCustomAttributeTitle' => str_replace('%marketplace%', strtoupper($this->marketplaceTitle), ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE),
        );
    }

    /**
     * Loading Category Independent Attributes
     *
     * @param 
     * @return array
     */
    public function getCategoryIndependentAttributes($independentAttributes, $category, $prepare = false, $getDate = false, $customIdentifier = '') {
        $mpData = $independentAttributes;
        $modificationDate = '';
        $dbData = $this->getPreparedIndependentData($prepare, $customIdentifier, 'CategoryIndependentShopVariation');

        $tableName = $this->getVariationMatchingTableName();
        $shopAttributes = $this->flatShopVariations();
   
        $usedGlobal = false;
        
        $globalMatching = $this->getCategoryIndependentMatching();
        if (!$this->isProductPreparedForIndependent($prepare)) {
            $dbData = $globalMatching;
            $usedGlobal = true;
        }

        arrayEntitiesToUTF8($mpData);
        $attributes = array();
        foreach ($mpData['attributes'] as $code => $value) {
            $utf8Code = $this->fixHTMLUTF8Entities($value['name']);
            #$utf8Code = $code;
            
            $currentValues = array();
            if (isset($dbData[$utf8Code])) {
                $currentValues = $dbData[$utf8Code];
            } else if (!isset($dbData[$utf8Code]) && isset($value['mandatory']) && $value['mandatory']) {
                $currentValues = $this->setDefaultFiledsOnIndependentAttributes($utf8Code);
            }

            $attributes[$utf8Code] = array(
                'AttributeCode' => $utf8Code,
                'AttributeName' => html_entity_decode($value['title']),
                'AllowedValues' => isset($value['values']) ? $value['values'] : array(),
                'AttributeDescription' => isset($value['desc']) ? html_entity_decode($value['desc']) : '',
                'CurrentValues' => $currentValues,
                'ChangeDate' => isset($value['changed']) ? $value['changed'] : false,
                'Required' => isset($value['mandatory']) ? $value['mandatory'] : false,
                'DataType' => isset($value['type']) ? $value['type'] : 'text',
            );

            if (isset($value['limit'])) {
                $attributes[$utf8Code]['Limit'] = $value['limit'];
            }

            if (isset($dbData[$utf8Code])) {
                if (!isset($dbData[$utf8Code]['Required'])) {
                    $dbData[$utf8Code]['Required'] = isset($value['mandatory']) ? $value['mandatory'] : true;
                    $dbData[$utf8Code]['Code'] = !empty($value['values']) ? 'attribute_value' : 'freetext';
                    $dbData[$utf8Code]['AttributeName'] = $value['title'];
                }

                $attributes[$utf8Code]['CurrentValues'] = $dbData[$utf8Code];
            }
        }

        if ($this->getNumberOfMaxAdditionalAttributes() > 0) {
            $this->addAdditionalAttributesMP($attributes, $dbData);
        }

        $hasDifferentlyPreparedProducts = false;
        if (!$usedGlobal && !empty($globalMatching)) {
            $this->detectChanges($globalMatching, $attributes);
        } else if (!$prepare && !empty($globalMatching)) {
            // on variation matching tab. Check whether some products are prepared differently
            $hasDifferentlyPreparedProducts = $this->areProductsDifferentlyPrepared($category, $globalMatching, $customIdentifier);
        }

        arrayEntitiesToUTF8($dbData);

        // If there are saved values but they were removed either from Marketplace or Shop, display warning to user.
        if (is_array($dbData)) {
            foreach ($dbData as $utf8Code => $value) {
                $utf8Code = $this->fixHTMLUTF8Entities($utf8Code);
                $isAdditionalAttribute = strpos($utf8Code, 'additional_attribute_') !== false;
                if (!isset($attributes[$utf8Code]) && !$isAdditionalAttribute) {
                    $attributes[$utf8Code] = array(
                        'Deleted' => true,
                        'AttributeCode' => $utf8Code,
                        'AttributeName' => !empty($value['AttributeName']) ? html_entity_decode($value['AttributeName']) : $utf8Code,
                        'AllowedValues' => array(),
                        'AttributeDescription' => '',
                        'CurrentValues' => array('Values' => array()),
                        'ChangeDate' => '',
                        'Required' => isset($value['mandatory']) ? $value['mandatory'] : false,
                        'DataType' => 'text',
                    );
                } else {
                    if ($isAdditionalAttribute && $this->getNumberOfMaxAdditionalAttributes() <= 0) {
                        continue;
                    }

                    $attributes[$utf8Code]['WarningMessage'] = '';
                    $attributes[$utf8Code]['IsDeletedOnShop'] = $this->detectIfAttributeIsDeletedOnShop($shopAttributes, $value, $attributes[$utf8Code]['WarningMessage']);
                }
            }
        }

        if ($getDate) {
            $modificationDate = MagnaDB::gi()->fetchOne(eecho('
                    SELECT ModificationDate
                    FROM '.$tableName.'
                    WHERE MpId = '.$this->mpId.'
                        AND MpIdentifier = "'.MagnaDB::gi()->escape($category).'"
                        AND CustomIdentifier = "'.MagnaDB::gi()->escape($customIdentifier).'"
                ', false));

            $variationThemeData = array();
            if (!empty($mpData['variation_details'])) {
                $variationThemeData['variation_details'] = $mpData['variation_details'];
                $variationThemeData['variation_theme_code'] = $this->getSavedVariationThemeCode($category, $prepare);
            }

            if (!empty($mpData['variation_details_blacklist'])) {
                $variationThemeData['variation_details_blacklist'] = $mpData['variation_details_blacklist'];
            }

            return array_merge(
                array(
                    'Attributes' => $attributes,
                    'ModificationDate' => $modificationDate,
                    'DifferentProducts' => $hasDifferentlyPreparedProducts,
                    'MarketplaceId' => $this->mpId,
                ), $variationThemeData
            );
        }

        return array(
            'Attributes' => $attributes,
            'ModificationDate' => $modificationDate,
            'DifferentProducts' => $hasDifferentlyPreparedProducts,
            'MarketplaceId' => $this->mpId,
        );
    }

    public function getCategoryIndependentMatching() {
        $tableName = $this->getVariationMatchingTableName();

        $matching = json_decode(MagnaDB::gi()->fetchOne(eecho('
                SELECT ShopVariation
                FROM ' . $tableName . '
                WHERE MpId = ' . $this->mpId . '
                    AND MpIdentifier = "category_independent_attributes"
            ', false)), true);

        return $matching ? $matching : array();
    }

    public function saveIndependentMatching($category, &$matching, $savePrepare, $fromPrepare, $validateCustomAttributesNumber, $variationThemeAttributes = null, $sCustomIdentifier = '') {
        if (!$matching) {
            return array();
        }

        $attributeCodeKey = isset($_REQUEST['AttributeCodeKey']) ? $_REQUEST['AttributeCodeKey'] : '';
        $tableName = $this->getVariationMatchingTableName();
        $errors = array();
        $addNotAllValuesMatchedNotice = false;
        $previouslyMatchedAttributes = array();
        $emptyCustomName = false;
        $maxNumberOfAdditionalAttributes = $this->getNumberOfMaxAdditionalAttributes();
        $numberOfMatchedAdditionalAttributes = 0;
        $variationThemeExists = is_array($variationThemeAttributes);

        foreach ($matching['CategoryIndependentShopVariation'] as $key => &$value) {
            if (isset($value['Required'])) {
                $value['Required'] = (bool) $value['Required'];
            } else {
                $value['Required'] = false;
            }

            $value['Error'] = false;
            $isSelectedAttribute = $key === $attributeCodeKey;

            $this->transformMatching($value);
            $this->validateCustomAttributes($key, $value, $previouslyMatchedAttributes, $errors, $emptyCustomName, $savePrepare, $isSelectedAttribute, $numberOfMatchedAdditionalAttributes);
            $sAttributeName = $value['AttributeName'];
            $isVariationThemeAttribute = $variationThemeExists && in_array($key, $variationThemeAttributes);

            if (in_array($value['Code'], array('null', 0), true)  || !isset($value['Values']) || in_array($value['Values'], array( '', null), true)) {
                if ((isset($value['Required']) && $value['Required'] == true) || $isVariationThemeAttribute) {

                    if ($savePrepare || $isSelectedAttribute) {
                        if ($savePrepare) {
                            $errors[] = str_replace('%attribute_name%', $sAttributeName, ML_GENERAL_VARMATCH_ERROR_MESSAGE_REQUIRED);
                        }
                        $value['Error'] = true;
                        unset($value['Values']);
                    }
                }

                // $key should be unset whenever item does not have any errors and condition
                //(isset($value['Required']) && $value['Required'] && $savePrepare) is not true. That way only required data
                // or data with errors will be saved to DB.
                if ((!isset($value['Required']) || !$value['Required'] || !$savePrepare) && empty($value['Error'])) {
                    unset($matching['CategoryIndependentShopVariation'][$key]);
                }

                continue;
            }

            if (!is_array($value['Values']) || !isset($value['Values']['FreeText'])) {
                continue;
            }

            $sInfo = ML_GENERAL_VARMATCH_MANUALY_MATCHED;
            $sFreeText = $value['Values']['FreeText'];
            unset($value['Values']['FreeText']);

            if ($value['Values']['0']['Shop']['Key'] === 'null' || $value['Values']['0']['Marketplace']['Key'] === 'null') {
                unset($value['Values']['0']);
                if ((empty($value['Values']) && ($value['Required'] == true || $isVariationThemeAttribute)) &&
                        ($savePrepare || $isSelectedAttribute)) {

                    $value['Error'] = true;
                    if ($savePrepare) {
                        $errors[] = str_replace('%attribute_name%', $sAttributeName, ML_GENERAL_VARMATCH_ERROR_MESSAGE_REQUIRED);
                    }
                }

                foreach ($value['Values'] as $k => &$v) {
                    if (empty($v['Marketplace']['Info']) || $v['Marketplace']['Key'] === 'manual') {
                        $v['Marketplace']['Info'] = $v['Marketplace']['Value'] . ML_GENERAL_VARMATCH_FREE_TEXT;
                    }
                }

                continue;
            }

            if ($value['Values']['0']['Marketplace']['Key'] === 'reset') {
                unset($matching['CategoryIndependentShopVariation'][$key]);
                continue;
            }

            if ($value['Values']['0']['Marketplace']['Key'] === 'manual') {
                $sInfo = ML_GENERAL_VARMATCH_FREE_TEXT;
                if (empty($sFreeText) || !isset($sFreeText)) {
                    if ($savePrepare || $isSelectedAttribute) {
                        if ($savePrepare) {
                            $errors[] = $sAttributeName . ML_GENERAL_VARMATCH_ERROR_MESSAGE_FREE_TEXT;
                        }
                        $value['Error'] = true;
                    }

                    unset($value['Values']['0']);
                    continue;
                }

                $value['Values']['0']['Marketplace']['Value'] = $sFreeText;
            }

            if ($value['Values']['0']['Marketplace']['Key'] === 'auto') {
                $allValuesAreMatched = $this->autoMatchIndependent($category, $key, $value);
                if (!$allValuesAreMatched) {
                    $addNotAllValuesMatchedNotice = true;
                }
                continue;
            }

            $this->checkNewMatchedCombination($value['Values']);
            if ($value['Values']['0']['Shop']['Key'] === 'all') {
                $newValue = array();
                $i = 0;
                $mpVariations = $this->flatShopVariations();
                $matchedMpValue = $value['Values']['0']['Marketplace']['Value'];

                foreach ($mpVariations[$value['Code']]['Values'] as $keyAttribute => $valueAttribute) {
                    $newValue[$i]['Shop']['Key'] = $keyAttribute;
                    $newValue[$i]['Shop']['Value'] = $valueAttribute;
                    $newValue[$i]['Marketplace']['Key'] = $value['Values']['0']['Marketplace']['Key'];
                    $newValue[$i]['Marketplace']['Value'] = $matchedMpValue;
                    // $matchedMpValue can be array if it is multi value, so that`s why this is checked and converted to
                    // string if it is. That is done because this information will be displayed in matched table.
                    $newValue[$i]['Marketplace']['Info'] = (is_array($matchedMpValue) ? implode(', ', $matchedMpValue) : $matchedMpValue) . $sInfo;
                    $i++;
                }

                $value['Values'] = $newValue;
            } else {
                foreach ($value['Values'] as $k => &$v) {
                    if (empty($v['Marketplace']['Info'])) {
                        // $v['Marketplace']['Value'] can be array if it is multi value, so that`s why this is checked
                        // and converted to string if it is. That is done because this information will be displayed in matched
                        // table.
                        $v['Marketplace']['Info'] = (is_array($v['Marketplace']['Value']) ?
                                implode(', ', $v['Marketplace']['Value']) : $v['Marketplace']['Value']) . $sInfo;
                    }
                }
            }
        }

        if ($fromPrepare) {

            if ($validateCustomAttributesNumber && ($numberOfMatchedAdditionalAttributes > $maxNumberOfAdditionalAttributes)) {
                $errors[] = str_replace('%number_of_attributes%', $maxNumberOfAdditionalAttributes, ML_GENERAL_VARMATCH_MAX_NUMBER_OF_ADDITIONAL_ATTRIBUTES_EXCEEDED);
            }
            $this->checkNumberOfVariationValues($matching['CategoryIndependentShopVariation']);

            // If variation theme is defined for that category and mandatory but nothing is selected.
            if ($variationThemeAttributes === 'null') {
                $errors[] = ML_GENERAL_VARMATCH_CHOOSE_VARIATION_THEME;
            }
        }

        arrayEntitiesToUTF8($matching['CategoryIndependentShopVariation']);

        if (!$fromPrepare || !MagnaDB::gi()->recordExists($tableName, array('MpIdentifier' => $category, 'CustomIdentifier' => $sCustomIdentifier)) && $savePrepare) {
            
            MagnaDB::gi()->insert($tableName, array(
                'MpId' => $this->mpId,
                'MpIdentifier' => $category,
                'CustomIdentifier' => $sCustomIdentifier,
                'ShopVariation' => json_encode($matching['ShopVariation']),
                'CategoryIndependentShopVariation' => json_encode($matching['CategoryIndependentShopVariation']),
                'IsValid' => isset($matching['IsValid']) && $matching['IsValid'] === 'false' ? false : true,
                'ModificationDate' => date('Y-m-d H:i:s'),
                    ), true);
        }

        if (!empty($addNotAllValuesMatchedNotice)) {
            array_unshift($errors, array(
                'type' => 'notice',
                'additionalCssClass' => 'notAllAttributeValuesMatched',
                'message' => ML_GENERAL_VARMATCH_NOTICE_NOT_ALL_AUTO_MATCHED,
            ));
        }

        return $errors;
    }

    protected function autoMatchIndependent($categoryId, $sMPAttributeCode, &$aAttributes) {
        $independentAttributesClass = new OttoIndependentAttributes;
        $independentAttributes = $independentAttributesClass->getCategoryIndependentAttributes();
        $mpVariations = $this->getCategoryIndependentAttributes($independentAttributes, $categoryId, false, true);
        $aMPAttributeValues = $mpVariations['Attributes'][$sMPAttributeCode]['AllowedValues'];
        $blAllowFreetext = ($mpVariations[$sMPAttributeCode]['DataType'] == 'multiSelectAndText'
            || $mpVariations[$sMPAttributeCode]['DataType'] == 'selectAndText'
            || $mpVariations[$sMPAttributeCode]['DataType'] == 'text');

        $sVariations = $this->flatShopVariations();
        $sAttributeValues = $sVariations[$aAttributes['Code']]['Values'];

        if (empty($aMPAttributeValues)) {
            foreach ($sAttributeValues as $sShopValue) {
                $aMPAttributeValues[$sShopValue] = $sShopValue;
            }
        }

        // don't overwrite already matched values
        $aAlreadyMatchedValues = array();
        foreach($aAttributes['Values'] as $aValue) {
            $aAlreadyMatchedValues[] = $aValue['Shop']['Value'];
        }

        $sInfo = ML_GENERAL_VARMATCH_AUTO_MATCHED;
        $blFound = false;
        $allValuesAreMatched = true;
        if ($aAttributes['Values']['0']['Shop']['Key'] === 'all') {
            $newValue = array();
            $i = 0;

            foreach ($sAttributeValues as $keyAttribute => $valueAttribute) {
                foreach ($aMPAttributeValues as $key => $value) {
                    if (in_array($valueAttribute, $aAlreadyMatchedValues)) continue;
                    if (strcasecmp(html_entity_decode($valueAttribute), html_entity_decode($value)) == 0) {
                        $newValue[$i]['Shop']['Key'] = $keyAttribute;
                        $newValue[$i]['Shop']['Value'] = $valueAttribute;
                        $newValue[$i]['Marketplace']['Key'] = $key;
                        $newValue[$i]['Marketplace']['Value'] = $value;
                        // $value can be array if it is multi value, so that`s why this is checked
                        // and converted to string if it is. That is done because this information will be displayed in matched
                        // table.
                        $newValue[$i]['Marketplace']['Info'] = (is_array($value) ? implode(', ', $value) : $value) . $sInfo;
                        $blFound = true;
                        $i++;
                        break;
                    }
                }
            }

            if (empty($newValue)) {
            // matching did not succeed. Try to match only the numeric parts
                foreach ($sAttributeValues as $keyAttribute => $valueAttribute) {
                    foreach ($aMPAttributeValues as $key => $value) {
                        if (in_array($valueAttribute, $aAlreadyMatchedValues)) continue;
                        $shopValueInteger = filter_var($valueAttribute, FILTER_SANITIZE_NUMBER_INT);
                        $mpValueInteger = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
                        if (!empty($shopValueInteger) && !empty($mpValueInteger) && $mpValueInteger == $shopValueInteger) {
                            $newValue[$i]['Shop']['Key'] = $keyAttribute;
                            $newValue[$i]['Shop']['Value'] = $valueAttribute;
                            $newValue[$i]['Marketplace']['Key'] = $key;
                            $newValue[$i]['Marketplace']['Value'] = $value;
                            $newValue[$i]['Marketplace']['Info'] = (is_array($value) ? implode(', ', $value) : $value) . $sInfo;
                            $blFound = true;
                            $i++;
                            break;
                        }
                    }
                }
            }

            unset($aAttributes['Values']['0']);
            $aAttributes['Values'] = array_merge($aAttributes['Values'], $newValue);
            if (count($sAttributeValues) !== count($newValue)) {
                $allValuesAreMatched = false;
            }
        } else {
            foreach ($aMPAttributeValues as $key => $value) {
                if (strcasecmp(html_entity_decode($aAttributes['Values']['0']['Shop']['Value']), html_entity_decode($value)) == 0) {
                    $aAttributes['Values']['0']['Marketplace']['Key'] = $key;
                    $aAttributes['Values']['0']['Marketplace']['Value'] = $value;
                    // $value can be array if it is multi value, so that`s why this is checked
                    // and converted to string if it is. That is done because this information will be displayed in matched
                    // table.
                    $aAttributes['Values']['0']['Marketplace']['Info'] = (is_array($value) ? implode(', ', $value) : $value) . $sInfo;
                    $blFound = true;
                    break;
                }
            }

            if (!$blFound && $blAllowFreetext) {
                // single automatching, not found: Set as free text entry
                $aAttributes['Values']['0']['Marketplace']['Key'] = $aAttributes['Values']['0']['Shop']['Value'];
                $aAttributes['Values']['0']['Marketplace']['Value'] = $aAttributes['Values']['0']['Shop']['Value'];
                $aAttributes['Values']['0']['Marketplace']['Info'] = $aAttributes['Values']['0']['Shop']['Value'] . ML_GENERAL_VARMATCH_FREE_TEXT;
                $allValuesAreMatched = false;
                $blFound = true;
            }

            if (!$blFound && !empty($aAttributes['Values'])) {
                foreach ($aAttributes['Values'] as $keyAttribute => $valueAttribute) {
                    if (isset($valueAttribute['Marketplace']['Key']) && $valueAttribute['Marketplace']['Key'] == 'auto') {
                        unset($aAttributes['Values'][$keyAttribute]);
                    }
                }
            }
        }

        $this->checkNewMatchedCombination($aAttributes['Values']);

        return $allValuesAreMatched;
    }

    private function transformMatching(&$matchedAttribute) {
        if (isset($matchedAttribute['Values']) && is_array($matchedAttribute['Values'])) {
            $emptyOptionValue = 'null';
            $multiSelectKey = 'multiselect';

            foreach ($matchedAttribute['Values'] as &$matchedAttributeValue) {
                if (is_array($matchedAttributeValue)) {
                    if (is_array($matchedAttributeValue['Shop']['Key'])) {
                        $matchedAttributeValue['Shop']['Value'] = json_decode($matchedAttributeValue['Shop']['Value'], true);
                    } else if (strtolower($matchedAttributeValue['Shop']['Key']) === $multiSelectKey) {
                        // If multi select is chosen but nothing is selected from multiple select, this value should be ignored.
                        $matchedAttributeValue['Shop']['Key'] = $emptyOptionValue;
                    }

                    if (is_array($matchedAttributeValue['Marketplace']['Key'])) {
                        $matchedAttributeValue['Marketplace']['Value'] = json_decode($matchedAttributeValue['Marketplace']['Value'], true);
                    } else if (strtolower($matchedAttributeValue['Marketplace']['Key']) === $multiSelectKey) {
                        // If multi select is chosen but nothing is selected from multiple select, this value should be ignored.
                        $matchedAttributeValue['Marketplace']['Key'] = $emptyOptionValue;
                    }
                }
            }
        }
    }

    private function setDefaultFiledsOnIndependentAttributes($utf8Code) {
        $array = array(
            'Kind' => "FreeText",
            'Required' => "1",
            'Values' => "true"
        );

        switch ($utf8Code) {
            case 'EAN':
                $array['AttributeName'] = "EAN";
                $array['Code'] = "ean";
                break;

            case 'Brand':
                $array['AttributeName'] = "Brand";
                $array['Code'] = "manufacturer";
                $array['Kind'] = "Matching";
                $array['Values'] = 'undefined';
                break;
            
            default:
                $array['AttributeName'] = "Bullet Points";
                $array['Code'] = "description";
                break;
        }

        return $array;
    }

    public function renderMatchingTable($url, $categoryOptions, $addCategoryPick = true, $displayCategory = true, $customIdentifierHtml = '') {
        $aTitles = $this->setMatchingTableTranslations();
        $displayCategoryClass = $displayCategory ? '' : 'ml-hidden';

        ob_start();
        ?>
        <form method="post" id="matchingForm" action="<?php echo toURL($url, array(), true); ?>">
            <table id="variationMatcher" class="attributesTable">
                <tbody class="<?php echo $displayCategoryClass ?>">
                <tr class="headline">
                    <td colspan="3"><h4><?php echo $aTitles['mpTitle'] ?></h4></td>
                </tr>
                <tr id="mpVariationSelector">
                    <th><?php echo ML_LABEL_MAINCATEGORY ?></th>
                    <td class="input">
                        <table class="inner middle fullwidth categorySelect">
                            <tbody>
                            <tr>
                                <td>
                                    <div class="hoodCatVisual" id="PrimaryCategoryVisual">
                                        <select title="" id="PrimaryCategory" name="PrimaryCategory" style="width:100%">
                                            <?php echo $categoryOptions ?>
                                        </select>
                                    </div>
                                </td>
                                <?php if ($addCategoryPick) { ?>
                                    <td class="buttons">
                                        <input class="fullWidth ml-button smallmargin mlbtn-action" type="button"
                                               value="<?php echo ML_GENERIC_CATEGORIES_CHOOSE ?>" id="selectPrimaryCategory"/>
                                    </td>
                                <?php } ?>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td class="info"></td>
                </tr>
                <?php
                if (!empty($customIdentifierHtml)) {
                    echo $customIdentifierHtml;
                }
                ?>
                <tr class="spacer">
                    <td colspan="3">&nbsp;</td>
                </tr>
                </tbody>
                <tbody id="tbodyDynamicMatchingHeadline" style="display:none;">
                <tr class="headline">
                <tr class="headline">
                    <td class="ottoDarkGreyBackground" colspan="3"><h4><?php echo ML_OTTO_CATEGORY_ATTRIBUTES ?></h4>
                        <p><?php echo ML_OTTO_CATEGORY_ATTRIBUTES_INFO ?></p>
                    </td>
                </tr>
                <tr class="even">
                    <th class="ottoGreyBackground"><h4><?php echo ML_OTTO_CATEGORY_INDEPENDENT_ATTRIBUTES_REQUIRED ?></h4></th>
                    <td class="ottoGreyBackground" colspan="3"><h4><?php echo ML_OTTO_CATEGORY_INDEPENDENT_ATTRIBUTES_REQUIRED_INFO ?></h4></td>
                </tr>
                </tbody>
                <tbody id="tbodyDynamicMatchingInput" style="display:none;">
                <tr>
                    <th></th>
                    <td class="input"><?php echo ML_GENERAL_VARMATCH_SELECT_CATEGORY ?></td>
                    <td class="info"></td>
                </tr>
                </tbody>
                <tbody id="tbodyDynamicMatchingOptionalHeadline" style="display:none;">
                <tr class="even">
                    <th class="ottoGreyBackground"><h4><?php echo ML_OTTO_CATEGORY_INDEPENDENT_ATTRIBUTES_OTIONAL ?></h4></th>
                    <td class="ottoGreyBackground" colspan="3"><h4><?php echo ML_OTTO_CATEGORY_INDEPENDENT_ATTRIBUTES_OTIONAL_INFO ?></h4></td>
                </tr>
                </tbody>
                <tbody id="tbodyDynamicMatchingOptionalInput" style="display:none;">
                <tr>
                    <th></th>
                    <td class="input"><?php echo ML_GENERAL_VARMATCH_SELECT_CATEGORY ?></td>
                    <td class="info"></td>
                </tr>
                </tbody>
                <tbody id="tbodyDynamicMatchingCustomHeadline" style="display:none;">
                <tr class="headline">
                    <td colspan="1"><h4><?php echo $aTitles['mpCustomAttributeTitle'] ?></h4></td>
                    <td colspan="2"><h4><?php echo ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB ?></h4></td>
                </tr>
                </tbody>
                <tbody id="tbodyDynamicMatchingCustomInput" style="display:none;">
                <tr>
                    <th></th>
                    <td class="input"><?php echo ML_GENERAL_VARMATCH_SELECT_CATEGORY ?></td>
                    <td class="info"></td>
                </tr>
                </tbody>
            </table>
            <p id="categoryInfo" style="display: none"><?php echo ML_GENERAL_VARMATCH_CATEGORY_INFO ?></p>
            <br><br><br>
            <table class="actions">
                <thead>
                <tr>
                    <th><?php echo ML_LABEL_ACTIONS ?></th>
                </tr>
                </thead>
                <tbody>
                <tr class="firstChild">
                    <td>
                        <table>
                            <tbody>
                            <tr>
                                <td class="firstChild">
                                    <button type="button" class="ml-button ml-reset-matching">
                                        <?php echo ML_GENERAL_VARMATCH_RESET_MATCHING ?></button>
                                </td>
                                <td></td>
                                <td class="lastChild">
                                    <input type="submit" value="<?php echo ML_GENERAL_VARMATCH_SAVE_BUTTON ?>"
                                           class="ml-button mlbtn-action">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
        <?php
        return ob_get_clean();
    }
}
