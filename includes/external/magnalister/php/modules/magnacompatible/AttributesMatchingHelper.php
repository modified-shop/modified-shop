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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/MagnaCompatibleHelper.php');

class AttributesMatchingHelper extends MagnaCompatibleHelper
{
    protected $numberOfMaxAdditionalAttributes = 0;
    protected $mpId;
    protected $marketplace;
    protected $defaultLanguage;

    public function __construct()
    {
        global $_MagnaSession;
        $this->mpId = $_MagnaSession['mpID'];
        $this->marketplace = $_MagnaSession['currentPlatform'];
        $this->defaultLanguage = $_SESSION['languages_id'];
    }

    public function getShopVariations()
    {
        $languageId = getDBConfigValue($this->marketplace . '.lang', $this->mpId, $this->defaultLanguage);

        if (defined('TABLE_PRODUCTS_OPTIONS') && MagnaDB::gi()->tableExists(TABLE_PRODUCTS_OPTIONS)) {
            $defaultGroupsOptions = $this->getVariationGroupsOptions($this->defaultLanguage);
            $groupsOptions = $this->getVariationGroupsOptions($languageId);

            if (empty($groupsOptions)) {
                $groupsOptions = $defaultGroupsOptions;
            }

            if (!empty($groupsOptions)) {
                foreach ($groupsOptions as $k => &$g) {
                    if (empty($g['Name'])) {
                        foreach ($defaultGroupsOptions as $dg) {
                            if ($dg['Code'] === $g['Code']) {
                                $g['Name'] = $dg['Name'];
                                break;
                            }
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
                }
            } else {
                $groupsOptions = array();
            }
        } else {
            $groupsOptions = array();
        }

        if (defined('TABLE_MAGNA_PROPERTIES_DESCRIPTION') && MagnaDB::gi()->tableExists(TABLE_MAGNA_PROPERTIES_DESCRIPTION)) {
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
                }
            } else {
                $groupsProperties = array();
            }
        } else {
            $groupsProperties = array();
        }

        $groups = array_merge($groupsOptions, $groupsProperties);

        $this->addAdditionalAttributesShop($groups);

        arrayEntitiesToUTF8($groups);
        $aOut = array();
        foreach ($groups as $aGroup) {
            if (!isset($aGroup['Disabled'])) {
                $aGroup['Disabled'] = '';
            }

            if (!isset($aGroup['Custom'])) {
                $aGroup['Custom'] = '';
            }

            $aOut[$aGroup['Code']] = $aGroup;
        }

        return $aOut;
    }

    /**
     * @return int
     */
    public function getNumberOfMaxAdditionalAttributes()
    {
        return $this->numberOfMaxAdditionalAttributes;
    }

    protected function getAttributesFromMP($category, $customIdentifier = '')
    {
        return array();
    }

    /**
     * Gets attributes from prepare table for specified product
     *
     * @param $category
     * @param mixed $prepare ID or SKU of a product
     * @return mixed FALSE if prepare for specified product does not exist; NULL if Attributes are empty; array of attributes
     */
    protected function getPreparedData($category, $prepare = false, $customIdentifier = '')
    {
        return null;
    }

