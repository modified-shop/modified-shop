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
 * (c) 2010 - 2022 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/MagnaCompatibleHelper.php');

class AttributesMatchingHelper extends MagnaCompatibleHelper {

    const UNLIMITED_ADDITIONAL_ATTRIBUTES = PHP_INT_MAX;

    /** @var int Number of allowed custom attributes */
    protected $numberOfMaxAdditionalAttributes = 0;
    /** @var int if this is exceeded in the preparation, use only the values for the product(s) */
    protected $reasonableNumberOfVariationValues = 256;
    protected $mpId;
    protected $marketplace;
    protected $defaultLanguage;

    public function __construct() {
        global $_MagnaSession;
        $this->mpId = $_MagnaSession['mpID'];
        $this->marketplace = $_MagnaSession['currentPlatform'];
        $this->defaultLanguage = $_SESSION['languages_id'];
    }

    public function getShopVariations() {
        $languageId = getDBConfigValue($this->marketplace . '.lang', $this->mpId, $this->defaultLanguage);
        $languageId = is_numeric($languageId) ? $languageId : $this->defaultLanguage;

        if (    defined('TABLE_PRODUCTS_OPTIONS')
             && MagnaDB::gi()->tableExists(TABLE_PRODUCTS_OPTIONS)
             && (getDBConfigValue('general.options', '0', 'old') != 'gambioProperties')) {
            $defaultGroupsOptions = $this->getVariationGroupsOptions($this->defaultLanguage);
            $groupsOptions = $this->getVariationGroupsOptions($languageId);

            if (empty($groupsOptions)) {
                $groupsOptions = $defaultGroupsOptions;
            }

            if (!empty($groupsOptions)) {
                foreach ($groupsOptions as $k => &$g) {
                    if (empty($g['Name'])) {
                        foreach ($defaultGroupsOptions as $dg) {
                            if ($dg['Code'] === $g['Code'] && !empty($dg['Name'])) {
                                $g['Name'] = $dg['Name'];
                                break;
                            }
                        }

                        // we cant support empty names on groups - so if still empty remove it
                        if (empty($g['Name'])) {
                            unset($groupsOptions[$k]);
                            continue;
                        }
                    }

                    $defaultValues = $this->getVariationGroupsOptionValues($g['Code'], $this->defaultLanguage);
                    $values = $this->getVariationGroupsOptionValues($g['Code'], $languageId);

                    if (empty($values)) {
                        if (empty($defaultValues)) {
                            unset($groupsOptions[$k]);
                            continue;
                        } else {
                            $values = $defaultValues;
                        }
                    }

                    $g['Values'] = array();
                    foreach ($values as $v) {
                        if (!empty($v['Value'])) {
                            $g['Values'][$v['Id']] = $v['Value'];
                        } else {
                            foreach ($defaultValues as $dv) {
                                if ($dv['Id'] === $v['Id']) {
                                    $g['Values'][$v['Id']] = $dv['Value'];
                                    break;
                                }
                            }
                        }
                    }

                    $g['Type'] = 'select';
                }
            } else {
                $groupsOptions = array();
            }
        } else {
            $groupsOptions = array();
        }

        if (    defined('TABLE_MAGNA_PROPERTIES_DESCRIPTION')
             && MagnaDB::gi()->tableExists(TABLE_MAGNA_PROPERTIES_DESCRIPTION)
             && (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties')) {
            $defaultGroupsProperties = $this->getVariationGroupsProperties($this->defaultLanguage);
            $groupsProperties = $this->getVariationGroupsProperties($languageId);

            if (empty($groupsProperties)) {
                $groupsProperties = $defaultGroupsProperties;
            }

            if (!empty($groupsProperties)) {
                foreach ($groupsProperties as $k => &$g) {
                    if (!empty($g['AdminName'])) {
                        $g['Name'] = $g['AdminName'];
                        unset($g['AdminName']);
                    }

                    if (empty($g['Name'])) {
                        foreach ($defaultGroupsProperties as $dg) {
                            if ($dg['Code'] === $g['Code']) {
                                $g['Name'] = !empty($dg['AdminName']) ? $dg['AdminName'] : $dg['Name'];
                                break;
                            }
                        }

                        // we cant support empty names on properties - so if still empty remove it
                        if (empty($g['Name'])) {
                            unset($groupsProperties[$k]);
                            continue;
                        }
                    }

                    $defaultValues = $this->getVariationGroupsPropertyValues($g['Code'], $this->defaultLanguage);
                    $values = $this->getVariationGroupsPropertyValues($g['Code'], $languageId);

                    if (empty($values)) {
                        if (empty($defaultValues)) {
                            unset($groupsProperties[$k]);
                            continue;
                        } else {
                            $values = $defaultValues;
                        }
                    }

                    $g['Values'] = array();
                    foreach ($values as $v) {
                        if (!empty($v['Value'])) {
                            $g['Values'][$v['Id']] = $v['Value'];
                        } else {
                            foreach ($defaultValues as $dv) {
                                if ($dv['Id'] === $v['Id']) {
                                    $g['Values'][$v['Id']] = $dv['Value'];
                                    break;
                                }
                            }
                        }
                    }

                    $g['Type'] = 'select';
                }
            } else {
                $groupsProperties = array();
            }
        } else {
            $groupsProperties = array();
        }
        if (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties') {
            $groups = $groupsProperties;
        } else {
            $groups = $groupsOptions;
        }
        $groups = array(ML_VARIATION => $groups);
        $this->addAdditionalAttributesShop($groups, $languageId);
        /* {Hook} "AttributesMatchingHelper_addCustomAdditionalAttributesShop": Runs after addAdditionalAttributesShop and allows to add own attributes. You can define a method addCustomAdditionalAttributesShop which extends $groups (as obtained from addAdditionalAttributesShop), and then call it.
                          Variables that can be used: 
                           <ul><li>$this->mpId: The ID of the marketplace.</li>
                               <li>$this->marketplace: The name of the marketplace.</li>
                               <li>$groups - the attribute groups, see addAdditionalAttributesShop()</li>
                           </ul>
         */
        if (($hp = magnaContribVerify('AttributesMatchingHelper_addCustomAdditionalAttributesShop', 1)) !== false) {
            require($hp);
        }

        arrayEntitiesToUTF8($groups);
        $aOut = array();
        foreach ($groups as $sGroupKey => $aGroupValue) {
            foreach ($aGroupValue as $aGroup) {
                if (!isset($aGroup['Disabled'])) {
                    $aGroup['Disabled'] = '';
                }

                if (!isset($aGroup['Custom'])) {
                    $aGroup['Custom'] = '';
                }
                $aOut[$sGroupKey][$aGroup['Code']] = $aGroup;
            }
        }

        return $aOut;
    }

    /**
     *
     *
     * @param $languageId
     * @return array|bool
     */
    private function getVariationGroupsOptions($languageId) {
        return MagnaDB::gi()->fetchArray('
            SELECT products_options_id AS Code, products_options_name AS Name
            FROM ' . TABLE_PRODUCTS_OPTIONS . '
            WHERE language_id = "' . $languageId . '"
            ORDER BY products_options_name ASC
        ');
    }

    /**
     *
     *
     * @param $optionCode
     * @param $languageId
     * @return array|bool
     */
    private function getVariationGroupsOptionValues($optionCode, $languageId) {
        return MagnaDB::gi()->fetchArray('
            SELECT pov.products_options_values_id Id, pov.products_options_values_name AS Value
            FROM ' . TABLE_PRODUCTS_OPTIONS_VALUES . ' pov
            INNER JOIN ' . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . ' ov2po ON
                    ov2po.products_options_values_id = pov.products_options_values_id
                AND ov2po.products_options_id = "' . $optionCode . '"
                    AND pov.products_options_values_name != \'\'
            WHERE pov.language_id = "' . $languageId . '"
            ORDER BY pov.products_options_values_name ASC
        ');
    }

    /**
     *
     *
     * @param $languageId
     * @return array|bool
     */
    private function getVariationGroupsProperties($languageId) {
        return MagnaDB::gi()->fetchArray('
            SELECT properties_id AS Code, properties_name AS Name, properties_admin_name AS AdminName
            FROM ' . TABLE_MAGNA_PROPERTIES_DESCRIPTION . '
            WHERE language_id = "' . $languageId . '"
            ORDER BY properties_name ASC
        ');
    }

    /**
     *
     *
     * @param $propertyCode
     * @param $languageId
     * @return array|bool
     */
    private function getVariationGroupsPropertyValues($propertyCode, $languageId) {
        $aPVValues = MagnaDB::gi()->fetchArray("
            SELECT properties_values_id
              FROM ".TABLE_MAGNA_PROPERTIES_VALUES." 
             WHERE properties_id = '".$propertyCode."'
          ORDER BY properties_values_id
        ", true);

        if (empty($aPVValues)) return $aPVValues;

        return MagnaDB::gi()->fetchArray('
            SELECT properties_values_id AS Id, values_name AS Value
            FROM ' . TABLE_MAGNA_PROPERTIES_DESCRIPTION_VALUES . '
            WHERE properties_values_id IN ('.implode(',', $aPVValues).')
            AND language_id = "' . $languageId . '"
                   AND values_name != \'\'
            ORDER BY values_name ASC
        '); 
    }

    /**
     * @return int
     */
    public function getNumberOfMaxAdditionalAttributes() {
        return $this->numberOfMaxAdditionalAttributes;
    }

    /**
     * Gets attributes from marketplace for supplied category.
     *
     * @param string $category
     * @param mixed $additionalData If MP requires some additional config for a call, use this parameter.
     * @return array
     */
    protected function getAttributesFromMP($category, $additionalData = null, $customIdentifier = '') {
        return array();
    }

    protected function isProductPrepared($category, $prepare = false) {
        return false;
    }

    /**
     * Gets attributes from prepare table for specified product
     *
     * @param string $category
     * @param mixed $prepare ID or SKU of a product
     * @return mixed FALSE if prepare for specified product does not exist; NULL if Attributes are empty; array of attributes
     */
    protected function getPreparedData($category, $prepare = false, $customIdentifier = '') {
        return null;
    }

    /**
     * Gets prepared attributes data for products prepared for given category.
     *
     * @param string $category
     * @return array|null
     */
    protected function getPreparedProductsData($category) {
        return null;
    }

    /**
     * Gets saved variation theme.
     * @param $category
     * @param bool $prepare
     * @return null
     */
    protected function getSavedVariationThemeCode($category, $prepare = false) {
        return null;
    }

    protected function addAdditionalAttributesShop(&$groups, $languageId) {
        // Getting manufacturer list
        $manufacturers = MagnaDB::gi()->fetchArray('
			SELECT manufacturers_id AS ID, manufacturers_name AS Name
			FROM ' . TABLE_MANUFACTURERS . '
			WHERE manufacturers_id <> 0
			ORDER BY manufacturers_name ASC
		');

        $resultManufacturerList = array();
        if (!empty($manufacturers)) {
            foreach ($manufacturers as $manufacturer) {
                $resultManufacturerList[$manufacturer['ID']] = fixHTMLUTF8Entities($manufacturer['Name']);
            }
        }

        // Getting FSK 18
        // These values are hardcoded in osCommerce, we can not pull them from DB
        $resultFSK18List = array(NO, YES);

        // Getting delivery time
        if (MagnaDB::gi()->tableExists(TABLE_SHIPPING_STATUS)) {
            $deliveryTimes = MagnaDB::gi()->fetchArray('
				SELECT shipping_status_id AS ID, shipping_status_name AS Name
				FROM ' . TABLE_SHIPPING_STATUS . '
				WHERE shipping_status_id <> 0
					AND language_id = "' . $languageId . '"
				ORDER BY shipping_status_name ASC
			');
        } else {
            $deliveryTimes = array(
                array(
                    getDBConfigValue($this->marketplace . '.shippingtime', $this->mpId, 0) => getDBConfigValue($this->marketplace . '.shippingtime', $this->mpId, 0)
                )
            );
        }

        $resultDeliveryTimeList = array();
        if (!empty($deliveryTimes)) {
            foreach ($deliveryTimes as $deliveryTime) {
                $resultDeliveryTimeList[$deliveryTime['ID']] = fixHTMLUTF8Entities($deliveryTime['Name']);
            }
        }

        // Getting shop tabel list
        $tables = MagnaDB::gi()->getAvailableTables();
        $editedTables = array();
        foreach ($tables as $table) {
            $editedTables[$table] = $table;
        }

        $groups = array_merge($groups, array(
            ML_PRODUCT_DEFAULT_FIELDS => array(
                array(
                    'Code' => 'article_number',
                    'Name' => ML_COMPARISON_SHOPPING_FIELD_MODEL_NUMBER,
                    'Values' => array(),
                    'Custom' => true,
                    'Type' => 'text',
                ),
                array(
                    'Code' => 'title',
                    'Name' => ML_COMPARISON_SHOPPING_FIELD_ITEM_TITLE,
                    'Values' => array(),
                    'Custom' => true,
                    'Type' => 'text',
                ),
                array(
                    'Code' => 'description',
                    'Name' => ML_COMPARISON_SHOPPING_FIELD_DESCRIPTION,
                    'Values' => array(),
                    'Custom' => true,
                    'Type' => 'text',
                ),
                array(
                    'Code' => 'ean',
                    'Name' => ML_COMPARISON_SHOPPING_FIELD_EAN,
                    'Values' => array(),
                    'Custom' => true,
                    'Type' => 'text',
                ),
                array(
                    'Code' => 'weight',
                    'Name' => ML_ATTRIBUTES_MATCHING_FIELD_WEIGHT_AND_UNIT,
                    'Values' => array(),
                    'Custom' => true,
                    'Type' => 'text',
                ),
                array(
                    'Code' => 'weight_value',
                    'Name' => ML_ATTRIBUTES_MATCHING_FIELD_WEIGHT_VALUE,
                    'Values' => array(),
                    'Custom' => true,
                    'Type' => 'text',
                ),
                array(
                    'Code' => 'weight_unit',
                    'Name' => ML_ATTRIBUTES_MATCHING_FIELD_WEIGHT_UNIT,
                    'Values' => array(),
                    'Custom' => true,
                    'Type' => 'text',
                ),
                array(
                    'Code' => 'content_volume',
                    'Name' => ML_COMPARISON_SHOPPING_FIELD_VPE,
                    'Values' => array(),
                    'Custom' => true,
                    'Type' => 'text',
                ),
                array(
                    'Code' => 'content_volume_unit',
                    'Name' => ML_COMPARISON_SHOPPING_FIELD_VPE_UNIT,
                    'Values' => array(),
                    'Custom' => true,
                    'Type' => 'text',
                ),
                array(
                    'Code' => 'content_volume_value',
                    'Name' => ML_COMPARISON_SHOPPING_FIELD_VPE_VALUE,
                    'Values' => array(),
                    'Custom' => true,
                    'Type' => 'text',
                ),
                array(
                    'Code' => 'manufacturer',
                    'Name' => ML_COMPARISON_SHOPPING_FIELD_MANUFACTURER_NAME,
                    'Values' => $resultManufacturerList,
                    'Custom' => '',
                    'Type' => 'select',
                ),
                array(
                    'Code' => 'fsk_18',
                    'Name' => ML_COMPARISON_SHOPPING_FIELD_FSK_18,
                    'Values' => $resultFSK18List,
                    'Custom' => '',
                    'Type' => 'select',
                ),
                array(
                    'Code' => 'shipping_cost',
                    'Name' => ML_COMPARISON_SHOPPING_FIELD_SHIPPING_PRICE,
                    'Values' => array(),
                    'Custom' => true,
                    'Type' => 'text',
                ),
                array(
                    'Code' => 'delivery_time',
                    'Name' => ML_COMPARISON_SHOPPING_FIELD_DELIVERY_TIME,
                    'Values' => $resultDeliveryTimeList,
                    'Custom' => '',
                    'Type' => 'select',
                ),
                array(
                    'Code' => 'minimum_order_quantity',
                    'Name' => ML_COMPARISON_SHOPPING_FIELD_MINIMUM_ORDER_QUANTITY,
                    'Values' => array(),
                    'Custom' => true,
                    'Type' => 'text',
                ),
                array(
                    'Code' => 'possible_amount_interval',
                    'Name' => ML_COMPARISON_SHOPPING_FIELD_POSSIBLE_AMOUNT_INTERVAL,
                    'Values' => array(),
                    'Custom' => true,
                    'Type' => 'text',
                ),
            ),
            ML_GENERAL_VARMATCH_ADDITIONAL_OPTIONS => array(
                array(
                    'Code' => 'freetext',
                    'Name' => ML_GENERAL_VARMATCH_FREE_TEXT_LABEL,
                    'Values' => array(),
                    'Type' => 'text',
                ),
                array(
                    'Code' => 'attribute_value',
                    'Name' => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_CHOOSE_MP_VALUE),
                    'Values' => array(),
                    'Type' => 'select',
                ),
                array(
                    'Code' => 'database_value',
                    'Name' => ML_GENERAL_VARMATCH_CHOOSE_DB_VALUE,
                    'Values' => $editedTables,
                    'Type' => 'text',
                ),
            ),
        ));
        // Additional Fields check by Database columns
        if( MagnaDB::gi()->tableExists('products_item_codes')
            && MagnaDB::gi()->columnExistsInTable('brand_name', 'products_item_codes')
        ) {
            $groups[ML_PRODUCT_DEFAULT_FIELDS][] = array(
                'Code' => 'brand_name',
                'Name' => ML_COMPARISON_SHOPPING_FIELD_BRAND,
                'Custom' => true,
                'Values' => array(),
                'Type' => 'text',
            );
        }
        // Additional Fields check by Database columns
        if (MagnaDB::gi()->columnExistsInTable('products_manufacturers_model', TABLE_PRODUCTS)
            || MagnaDB::gi()->columnExistsInTable('products_manufacturers_sku', TABLE_PRODUCTS)
            || (MagnaDB::gi()->tableExists('products_item_codes') && MagnaDB::gi()->columnExistsInTable('code_mpn', 'products_item_codes'))
        ) {
            $groups[ML_PRODUCT_DEFAULT_FIELDS][] = array(
                'Code' => 'manufacturerpartnumber',
                'Name' => 'Herstellerartikelnummer (MPN)',
                'Values' => array(),
                'Custom' => true,
                'Type' => 'text',
            );
        }
    }

    protected function getVariationMatchingTableName() {
        return 'magnalister_' . $this->marketplace . '_variantmatching';
    }

    protected function validateCustomAttributes(
    $key, &$value, &$previouslyMatchedAttributes, &$errors, &$emptyCustomName, $savePrepare, $isSelectedAttribute, &$numberOfMatchedAdditionalAttributes
    ) {
        if (strpos($key, 'additional_attribute_') !== false && $value['Code'] !== 'null') {
            $invalidName = false;
            $numberOfMatchedAdditionalAttributes++;

            if (empty($value['CustomName']) && ($savePrepare || $isSelectedAttribute)) {
                $value['Error'] = true;
                if (!$emptyCustomName && $savePrepare) {
                    $errors[] = ML_GENERAL_VARMATCH_ERROR_EMPTY_CUSTOM_ATTRIBUTE_NAME;
                }
                $emptyCustomName = true;
            } else {
                $savedAttributeName = '';
                foreach ($previouslyMatchedAttributes as $previouslyMatchedAttribute) {
                    $savedAttributeName = !empty($previouslyMatchedAttribute['CustomName']) ?
                            $previouslyMatchedAttribute['CustomName'] : $previouslyMatchedAttribute['AttributeName'];
                    if ($savedAttributeName === $value['CustomName']) {
                        $invalidName = true;
                        break;
                    }
                }

                if ($invalidName && ($savePrepare || $isSelectedAttribute)) {
                    $value['Error'] = true;
                    if ($savePrepare) {
                        $errors[] = str_replace(
                                array('%attributeName%', '%marketplace%'), array($savedAttributeName, ucfirst($this->marketplace)), ML_GENERAL_VARMATCH_ERROR_CUSTOM_ATTRIBUTE_NAME_INVALID
                        );
                    }
                }
            }
        }

        $previouslyMatchedAttributes[$key] = $value;
    }

    public function getCategoryMatching($category, $customIdentifier = '') {
        $tableName = $this->getVariationMatchingTableName();

        $matching = json_decode(MagnaDB::gi()->fetchOne(eecho('
				SELECT ShopVariation
				FROM ' . $tableName . '
				WHERE MpId = ' . $this->mpId . '
					AND MpIdentifier = "' . $category . '"
					AND CustomIdentifier = "' . $customIdentifier . '"
			', false)), true);

        return $matching ? $matching : array();
    }

    public function getCustomIdentifiers($category, $prepare = false, $getDate = false) {
        return array();
    }

    /**
     * @param string $category
     * @param bool $prepare
     * @param bool $getDate Set to <b>TRUE</b> if modification date should be returned
     * @param mixed $additionalData Use this parameter for additional handling if needed.
     * @return array
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
        if (!empty($mpData['attributes'])) {
            foreach ($mpData['attributes'] as $code => $value) {
                $utf8Code = $this->fixHTMLUTF8Entities($code);
                $attributes[$utf8Code] = array(
                    'AttributeCode' => $utf8Code,
                    'AttributeName' => $value['title'],
                    'AllowedValues' => isset($value['values']) ? $value['values'] : array(),
                    'AttributeDescription' => isset($value['desc']) ? $value['desc'] : '',
                    'CurrentValues' => isset($dbData[$utf8Code]) ? $dbData[$utf8Code] : array('Values' => array()),
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
					FROM ' . $tableName . '
					WHERE MpId = ' . $this->mpId . '
						AND MpIdentifier = "' . $category . '"
						AND CustomIdentifier = "' . $customIdentifier . '"
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

    /**
     * Detects if matched attribute is deleted on shop.
     * @param array $shopAttributes
     * @param array $attribute
     * @param $message
     * @return bool
     */
    public function detectIfAttributeIsDeletedOnShop($shopAttributes, $attribute, &$message) {
        if ($attribute['Code'] === 'null' || $attribute['Code'] === 'freetext' || $attribute['Code'] === 'attribute_value' ||
                $attribute['Code'] === 'database_value') {
            return false;
        }

        if (!isset($shopAttributes[$attribute['Code']])) {
            $message = ML_GENERAL_VARMATCH_ATTRIBUTE_DELETED_ON_SHOP;
            return true;
        }

        if (isset($attribute['Values']) && is_array($attribute['Values'])) {
            foreach ($attribute['Values'] as $attributeValue) {
                // If attribute is not an array that means that it has single value. It is explicitly casted to
                // an array and then checking function is the same both for single and multi values.
                if (!is_array($attributeValue['Shop']['Key'])) {
                    $attributeValue['Shop']['Key'] = array($attributeValue['Shop']['Key']);
                }
                $shopAttributeValues = $shopAttributes[$attribute['Code']]['Values'];
                $missingShopValueKeys = array_diff_key(array_flip($attributeValue['Shop']['Key']), $shopAttributeValues);
                if (count($missingShopValueKeys) > 0) {
                    $message = ML_GENERAL_VARMATCH_ATTRIBUTE_VALUE_DELETED_ON_SHOP;
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    protected function addAdditionalAttributesMP(&$attributes, $aResultFromDB) {
        $additionalAttributes = array();
        $newAdditionalAttributeIndex = 0;
        $positionOfIndexInAdditionalAttribute = 2;

        $aResultFromDB = is_array($aResultFromDB) ? $aResultFromDB : array();
        if ($aResultFromDB) {
            foreach ($aResultFromDB as $key => $value) {
                if (strpos($key, 'additional_attribute_') === 0) {
                    $additionalAttributes[$key] = $value;
                    $keyParts = explode('_', $key);
                    $additionalAttributeIndex = (int) $keyParts[$positionOfIndexInAdditionalAttribute];
                    $newAdditionalAttributeIndex = ($newAdditionalAttributeIndex > $additionalAttributeIndex) ?
                            $newAdditionalAttributeIndex + 1 : $additionalAttributeIndex + 1;
                }
            }
        }

        $additionalAttributes['additional_attribute_' . $newAdditionalAttributeIndex] = array();

        foreach ($additionalAttributes as $attributeKey => $attributeValue) {
            $attributes[$attributeKey] = array(
                'AttributeCode' => $attributeKey,
                'AttributeName' => ML_GENERAL_VARMATCH_ADDITIONAL_ATTRIBUTE_LABEL,
                'AttributeDescription' => '',
                'AllowedValues' => array(),
                'Custom' => true,
                'CustomAttributeValue' => isset($aResultFromDB[$attributeKey]['CustomAttributeValue']) ?
                $aResultFromDB[$attributeKey]['CustomAttributeValue'] : null,
                'CurrentValues' => isset($aResultFromDB[$attributeKey]) ? $aResultFromDB[$attributeKey] : array('Values' => array()),
                'ChangeDate' => '',
                'Required' => false,
                'DataType' => 'text',
            );
        }
    }

    /**
     * Checks for each product attribute whether it is prepared differently in Attributes Matching tab,
     * and if so, marks it as Modified.
     * Arrays cannot be compared directly because values could be in different order (with different numeric keys).
     *
     * @param array $globalMatching
     * @param array $productMatching
     * @return bool TRUE if there are differences; otherwise, FALSE
     */
    public function detectChanges($globalMatching, &$productMatching) {
        if (empty($globalMatching) && empty($productMatching)) {
            return false;
        }

        if ((empty($globalMatching) && !empty($productMatching)) || (!empty($globalMatching) && empty($productMatching))
        ) {
            return true;
        }

        if ((is_array($globalMatching) && !is_array($productMatching)) || (!is_array($globalMatching) && is_array($productMatching))
        ) {
            return true;
        }

        $different = false;
        if (count($globalMatching) != count($productMatching)) {
            $different = true;
        }

        foreach ($globalMatching as $attributeCode => $attributeSettings) {
            // Errors should not be compared, because they are not relevant.
            if (isset($attributeSettings['Error'])) {
                unset($attributeSettings['Error']);
            }

            if (!empty($productMatching[$attributeCode])) {
                $productAttrs = isset($productMatching[$attributeCode]['CurrentValues']) ?
                        $productMatching[$attributeCode]['CurrentValues'] : $productMatching[$attributeCode];

                // Errors should not be compared, because they are not relevant.
                if (isset($productAttrs['Error'])) {
                    unset($productAttrs['Error']);
                }

                if (!isset($productAttrs['Values']) || !is_array($productAttrs['Values']) || !is_array($attributeSettings['Values'])) {
                    $productMatching[$attributeCode]['Modified'] = $productAttrs != $attributeSettings;
                    if ($productMatching[$attributeCode]['Modified']) {
                        $different = true;
                    }

                    continue;
                }

                if (isset($productAttrs['Values']['Table']) || isset($attributeSettings['Values']['Table'])) {
                    $productMatching[$attributeCode]['Modified'] = $productAttrs != $attributeSettings;
                    if ($productMatching[$attributeCode]['Modified']) {
                        $different = true;
                    }

                    continue;
                }

                $productAttrsValues = $productAttrs['Values'];
                $attributeSettingsValues = $attributeSettings['Values'];
                unset($productAttrs['Values']);
                unset($attributeSettings['Values']);

                // first compare without values (optimization)
                if ($productAttrs == $attributeSettings && count($productAttrsValues) === count($attributeSettingsValues)) {
                    // compare values
                    // values could be in different order so we need to iterate through array and check one by one
                    $allValuesMatched = true;
                    foreach ($productAttrsValues as $attribute) {
                        // Since $productAttrsValues can be array of (string) values, we must check for existence of Info to
                        // avoid Fatal error: Cannot unset string offsets
                        if (!empty($attribute['Marketplace']['Info'])) {
                            unset($attribute['Marketplace']['Info']);
                        }

                        $found = false;
                        foreach ($attributeSettingsValues as $value) {
                            if (!empty($value['Marketplace']['Info'])) {
                                unset($value['Marketplace']['Info']);
                            }

                            if ($attribute == $value) {
                                $found = true;
                                break;
                            }
                        }

                        if (!$found) {
                            $allValuesMatched = false;
                            break;
                        }
                    }

                    if ($allValuesMatched) {
                        $productMatching[$attributeCode]['Modified'] = false;
                        continue;
                    }
                }

                $productMatching[$attributeCode]['Modified'] = true;
                $different = true;
            }
        }

        return $different;
    }

    /**
     * @param string $category
     * @param array $globalMatching
     * @param string $customIdentifier
     * @return bool TRUE if there are differently prepared products; otherwise, FALSE
     */
    protected function areProductsDifferentlyPrepared($category, $globalMatching, $customIdentifier = '') {
        $preparedProducts = $this->getPreparedProductsData($category, $customIdentifier);
        if ($preparedProducts) {
            foreach ($preparedProducts as $productMatching) {
                if ($this->detectChanges($globalMatching, $productMatching)) {
                    return true;
                }
            }
        }

        return false;
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
                        <td colspan="1"><h4><?php echo $aTitles['mpAttributeTitle'] ?></h4></td>
                        <td colspan="2"><h4><?php echo ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB ?></h4></td>
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
                    <tr class="headline">
                        <td colspan="1"><h4><?php echo $aTitles['mpOptionalAttributeTitle'] ?></h4></td>
                        <td colspan="2"><h4><?php echo ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB ?></h4></td>
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

    /**
     * Shop attributes are in two-dimensional array because of opt-groups. Here attributes are flatten and that result is returned.
     * @return array
     */
    public function flatShopVariations() {
        $aFlatVariations = array();
        foreach ($this->getShopVariations() as $sVariationKey => $aVariationValue) {
            foreach ($aVariationValue as $sAttributeCodeKey => $aAttributeCodeValue) {
                $aFlatVariations[$sAttributeCodeKey] = $aAttributeCodeValue;
            }
        }

        return $aFlatVariations;
    }

    protected function autoMatch($categoryId, $sMPAttributeCode, &$aAttributes, $customIdentifier = '') {
        $mpVariations = $this->getMPVariations($categoryId, false, false, null, $customIdentifier);
        $aMPAttributeValues = $mpVariations[$sMPAttributeCode]['AllowedValues'];
        $blAllowFreetext = (    $mpVariations[$sMPAttributeCode]['DataType'] == 'multiSelectAndText'
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
                $blCurrValueFound = false;
                foreach ($aMPAttributeValues as $key => $value) {
                    if (in_array($valueAttribute, $aAlreadyMatchedValues)) {
                        $blCurrValueFound = true;
                        continue;
                    }
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
                        $blCurrValueFound = true;
                        $i++;
                        break;
                    }
                }
                if (!$blCurrValueFound && $blAllowFreetext) {
                    // not found? Use shop value.
                    $newValue[$i]['Shop']['Key'] = $keyAttribute;
                    $newValue[$i]['Shop']['Value'] = $valueAttribute;
                    $newValue[$i]['Marketplace']['Key'] = 'manual';
                    $newValue[$i]['Marketplace']['Value'] = $valueAttribute;
                    $newValue[$i]['Marketplace']['Info'] =  $valueAttribute . ML_GENERAL_VARMATCH_FREE_TEXT;
                    $blFound = true;
                    $blCurrValueFound = true;
                    $i++;
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

    public function checkNewMatchedCombination(&$attributes) {
        foreach ($attributes as $key => $value) {
            if ($key === 0) {
                continue;
            }

            if (isset($attributes['0']) && $value['Shop']['Key'] === $attributes['0']['Shop']['Key']) {
                unset($attributes[$key]);
                break;
            }
        }
    }

    public function getMPAttributeValues($sAttributeCode = false) {
        if ($sAttributeCode) {
            foreach ($this->getShopVariations() as $mpVariation) {
                if (isset($mpVariation[$sAttributeCode])) {
                    return $mpVariation[$sAttributeCode]['Values'];
                }
            }
        }

        return array();
    }

    /**
     * @param string $category
     * @param array $matching
     * @param bool $savePrepare
     * @param bool $fromPrepare
     * @param bool $validateCustomAttributesNumber If this parameter is set to true, number of additional attributes will
     *                                             be validated.
     * @param array $variationThemeAttributes Contains attributes of chosen variation theme.
     * @param string $sCustomIdentifier
     * @return array
     */
    public function saveMatching($category, &$matching, $savePrepare, $fromPrepare, $validateCustomAttributesNumber, $variationThemeAttributes = null, $sCustomIdentifier = '') {
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

        foreach ($matching['ShopVariation'] as $key => &$value) {
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
                    unset($matching['ShopVariation'][$key]);
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
                unset($matching['ShopVariation'][$key]);
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
                $allValuesAreMatched = $this->autoMatch($category, $key, $value, $sCustomIdentifier);
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
            $this->checkNumberOfVariationValues($matching['ShopVariation']);

            // If variation theme is defined for that category and mandatory but nothing is selected.
            if ($variationThemeAttributes === 'null') {
                $errors[] = ML_GENERAL_VARMATCH_CHOOSE_VARIATION_THEME;
            }
        }

        arrayEntitiesToUTF8($matching['ShopVariation']);

        if (!$fromPrepare || !MagnaDB::gi()->recordExists($tableName, array('MpIdentifier' => $category, 'CustomIdentifier' => $sCustomIdentifier)) && $savePrepare) {
            MagnaDB::gi()->insert($tableName, array(
                'MpId' => $this->mpId,
                'MpIdentifier' => $category,
                'CustomIdentifier' => $sCustomIdentifier,
                'ShopVariation' => json_encode($matching['ShopVariation']),
                'IsValid' => isset($matching['IsValid']) && $matching['IsValid'] === 'false' ? 0 : 1,
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

    public function getVarMatchTranslations() {
        return array(
            'defineName' => ML_GENERAL_VARMATCH_DEFINE_NAME,
            'ajaxError' => ML_GENERAL_VARMATCH_AJAX_ERROR,
            'selectVariantGroup' => ML_GENERAL_VARMATCH_SELECT_VARIANT_GROUP,
            'allSelect' => ML_GENERAL_VARMATCH_ALL_SELECT,
            'pleaseSelect' => ML_GENERAL_VARMATCH_PLEASE_SELECT,
            'autoMatching' => ML_GENERAL_VARMATCH_AUTO_MATCHING,
            'resetMatching' => ML_GENERAL_VARMATCH_RESET_MATCHING,
            'manualMatching' => ML_GENERAL_VARMATCH_MANUAL_MATCHING,
            'matchingTable' => ML_GENERAL_VARMATCH_MATCHNIG_TABLE,
            'resetInfo' => ML_GENERAL_VARMATCH_RESET_INFO,
            'shopValue' => ML_GENERAL_VARMATCH_SHOP_VALUE,
            'mpValue' => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_VALUE),
            'webShopAttribute' => ML_GENERAL_VARMATCH_WEBSHOP_ATTRIB,
            'beforeAttributeChange' => ML_GENERAL_VARMATCH_CHANGE_ATTRIBUTE_INFO,
            'deleteCustomGroupButtonTitle' => ML_GENERAL_VARMATCH_DELETE_CUSTOM_BTN_TITLE,
            'deleteCustomGroupButtonContent' => ML_GENERAL_VARMATCH_DELETE_CUSTOM_BTN_CONTENT,
            'buttonOk' => ML_BUTTON_LABEL_OK,
            'buttonCancel' => ML_BUTTON_LABEL_ABORT,
            'info' => ML_LABEL_NOTE,
            'dbtable' => ML_GENERAL_VARMATCH_CHOOSE_DB_TABLE,
            'dbcolumn' => ML_GENERAL_VARMATCH_CHOOSE_DB_COLUMN,
            'dbalias' => ML_GENERAL_VARMATCH_CHOOSE_DB_ALIAS,
            'attributeChangedOnMp' => str_replace('%marketplace%', $this->marketplace, ML_GENERAL_VARMATCH_ATTRIBUTE_CHANGED_ON_MP),
            'attributeDifferentOnProduct' => ML_GENERAL_VARMATCH_ATTRIBUTE_DIFFERENT_ON_PRODUCT,
            'attributeDeletedOnMp' => str_replace('%marketplace%', $this->marketplace, ML_GENERAL_VARMATCH_ATTRIBUTE_DELETED_ON_MP),
            'attributeValueDeletedOnMp' => str_replace('%marketplace%', $this->marketplace, ML_GENERAL_VARMATCH_ATTRIBUTE_VALUE_DELETED_ON_MP),
            'categoryWithoutAttributesInfo' => str_replace('%marketplace%', $this->marketplace, ML_GENERAL_VARMATCH_CATEGORY_WITHOUT_ATTRIBUTES_INFO),
            'differentAttributesOnProducts' => ML_GENERAL_VARMATCH_PRODUCTS_PREPARED_DIFFERENTLY,
            'variationTheme' => ML_GENERAL_VARIATION_THEME,
            'mandatoryInfo' => ML_CDISCOUNT_VARMATCH_MANDATORY_INFO,
            'alreadyMatched' => ML_GENERAL_VARMATCH_ALREADY_MATCHED,
            'multiSelect' => ML_GENERAL_VARMATCH_MULTI_SELECT,
            'multiselectHint' => ML_GENERAL_VARMATCH_MULTISELECTHINT,
        );
    }

    public function getProductModel($selectionName) {
        $pID = MagnaDB::gi()->fetchOne('
			 SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
			 WHERE mpID=\'' . $this->mpId . '\' AND
				  selectionname=\'' . $selectionName . '\' AND
				  session_id=\'' . session_id() . '\'
			  LIMIT 1
		');

        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $productModel = MagnaDB::gi()->fetchOne('
                SELECT products_model
                  FROM ' . TABLE_PRODUCTS . '
                 WHERE products_id=\'' . $pID . '\' LIMIT 1
            ');

            if (!$productModel) {
                $productModel = false;
            }
        } else {
            $productModel = (int) $pID;
        }

        return $productModel;
    }

    /**
     * Converts attribute matching configuration and product data values into array of MP attribute name and value pairs
     *
     * @param array $attributeMatching Matching attribute configurations from AM
     * @param array $productData Product data with concrete values for attributes
     * @param bool &limitResultSetToProductData If set to true only values provided in $productData that are matched will be returned.
     * All free text i MP values will be skipped
     *
     * @return array Key value pairs of matched MP attribute name and its value
     */
    public function convertMatchingToNameValue($attributeMatching, $productData, $limitResultSetToProductData = false) {
        $attributes = array();
        if (isset($attributeMatching) && is_array($attributeMatching)) {
            foreach ($attributeMatching as $mpCode => $attribute) {
                $shopCode = $attribute['Code'];
                $attributeName = $this->stringStartsWith($mpCode, 'additional_attribute') ? $attribute['CustomName'] : $mpCode;
                $attributeValue = '';

                switch ($shopCode) {
                    case 'freetext':
                    case 'attribute_value':
                        if ((!empty($attribute['Values']) && !$limitResultSetToProductData)
                            || ($attribute['Values'] === '0' && !$limitResultSetToProductData) // allow string to be '0'
                        ) {
                            $attributeValue = $attribute['Values'];
                        }
                        break;
                    case 'database_value':
                        if (!empty($attribute['Values']) && !$limitResultSetToProductData) {
                            $attributeValue = $this->runDbMatching(array(
                                'Table' => array(
                                    'table' => $attribute['Values']['Table'],
                                    'column' => $attribute['Values']['Column'],
                                ),
                                'Alias' => $attribute['Values']['Alias'],
                                    ), 'products_id', $productData['ProductId']);
                        }
                        break;
                    case 'article_number':
                        $articleNumberKey = (getDBConfigValue('general.keytype', '0') == 'artNr') ? 'ProductsModel' : 'ProductId';
                        if (!empty($productData[$articleNumberKey])) {
                            $attributeValue = $productData[$articleNumberKey];
                        }
                        break;
                    case 'title':
                        if (!empty($productData['Title'])) {
                            $attributeValue = $productData['Title'];
                        }
                        break;
                    case 'description':
                        if (!empty($productData['Description'])) {
                            $attributeValue = $productData['Description'];
                        }
                        break;
                    case 'ean':
                        if (!empty($productData['EAN'])) {
                            $attributeValue = $productData['EAN'];
                        }

                        break;
                    case 'weight':
                        if (!empty($productData['Weight'])) {
                            $attributeValue = $productData['Weight']['Value'] . $productData['Weight']['Unit'];
                        }
                        break;
                    case 'weight_value':
                        if (!empty($productData['Weight'])) {
                            $attributeValue = $productData['Weight']['Value'];
                        }
                        break;
                    case 'weight_unit':
                        if (!empty($productData['Weight'])) {
                            $attributeValue = $productData['Weight']['Unit'];
                        }
                        break;
                    case 'contentvolume':
                    case 'content_volume':
                        if (!empty($productData['BasePrice'])) {
                            $attributeValue = $productData['BasePrice']['Value'] . $productData['BasePrice']['Unit'];
                        }
                        break;
                    case 'content_volume_unit':
                        if (!empty($productData['BasePrice'])) {
                            $attributeValue = $productData['BasePrice']['Unit'];
                        }
                        break;
                    case 'content_volume_value':
                        if (!empty($productData['BasePrice'])) {
                            $attributeValue = $productData['BasePrice']['Value'];
                        }
                        break;
                    case 'manufacturer':
                        if (!empty($productData['ManufacturerId'])) {
                            $attributeValue = $this->getMatchedValue($attribute, $productData['ManufacturerId']);
                        }
                        break;
                    case 'manufacturerpartnumber':
                        if (!empty($productData['ManufacturerPartNumber'])) {
                            $attributeValue = $productData['ManufacturerPartNumber'];
                        }
                        break;
                    case 'fsk_18':
                        if (isset($productData['IsFSK18'])) {
                            $attributeValue = $this->getMatchedValue($attribute, $productData['IsFSK18']);
                        }
                        break;
                    case 'brand_name':
                        if (!empty($productData['brand_name'])) {
                            $attributeValue = $productData['brand_name'];
                        }
                        break;
                    case 'shipping_cost':
                        if (!empty($productData['ShippingCost'])) {
                            $attributeValue = $productData['ShippingCost'];
                        }
                        break;
                    case 'delivery_time':
                        if (!empty($productData['ShippingTimeId'])) {
                            $attributeValue = $this->getMatchedValue($attribute, $productData['ShippingTimeId']);
                        }
                        break;
                    case 'minimum_order_quantity':
                        if (!empty($productData['MinOrderQuantity'])) {
                            $attributeValue = $productData['MinOrderQuantity'];
                        }
                        break;
                    case 'possible_amount_interval':
                        if (!empty($productData['PossibleAmountInterval'])) {
                            $attributeValue = $productData['PossibleAmountInterval'];
                        }
                        break;
                    default:
                        if (isset($productData[$shopCode])) {
                            $attributeValue = $this->getMatchedValue($attribute, $productData[$shopCode]);
                        } elseif (isset($productData["variant_{$shopCode}"])) {
                            $attributeValue = $this->getMatchedValue($attribute, $productData["variant_{$shopCode}"]);
                        }

                        break;
                }

                if (!empty($attributeValue) || $attributeValue === '0') {
                    $attributes[$attributeName] = $attributeValue;
                }
            }
        }

        return $attributes;
    }

    /**
     * Finds matching marketplace value out from matching attribute configuration
     *
     * @param array $attribute Matching attribute configuration from AM
     * @param string $shopKey Shop attribute key to search for inside matched attribute values
     *
     * @return string Matched marketplace value or empty string if no matching found
     */
    protected function getMatchedValue($attribute, $shopKey) {
        if (empty($attribute['Values']) || !is_array($attribute['Values'])) {
            return '';
        }

        $attributeValue = '';
        foreach ($attribute['Values'] as $value) {
            if ($shopKey == $value['Shop']['Key']) {
                if ($value['Marketplace']['Key'] === 'manual') {
                    $attributeValue = $value['Marketplace']['Value'];
                } else {
                    $attributeValue = $value['Marketplace']['Key'];
                }

                break;
            }
        }

        return $attributeValue;
    }

    /**
     * In case that multiple values are sent for shop and marketplace, that information will be json_encoded array.
     * Deserialization is done so that it can be properly saved to database.
     * @param $matchedAttribute
     */
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

    /**
     * Helper method to execute a db matching query.
     * @return mixed
     *   A string or false if the matching config is empty.
     */
    protected function runDbMatching($tableSettings, $defaultAlias, $where) {
        if (empty($tableSettings['Table']['table']) || empty($tableSettings['Table']['column'])) {
            return false;
        }

        if (    $tableSettings['Table']['table'] == TABLE_MANUFACTURERS
             || $tableSettings['Alias'] == 'manufacturers_id' ) {
            return $this->runManufacturersDbMatching($tableSettings, 'manufacturers_id', $where);
        }

        if (empty($tableSettings['Alias'])) {
            $tableSettings['Alias'] = $defaultAlias;
        }

        if (!is_numeric($where)) {
            $where = '"' . MagnaDB::gi()->escape($where) . '"';
        }

        $iResultCount = (int)MagnaDB::gi()->fetchOne('
            SELECT COUNT(DISTINCT ' . $tableSettings['Table']['column'] . ')
             FROM `' . $tableSettings['Table']['table'] . '`
            WHERE `' . $tableSettings['Alias'] . '` = ' . $where . '
              AND `' . $tableSettings['Table']['column'] . '` <> \'\'
            ');

        switch ($iResultCount) {
            case (0) : {
                return '';
            }
            case (1) : {
                return (string) MagnaDB::gi()->fetchOne('
                    SELECT DISTINCT `' . $tableSettings['Table']['column'] . '`
                     FROM `' . $tableSettings['Table']['table'] . '`
                    WHERE `' . $tableSettings['Alias'] . '` = ' . $where . '
                      AND `' . $tableSettings['Table']['column'] . '` <> \'\'
                    ');
            }
            default: {
                // language column is sometimes called language_id, sometimes languages_id
                if (MagnaDB::gi()->columnExistsInTable('language_id', $tableSettings['Table']['table'])) {
                    $sLanguageColumn = 'language_id';
                } else if (MagnaDB::gi()->columnExistsInTable('languages_id', $tableSettings['Table']['table'])) {
                    $sLanguageColumn = 'languages_id';
                } else {
                    return '';
                }
                if (getDBConfigValue($this->marketplace.'.lang' , $this->mpId, 0) != 0) {
                    return (string) MagnaDB::gi()->fetchOne('
                        SELECT DISTINCT `' . $tableSettings['Table']['column'] . '`
                         FROM `' . $tableSettings['Table']['table'] . '`
                        WHERE `' . $tableSettings['Alias'] . '` = ' . $where . '
                          AND `' . $tableSettings['Table']['column'] . '` <> \'\'
                          AND `'.$sLanguageColumn.'` = '.(int)getDBConfigValue($this->marketplace.'.lang' , $this->mpId, 0).'
                        LIMIT 1
                        ');
                } else {
                    return '';
                }
            }
        }
    }

     /**
     * Helper method to execute a db matching query for manufacturer's data.
     * (we get products_id as input, so we need to query for manufacturers_id first,
     *  and then proceed with it)
     * @return mixed
     *   A string or false if the matching config is empty.
     */
    protected function runManufacturersDbMatching($tableSettings, $defaultAlias, $where) {
        if (empty($tableSettings['Alias'])) {
            $tableSettings['Alias'] = $defaultAlias;
        }
        if (!is_numeric($where)) {
            $where = '"' . MagnaDB::gi()->escape($where) . '"';
        }  


        // We get products_id as input, so we must first detect the manufacturers_id of it
        $sManufacturersId = (int)MagnaDB::gi()->fetchOne('
            SELECT manufacturers_id
              FROM '.TABLE_PRODUCTS.'
             WHERE products_id = ' . $where
        );
        if (empty($sManufacturersId)) {
            return '';
        }

        $iResultCount = (int)MagnaDB::gi()->fetchOne('
            SELECT COUNT(DISTINCT ' . $tableSettings['Table']['column'] . ')
             FROM `' . $tableSettings['Table']['table'] . '`
            WHERE `manufacturers_id` = ' . $sManufacturersId . '
              AND `' . $tableSettings['Table']['column'] . '` <> \'\'
        ');

        switch ($iResultCount) {
            case (0) : {
                return '';
            }
            case (1) : {
                $sResult = (string) MagnaDB::gi()->fetchOne('
                    SELECT DISTINCT `' . $tableSettings['Table']['column'] . '`
                     FROM `' . $tableSettings['Table']['table'] . '`
                    WHERE `manufacturers_id` = ' . $sManufacturersId . '
                      AND `' . $tableSettings['Table']['column'] . '` <> \'\'
                    ');
                break; 
            }
            default: {
                // language column is sometimes called language_id, sometimes languages_id
                if (MagnaDB::gi()->columnExistsInTable('language_id', $tableSettings['Table']['table'])) {
                    $sLanguageColumn = 'language_id';
                } else if (MagnaDB::gi()->columnExistsInTable('languages_id', $tableSettings['Table']['table'])) {
                    $sLanguageColumn = 'languages_id';
                } else {
                    return '';
                }
                if (getDBConfigValue($this->marketplace.'.lang' , $this->mpId, 0) != 0) {
                    $sResult = (string) MagnaDB::gi()->fetchOne('
                        SELECT DISTINCT `' . $tableSettings['Table']['column'] . '`
                         FROM `' . $tableSettings['Table']['table'] . '`
                        WHERE `manufacturers_id` = ' . $sManufacturersId . '
                          AND `' . $tableSettings['Table']['column'] . '` <> \'\'
                          AND `'.$sLanguageColumn.'` = '.(int)getDBConfigValue($this->marketplace.'.lang' , $this->mpId, 0).'
                        LIMIT 1
                        ');
                } else {
                    return '';
                }
                break;
            }
        }
        // modified >= 3.1.3: If it's .._country_id, find the name
        if (    strpos($tableSettings['Table']['column'], 'country_id') !== false
             && is_numeric($sResult)) {
            if (!function_exists('xtc_get_country_name')) {
                if (file_exists(DIR_FS_CATALOG.'inc/xtc_get_country_name.inc.php')) {
                    require_once(DIR_FS_CATALOG.'inc/xtc_get_country_name.inc.php');
                }
            }
            if (function_exists('xtc_get_country_name')) {
                $sResult = xtc_get_country_name($sResult);
            }
        }
        return $sResult;
    }

    protected function fixHTMLUTF8Entities($code) {
        return fixHTMLUTF8Entities($code);
    }

    private function stringStartsWith($haystack, $needle) {
        return strpos($haystack, $needle) === 0;
    }

    protected function setMatchingTableTranslations() {
        return array(
            'mpTitle' => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_TITLE),
            'mpAttributeTitle' => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_ATTRIBUTE),
            'mpOptionalAttributeTitle' => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE),
            'mpCustomAttributeTitle' => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE),
        );
    }

    /*
     * If we have more than 256 variation values, see if we can constraint it
     * according to the products chosen
     */
    protected function checkNumberOfVariationValues(&$ShopVariation) {
        $aProducts = array();
        foreach ($ShopVariation as &$aAttr) {
            if (    !array_key_exists('CustomAttributeValue', $aAttr)
                 || !array_key_exists('Values', $aAttr)
                 || !is_array($aAttr['Values'])
                 || count($aAttr['Values']) <= $this->reasonableNumberOfVariationValues) {
                continue;
            }
            if ( array_key_exists('CustomName', $aAttr)
                 && strpos($aAttr['CustomName'], 'Varia' !== 0)) {
                // has to start with "Variations:" or "Varianten:"
                continue;
            }
            $iNameKey = $aAttr['CustomAttributeValue'];
            // check the products chosen, and the values they have
            if (empty($aProducts)) {
                $aProducts = MagnaDB::gi()->fetchArray('SELECT pID
                     FROM '.TABLE_MAGNA_SELECTION.'
                    WHERE mpID = '. $this->mpId .'
                      AND selectionname = \'prepare\'', true);
            } 
            $aUsedValues = array();
            if (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties') {
                foreach ($aProducts as $pID) {
                    $aUsedValues = array_merge($aUsedValues, 
                        MagnaDB::gi()->fetchArray('SELECT DISTINCT properties_values_id
                         FROM products_properties_index
                        WHERE products_id = '.$pID.'
                          AND properties_id = '.$iNameKey, true));
                }
            } else {
                foreach ($aProducts as $pID) {
                    $aUsedValues = array_merge($aUsedValues, 
                        MagnaDB::gi()->fetchArray('SELECT DISTINCT options_values_id
                         FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
                        WHERE products_id = '.$pID.'
                          AND options_id = '.$iNameKey, true));
                }
            }
            foreach ($aAttr['Values'] as $vKey => $vVal) {
                if (!in_array($vVal['Shop']['Key'], $aUsedValues)) {
                    unset($aAttr['Values'][ $vKey]);
                }
            }
            unset($aUsedValues);
        }
    }

}
