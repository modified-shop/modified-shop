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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/AttributesMatchingHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'metro/classes/MetroApiConfigValues.php');

class MetroHelper extends AttributesMatchingHelper {

    private static $instance;
    protected $marketplaceTitle = 'Metro';

    public static function gi() {
        if (self::$instance === null) {
            self::$instance = new MetroHelper();
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
                'IncludeTax' => false,
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

    public static function getQuantityForMetro($iProductsQuantity, $mpID) {
        $sCalcMethod = getDBConfigValue('metro.quantity.type', $mpID);
        $iQuantityValue = getDBConfigValue('metro.quantity.value', $mpID);
        $iMaxQuantity = getDBConfigValue('metro.maxquantity', $mpID, 0);
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

    public static function getSubstitutePictures($tmplStr, $pID, $imagePath) {
        $undo = ml_extractBase64($tmplStr);

        $pics = MLProduct::gi()->getAllImagesByProductsId($pID);
        $i = 1;
        # Ersetze alle Bilder
        foreach ($pics as $pic) {
            $tmplStr = str_replace(
                '#PICTURE'.$i.'#',
                "<img src=\"".$imagePath.$pic."\" style=\"border:0;\" alt=\"\" title=\"\" />",
                preg_replace(
                    '/(src|SRC|href|HREF|rev|REV)(\s*=\s*)(\'|")(#PICTURE'.$i.'#)/',
                    '\1\2\3'.$imagePath.$pic,
                    $tmplStr
                )
            );
            ++$i;
        }
        # Uebriggebliebene #PICTUREx# loeschen
        $tmplStr = preg_replace('/<[^<]*(src|SRC|href|HREF|rev|REV)\s*=\s*(\'|")#PICTURE\d+#(\'|")[^>]*\/*>/', '', $tmplStr);
        $tmplStr = preg_replace('/#PICTURE\d+#/', '', $tmplStr);
        $str = ml_restoreBase64($tmplStr, $undo);

        # ggf. leere image tags loeschen
        $str = preg_replace('/<img[^>]*src=(""|\'\')[^>]*>/i', '', $str);
        return $str;
    }

    public static function substituteTemplate($mpId, $pID, $template, $substitution) {
        return substituteTemplate($template, $substitution);
    }

    public static function MetroStripTagsFromDescription($string) {
        // tags constrained + attributes in tags not allowed + decoded &nbsp; not allowed
        return preg_replace('/(<p.+?)+(>)/i', '<p>',
                   preg_replace('/(<span.+?)+(>)/i', '<span>',
                       strip_tags(
                           replaceNbsp($string), '<p><ul><ol><li><span><br><b>'
                       )
                   )
               );
    }

    public static function str2float($str) {
        $val = str_replace(',', '.', $str);
        $val = preg_replace('/\.(?=.*\.)/', '', $val);
        return floatval($val);
    }
 
    // metro.shippingprofile.cost must have numeric values
    public static function fixShippingprofileCost(&$aProfileCost) {
        if (!is_array($aProfileCost)) {
            $aProfileCost = array(0.00);
            return;
        }
        foreach ($aProfileCost as $kCost => $vCost) {
            if (!is_numeric($vCost)) {
                $aProfileCost[$kCost] = (float)0.00;
            }
        }
    }

    public function getShippingProfilesHtml($iSelectedProfile=999) {

        $aDefaultProfile = getDBConfigValue('metro.shippingprofile', $this->mpId);
        $aProfileName = getDBConfigValue('metro.shippingprofile.name', $this->mpId);
        $aProfileCost = getDBConfigValue('metro.shippingprofile.cost', $this->mpId);
        self::fixShippingprofileCost($aProfileCost);
        $html = '';

        if ($iSelectedProfile < 999) {
            foreach ($aDefaultProfile['defaults'] as $iKey => $sValue) {
                $aDefaultProfile['defaults'][$iKey] = '';
            }
            unset($iKey); unset($sValue);
            $aDefaultProfile['defaults'][$iSelectedProfile] = '1';
        }

        foreach ($aDefaultProfile['defaults'] as $iKey => $sValue) {
            $html .= '<option value="'.$iKey.'" '.(($sValue) ? 'selected="selected"' : '').'>'.$aProfileName[$iKey].' ('.number_format((float)$aProfileCost[$iKey], 2, '.', '').' Euro) </option>';
        }

        return $html;
    }

    public function getShippingGroupsHtml($iSelectedGroup=999) {

        $aGroups = getDBConfigValue('metro.shippinggroup', $this->mpId, ''); 
        if (empty($aGroups)) return '';
        $aGroupNames = getDBConfigValue('metro.shippinggroup.name',$this->mpId, '');
        if (empty($aGroups)) return '';
        $html = '';

        if ($iSelectedGroup < 999) {
            foreach ($aGroups['defaults'] as $iKey => $sValue) {
                $aGroups['defaults'][$iKey] = '';
            }
            unset($iKey); unset($sValue);
            $aGroups['defaults'][$iSelectedGroup] = '1';
        }

        foreach ($aGroups['defaults'] as $iKey => $sValue) {
            $html .= '<option value="'.$iKey.'" '.(($sValue) ? 'selected="selected"' : '').'>'.$aGroupNames[$iKey].'</option>';
        }

        return $html;
    }

    /**
     * Returns the name of selected shipping group
     *
     * @param $iSelectedGroup
     * @return string
     */
    public function getShippingGroupName($iSelectedGroup=999) {
        $aGroups = getDBConfigValue('metro.shippinggroup', $this->mpId, '');
        if (empty($aGroups)) return '';
        $aGroupNames = getDBConfigValue('metro.shippinggroup.name',$this->mpId, '');
        if (isset($aGroupNames[$iSelectedGroup])) {
            return $aGroupNames[$iSelectedGroup];
        } else {
            return $aGroupNames[array_search('1', $aGroups['defaults'])];
        }
    }

    public function getVarMatchTranslations() {
        $translations = parent::getVarMatchTranslations();
        $translations['mpValue'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_MP_VALUE);
        $translations['attributeChangedOnMp'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_ATTRIBUTE_CHANGED_ON_MP);
        $translations['attributeDeletedOnMp'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_ATTRIBUTE_DELETED_ON_MP);
        $translations['attributeValueDeletedOnMp'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_ATTRIBUTE_VALUE_DELETED_ON_MP);;
        $translations['categoryWithoutAttributesInfo'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_CATEGORY_WITHOUT_ATTRIBUTES_INFO);

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
        $dbData = $this->getPreparedData($category, $prepare, $customIdentifier);
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
            $utf8Code = $this->fixHTMLUTF8Entities($code);
            #$utf8Code = $code;
            $attributes[$utf8Code] = array(
                'AttributeCode' => $utf8Code,
                'AttributeName' => $value['title'],
                'AllowedValues' => isset($value['values']) ? $value['values'] : array(),
                'AttributeDescription' => isset($value['desc']) ? $value['desc'] : '',
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
                $utf8Code = $this->fixHTMLUTF8Entities($utf8Code);
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
						AND MpIdentifier = "'.$category.'"
						AND CustomIdentifier = "'.$customIdentifier.'"
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
                ), $variationThemeData
            );
        }

        return $attributes;
    }

    public function getAttributesFromMP($category, $additionalData = null, $customIdentifier = '') {
        $data = MetroApiConfigValues::gi()->getVariantConfigurationDefinition($category);

        if (!is_array($data) || !isset($data['attributes'])) {
            $data = array();
        }

        if (empty($data['attributes'])) {
            $data['attributes'] = array();
        }

        return $data;
    }

    protected function getPreparedData($category, $prepare = false, $customIdentifier = '') {
        $availableCustomConfigs = array();

        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sSQLAnd = ' AND products_model = "'.$prepare.'"';
        } else {
            $sSQLAnd = ' AND products_id = "'.$prepare.'"';
        }

        if ($prepare) {
            $availableCustomConfigs = json_decode(MagnaDB::gi()->fetchOne(eecho('
                SELECT ShopVariation
                  FROM '.TABLE_MAGNA_METRO_PREPARE.'
                 WHERE     MpId = '.$this->mpId.'
                       AND PrimaryCategory = "'.$category.'"
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

        return MagnaDB::gi()->recordExists(TABLE_MAGNA_METRO_PREPARE, array(
            'MpId' => $this->mpId,
            $sKeyType => $prepare,
            'PrimaryCategory' => $category,
        ));
    }

    /**
     * Gets prepared attributes data for products prepared for given category.
     *
     * @param string $category
     * @return array|null
     */
    protected function getPreparedProductsData($category) {
        $dataFromDB = MagnaDB::gi()->fetchArray(eecho('
            SELECT `ShopVariation`
              FROM '.TABLE_MAGNA_METRO_PREPARE.'
             WHERE     mpID = '.$this->mpId.'
                   AND PrimaryCategory = "'.$category.'"
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
            'mpTitle' => str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_TITLE),
            'mpAttributeTitle' => str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_MP_ATTRIBUTE),
            'mpOptionalAttributeTitle' => str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE),
            'mpCustomAttributeTitle' => str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE),
        );
    }

}