    protected function addAdditionalAttributesShop(&$groups)
    {
        $tables = MagnaDB::gi()->getAvailableTables();
        $editedTables = array();
        foreach ($tables as $table) {
            $editedTables[$table] = $table;
        }

        $groups = array_merge($groups, array(
            array(
                'Code' => 'separator_line',
                'Name' => ML_GENERAL_VARMATCH_SEPARATOR_LINE,
                'Values' => array(),
                'Disabled' => 'disabled',
            ),
            array(
                'Code' => 'title',
                'Name' => ML_COMPARISON_SHOPPING_FIELD_ITEM_TITLE,
                'Values' => array(),
                'Custom' => true,
            ),
            array(
                'Code' => 'description',
                'Name' => ML_COMPARISON_SHOPPING_FIELD_DESCRIPTION,
                'Values' => array(),
                'Custom' => true,
            ),
            array(
                'Code' => 'ean',
                'Name' => ML_COMPARISON_SHOPPING_FIELD_EAN,
                'Values' => array(),
                'Custom' => true,
            ),
            array(
                'Code' => 'weight',
                'Name' => ML_HITMEISTER_WEIGHT,
                'Values' => array(),
                'Custom' => true,
            ),
            array(
                'Code' => 'contentvolume',
                'Name' => ML_HITMEISTER_VPE,
                'Values' => array(),
                'Custom' => true,
            ),
            array(
                'Code' => 'separator_line2',
                'Name' => ML_GENERAL_VARMATCH_SEPARATOR_LINE,
                'Values' => array(),
                'Disabled' => 'disabled',
            ),
            array(
                'Code' => 'freetext',
                'Name' => ML_GENERAL_VARMATCH_FREE_TEXT_LABEL,
                'Values' => array(),
            ),
            array(
                'Code' => 'attribute_value',
                'Name' => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_CHOOSE_MP_VALUE),
                'Values' => array(),
            ),
            array(
                'Code' => 'separator_line3',
                'Name' => ML_GENERAL_VARMATCH_SEPARATOR_LINE,
                'Values' => array(),
                'Disabled' => 'disabled',
            ),
            array(
                'Code' => 'database_value',
                'Name' => ML_GENERAL_VARMATCH_CHOOSE_DB_VALUE,
                'Values' => $editedTables,
            ),
        ));
    }

    protected function getVariationMatchingTableName()
    {
        return 'magnalister_' . $this->marketplace . '_variantmatching';
    }

    public function getCategoryMatching($category, $customIdentifier = '')
    {
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

    public function getCustomIdentifiers($category, $prepare = false, $getDate = false)
    {
        return array();
    }

    public function getMPVariations($category, $prepare = false, $getDate = false, $customIdentifier = '')
    {
        $mpData = $this->getAttributesFromMP($category, $customIdentifier);
        $dbData = $this->getPreparedData($category, $prepare, $customIdentifier);
        $tableName = $this->getVariationMatchingTableName();

        // load default values from Variation Matching tab (global matching)
        $usedGlobal = false;
        $globalMatching = $this->getCategoryMatching($category, $customIdentifier);

        if (empty($dbData)) {
            $dbData = $globalMatching;
            $usedGlobal = true;
        }

        arrayEntitiesToUTF8($mpData);
        $attributes = array();
        foreach ($mpData['attributes'] as $code => $value) {
            $attributes[$code] = array(
                'AttributeCode' => $code,
                'AttributeName' => $value['title'],
                'AllowedValues' => isset($value['values']) ? $value['values'] : array(),
                'AttributeDescription' => isset($value['desc']) ? $value['desc'] : '',
                'CurrentValues' => isset($dbData[$code]) ? $dbData[$code] : array('Values' => array()),
                'ChangeDate' => isset($value['changed']) ? $value['changed'] : false,
                'Required' => isset($value['mandatory']) ? $value['mandatory'] : false,
                'DataType' => isset($value['type']) ? $value['type'] : 'text',
            );

            if (isset($dbData[$code])) {
                if (!isset($dbData[$code]['Required'])) {
                    $dbData[$code]['Required'] = isset($value['mandatory']) ? $value['mandatory'] : true;
                    $dbData[$code]['Code'] = !empty($value['values']) ? 'attribute_value' : 'freetext';
                    $dbData[$code]['AttributeName'] = $value['title'];
                }

                $attributes[$code]['CurrentValues'] = $dbData[$code];
            }
        }

        $numberOfAdditionalAttributes = $this->getNumberOfMaxAdditionalAttributes();

        if ($numberOfAdditionalAttributes > 0 || $numberOfAdditionalAttributes === -1) {
            $this->addAdditionalAttributesMP($attributes, $dbData);
        }

        $hasDifferentlyPreparedProducts = false;
        if (!$usedGlobal && !empty($globalMatching)) {
            $this->detectChanges($globalMatching, $attributes);
        } else if (!$prepare && !empty($globalMatching)) {
            // on variation matching tab. Check whether some products are prepared differently
            $hasDifferentlyPreparedProducts = $this->areProductsDifferentlyPrepared($category, $globalMatching, $customIdentifier);
        }

        // if there are saved values but they were removed from Marketplace, display warning to user
        if (is_array($dbData)) {
            foreach ($dbData as $code => $value) {
                if (!isset($attributes[$code]) && strpos($code, 'additional_attribute_') === false) {
                    $attributes[$code] = array(
                        'Deleted' => true,
                        'AttributeCode' => $code,
                        'AttributeName' => !empty($value['AttributeName']) ? $value['AttributeName'] : $code,
                        'AllowedValues' => array(),
                        'AttributeDescription' => '',
                        'CurrentValues' => array('Values' => array()),
                        'ChangeDate' => '',
                        'Required' => isset($value['mandatory']) ? $value['mandatory'] : false,
                    );
                }
            }
        }

        if ($getDate) {
            return array(
                'Attributes' => $attributes,
                'ModificationDate' => MagnaDB::gi()->fetchOne(eecho('
                    SELECT ModificationDate
                    FROM ' . $tableName . '
                    WHERE MpId = ' . $this->mpId . '
                        AND MpIdentifier = "' . $category . '"
                        AND CustomIdentifier = "' . $customIdentifier . '"
                ', false)),
                'DifferentProducts' => $hasDifferentlyPreparedProducts,
            );
        }

        return $attributes;
    }

    protected function addAdditionalAttributesMP(&$attributes, $aResultFromDB)
    {
        $additionalAttributes = array();
        $newAdditionalAttributeIndex = 0;
        $positionOfIndexInAdditionalAttribute = 2;

        if ($aResultFromDB) {
            foreach ($aResultFromDB as $key => $value) {
                if (strpos($key, 'additional_attribute_') === 0) {
                    $additionalAttributes[$key] = $value;
                    $keyParts = explode('_', $key);
                    $additionalAttributeIndex = (int)$keyParts[$positionOfIndexInAdditionalAttribute];
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
                'CurrentValues' => isset($aResultFromDB[$attributeKey]) ?
                    $aResultFromDB[$attributeKey] : array('Values' => array()),
                'ChangeDate' => '',
                'Required' => false,
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
    public function detectChanges($globalMatching, &$productMatching)
    {
        if (empty($globalMatching) && empty($productMatching)) {
            return false;
        }

        if ((empty($globalMatching) && !empty($productMatching))
            || (!empty($globalMatching) && empty($productMatching))
        ) {
            return true;
        }

        $different = false;
        foreach ($globalMatching as $attributeCode => $attributeSettings) {
            if (!empty($productMatching[$attributeCode])) {
                $productAttrs = isset($productMatching[$attributeCode]['CurrentValues']) ? $productMatching[$attributeCode]['CurrentValues'] : $productMatching[$attributeCode];
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
                        unset($attribute['Marketplace']['Info']);
                        $found = false;
                        foreach ($attributeSettingsValues as $value) {
                            unset($value['Marketplace']['Info']);
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
    protected function areProductsDifferentlyPrepared($category, $globalMatching, $customIdentifier = '')
    {
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

    /**
     * Gets prepared attributes data for products prepared for given category.
     *
     * @param string $category
     * @param string $customIdentifier
     * @return array|null
     */
    protected function getPreparedProductsData($category, $customIdentifier = '')
    {
        return null;
    }

    public function renderMatchingTable($url, $categoryOptions, $addCategoryPick = true, $customIdentifierHtml = '')
    {
        $mpTitle = str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_TITLE);
        $mpAttributeTitle = str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_ATTRIBUTE);
        $mpOptionalAttributeTitle = str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE);

        ob_start();
        ?>
        <form method="post" id="matchingForm" action="<?php echo toURL($url, array(), true); ?>">
            <table id="variationMatcher" class="attributesTable">
                <tbody>
                <tr class="headline">
                    <td colspan="3"><h4><?php echo $mpTitle ?></h4></td>
                </tr>
                <tr id="mpVariationSelector">
                    <th><?php echo ML_LABEL_MAINCATEGORY ?></th>
                    <td class="input">
                        <table class="inner middle fullwidth categorySelect">
                            <tbody>
                            <tr>
                                <td>
                                    <div class="hoodCatVisual" id="PrimaryCategoryVisual">
                                        <select id="PrimaryCategory" name="PrimaryCategory" style="width:100%">
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
                <?php if (!empty($customIdentifierHtml)) {
                    echo $customIdentifierHtml;
                } ?>
                <tr class="spacer">
                    <td colspan="3">&nbsp;</td>
                </tr>
                </tbody>
                <tbody id="tbodyDynamicMatchingHeadline" style="display:none;">
                <tr class="headline">
                    <td colspan="1"><h4><?php echo $mpAttributeTitle ?></h4></td>
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
                    <td colspan="1"><h4><?php echo $mpOptionalAttributeTitle ?></h4></td>
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

    protected function autoMatch($categoryId, $sMPAttributeCode, &$aAttributes, $customIdentifier = '')
    {
        $mpVariations = $this->getMPVariations($categoryId, false, false, $customIdentifier);
        $aMPAttributeValues = $mpVariations[$sMPAttributeCode]['AllowedValues'];

        $sVariations = $this->getShopVariations();
        $sAttributeValues = $sVariations[$aAttributes['Code']]['Values'];

        if (empty($aMPAttributeValues)) {
            foreach ($sAttributeValues as $sShopValue) {
                $aMPAttributeValues[$sShopValue] = $sShopValue;
            }
        }

        $sInfo = ML_GENERAL_VARMATCH_AUTO_MATCHED;
        $blFound = false;
        $allValuesAreMatched = true;
        if ($aAttributes['Values']['0']['Shop']['Key'] === 'all') {
            $newValue = array();
            $i = 0;
            foreach ($sAttributeValues as $keyAttribute => $valueAttribute) {
                foreach ($aMPAttributeValues as $key => $value) {
                    if (strcasecmp($valueAttribute, $value) == 0) {
                        $newValue[$i]['Shop']['Key'] = $keyAttribute;
                        $newValue[$i]['Shop']['Value'] = $valueAttribute;
                        $newValue[$i]['Marketplace']['Key'] = $key;
                        $newValue[$i]['Marketplace']['Value'] = $value;
                        $newValue[$i]['Marketplace']['Info'] = $value . $sInfo;
                        $blFound = true;
                        $i++;
                        break;
                    }
                }
            }

            $aAttributes['Values'] = $newValue;
            if (count($sAttributeValues) !== count($newValue)) {
                $allValuesAreMatched = false;
            }
        } else {
            foreach ($aMPAttributeValues as $key => $value) {
                if (strcasecmp($aAttributes['Values']['0']['Shop']['Value'], $value) == 0) {
                    $aAttributes['Values']['0']['Marketplace']['Key'] = $key;
                    $aAttributes['Values']['0']['Marketplace']['Value'] = $value;
                    $aAttributes['Values']['0']['Marketplace']['Info'] = $value . $sInfo;
                    $blFound = true;
                    break;
                }
            }

            if (!$blFound) {
                $allValuesAreMatched = false;
            }
        }

        if (!$blFound) {
            unset($aAttributes['Values']['0']);
        }

        $this->checkNewMatchedCombination($aAttributes['Values']);

        return $allValuesAreMatched;
    }

    public function checkNewMatchedCombination(&$attributes)
    {
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

    public function getMPAttributeValues($sAttributeCode = false)
    {
        if ($sAttributeCode) {
            $mpVariations = $this->getShopVariations();
            return $mpVariations[$sAttributeCode]['Values'];
        }

        return array();
    }

    /**
     * @param string $category
     * @param array $matching
     * @param bool $savePrepare
     * @param bool $fromPrepare
     * @param string $sCustomIdentifier
     * @return array
     */
    public function saveMatching($category, &$matching, $savePrepare, $fromPrepare = false, $sCustomIdentifier = '')
    {
        if (!$matching) {
            return array();
        }

        $tableName = $this->getVariationMatchingTableName();
        $errors = array();
        $addNotAllValuesMatchedNotice = false;

        foreach ($matching['ShopVariation'] as $key => &$value) {
            if (isset($value['Required'])) {
                $value['Required'] = (bool)$value['Required'];
            } else {
                $value['Required'] = false;
            }

            $sAttributeName = $value['AttributeName'];
            $value['Error'] = false;

            if ($value['Code'] == 'null' || !isset($value['Values']) || empty($value['Values'])) {
                if (isset($value['Required']) && $value['Required'] == true) {
                    $errors[] = str_replace('%attribute_name%', $sAttributeName, ML_GENERAL_VARMATCH_ERROR_MESSAGE_REQUIRED);
                    if ($savePrepare) {
                        $value['Error'] = true;
                    }
                } else {
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
                if (empty($value['Values']) && $value['Required'] == true) {
                    $value['Error'] = true;
                    $errors[] = str_replace('%attribute_name%', $sAttributeName, ML_GENERAL_VARMATCH_ERROR_MESSAGE_REQUIRED);
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
                    $errors[] = $sAttributeName . ML_GENERAL_VARMATCH_ERROR_MESSAGE_FREE_TEXT;
                    $value['Error'] = true;
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
                $mpVariations = $this->getShopVariations();
                foreach ($mpVariations[$value['Code']]['Values'] as $keyAttribute => $valueAttribute) {
                    $newValue[$i]['Shop']['Key'] = $keyAttribute;
                    $newValue[$i]['Shop']['Value'] = $valueAttribute;
                    $newValue[$i]['Marketplace']['Key'] = $value['Values']['0']['Marketplace']['Key'];
                    $newValue[$i]['Marketplace']['Value'] = $value['Values']['0']['Marketplace']['Value'];
                    $newValue[$i]['Marketplace']['Info'] = $value['Values']['0']['Marketplace']['Value'] . $sInfo;
                    $i++;
                }

                $value['Values'] = $newValue;
            } else {
                foreach ($value['Values'] as $k => &$v) {
                    if (empty($v['Marketplace']['Info'])) {
                        $v['Marketplace']['Info'] = $v['Marketplace']['Value'] . $sInfo;
                    }
                }
            }
        }

        arrayEntitiesToUTF8($matching['ShopVariation']);

        if (!$fromPrepare || !MagnaDB::gi()->recordExists($tableName, array('MpIdentifier' => $category, 'CustomIdentifier' => $sCustomIdentifier)) && $savePrepare) {
            MagnaDB::gi()->insert($tableName, array(
                'MpId' => $this->mpId,
                'MpIdentifier' => $category,
                'CustomIdentifier' => $sCustomIdentifier,
                'ShopVariation' => json_encode($matching['ShopVariation']),
                'IsValid' => isset($matching['IsValid']) && $matching['IsValid'] === 'false' ? false : true,
                'ModificationDate' => date('Y-m-d H:i:s'),
            ), true);
        }

        if (!$fromPrepare && !empty($addNotAllValuesMatchedNotice)) {
            array_unshift($errors, array(
                'type' => 'notice',
                'additionalCssClass' => 'notAllAttributeValuesMatched',
                'message' => ML_GENERAL_VARMATCH_NOTICE_NOT_ALL_AUTO_MATCHED,
            ));
        }

        return $errors;
    }

    public function getVarMatchTranslations()
    {
        return array (
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
            'attributeChangedOnMp' => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_ATTRIBUTE_CHANGED_ON_MP),
            'attributeDifferentOnProduct' => ML_GENERAL_VARMATCH_ATTRIBUTE_DIFFERENT_ON_PRODUCT,
            'attributeDeletedOnMp' => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_ATTRIBUTE_DELETED_ON_MP),
            'attributeValueDeletedOnMp' => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_ATTRIBUTE_VALUE_DELETED_ON_MP),
            'categoryWithoutAttributesInfo' => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_CATEGORY_WITHOUT_ATTRIBUTES_INFO),
            'differentAttributesOnProducts' => ML_GENERAL_VARMATCH_PRODUCTS_PREPARED_DIFFERENTLY,
            'mandatoryInfo' => ML_CDISCOUNT_VARMATCH_MANDATORY_INFO,
            'alreadyMatched' => ML_GENERAL_VARMATCH_ALREADY_MATCHED,
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
                WHERE products_id = ' . $pID
            );
        } else {
            $productModel = $pID;
        }

        if ($productModel) {
            return $productModel;
        }

        return false;
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
        return MagnaDB::gi()->fetchArray('
            SELECT pov.properties_values_id Id, pov.values_name AS Value
            FROM ' . TABLE_MAGNA_PROPERTIES_DESCRIPTION_VALUES . ' pov
            INNER JOIN ' . TABLE_MAGNA_PROPERTIES_VALUES . ' ov2po ON
                    ov2po.properties_values_id = pov.properties_values_id
                AND ov2po.properties_id = "' . $propertyCode . '"
            WHERE pov.language_id = "' . $languageId . '"
            ORDER BY pov.values_name ASC
        ');
    }
}
