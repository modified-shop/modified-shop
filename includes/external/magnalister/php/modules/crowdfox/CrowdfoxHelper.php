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

require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/AttributesMatchingHelper.php');

class CrowdfoxHelper extends AttributesMatchingHelper {

    public static $TITLE_MAX_LENGTH = 255;
    public static $DESC_MAX_LENGTH = 5000;
    public static $MAX_NUMBER_OF_IMAGES = 4;

    private static $instance;

    public static function gi() {
        if (self::$instance === null) {
            self::$instance = new CrowdfoxHelper();
            self::$instance->numberOfMaxAdditionalAttributes = -1;
        }

        return self::$instance;
    }

    public static function loadPriceSettings($mpId) {
        $mp = magnaGetMarketplaceByID($mpId);

        $currency = getCurrencyFromMarketplace($mpId);
        $convertCurrency = getDBConfigValue(array($mp . '.exchangerate', 'update'), $mpId, false);

        $config = array(
            'Price' => array(
                'AddKind' => getDBConfigValue($mp . '.price.addkind', $mpId, 'percent'),
                'Factor' => (float)getDBConfigValue($mp . '.price.factor', $mpId, 0),
                'Signal' => getDBConfigValue($mp . '.price.signal', $mpId, ''),
                'Group' => getDBConfigValue($mp . '.price.group', $mpId, ''),
                'UseSpecialOffer' => getDBConfigValue(array($mp . '.price.usespecialoffer', 'val'), $mpId, false),
                'Currency' => $currency,
                'ConvertCurrency' => $convertCurrency,
            ),
            'PurchasePrice' => array(
                'AddKind' => getDBConfigValue($mp . '.purchaseprice.addkind', $mpId, 'percent'),
                'Factor' => (float)getDBConfigValue($mp . '.purchaseprice.factor', $mpId, 0),
                'Signal' => getDBConfigValue($mp . '.purchaseprice.signal', $mpId, ''),
                'Group' => getDBConfigValue($mp . '.purchaseprice.group', $mpId, ''),
                'UseSpecialOffer' => false,
                'Currency' => $currency,
                'ConvertCurrency' => $convertCurrency,
                'IncludeTax' => false,
            ),
        );

        return $config;
    }

    public static function loadQuantitySettings($mpId) {
        $mp = magnaGetMarketplaceByID($mpId);

        $config = array(
            'Type' => getDBConfigValue($mp . '.quantity.type', $mpId, 'lump'),
            'Value' => (int)getDBConfigValue($mp . '.quantity.value', $mpId, 0),
            'MaxQuantity' => (int)getDBConfigValue($mp . '.quantity.maxquantity', $mpId, 0),
        );

        return $config;
    }

    public static function processCheckinErrors($result, $mpID) {
        if (array_key_exists('ERRORS', $result) && is_array($result['ERRORS']) && !empty($result['ERRORS'])) {
            foreach ($result['ERRORS'] as $err) {
                $ad = array();
                if (isset($err['ERRORDATA']['SKU'])) {
                    $ad['SKU'] = $err['ERRORDATA']['SKU'];
                }
                $err = array(
                    'mpID' => $mpID,
                    'errormessage' => $err['ERRORMESSAGE'],
                    'dateadded' => gmdate('Y-m-d H:i:s'),
                    'additionaldata' => serialize($ad),
                );
                MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, $err);
            }
        }
    }

    public static function GetShippingMethods() {
        $shippingMethods = self::submitSessionCachedRequest('GetShippingMethod');
        array_unshift($shippingMethods, ML_GENERAL_VARMATCH_PLEASE_SELECT);

        return $shippingMethods;
    }

    public static function GetShippingMethodsConfig(&$types) {
        $types['values'] = self::GetShippingMethods();
    }

    public static function GetWeightFromShop($itemId) {
        $result = MagnaDB::gi()->fetchOne('
			SELECT products_weight
			FROM ' . TABLE_PRODUCTS . '
			WHERE products_id = "' . $itemId . '"
		');

        if ($result && (int)$result > 0) {
            $weight = round($result, 2);

            return $weight . 'kg';
        }

        return '';
    }

    public static function GetContentVolumeFromShop($itemId) {
        $result = MagnaDB::gi()->fetchRow('
			SELECT p.products_vpe_value AS vpe, pvpe.products_vpe_name AS sufix
			FROM ' . TABLE_PRODUCTS . ' p, ' . TABLE_PRODUCTS_VPE . ' pvpe
			WHERE p.products_id = "' . $itemId . '"
				AND pvpe.products_vpe_id = p.products_vpe
		');
        if ($result && (int)$result > 0) {
            $factor = array();
            if (preg_match('/^([0-9][0-9,.]*)/', $result['sufix'], $factor)) {
                $factor = mlFloatalize($factor[1]);
                $contentValue = round($result['vpe'] * $factor, 2);
                $result['sufix'] = trim(preg_replace('/^[0-9][0-9,.]*/', '', $result['sufix']));
            } else {
                $contentValue = round($result['vpe'], 2);
            }

            return $contentValue . $result['sufix'];
        }

        return '';
    }

    public static function getTitleDescriptionEan(&$selection, $mpID, $changeGTIN = true) {
        global $_MagnaSession;
        $marketplace = $_MagnaSession['currentPlatform'];

        $selection[0]['ItemTitle'] = CrowdfoxHelper::sanitizeTitle($selection[0]['ItemTitle'], self::$TITLE_MAX_LENGTH);
        $selection[0]['Description'] = CrowdfoxHelper::sanitizeDescription($selection[0]['Description'], self::$DESC_MAX_LENGTH);
        $selection[0]['Description'] = str_replace("\r", ' ', $selection[0]['Description']);
        $selection[0]['Description'] = str_replace("\n", ' ', $selection[0]['Description']);

        if ($changeGTIN) {
            $gtinColumnConfigTable = getDBConfigValue($marketplace . '.prepare.gtincolumn.dbmatching.table', $mpID);
            $gtinColumnConfigAlias = getDBConfigValue($marketplace . '.prepare.gtincolumn.dbmatching.alias', $mpID);
            $gtinColumnConfigAlias = empty($gtinColumnConfigAlias) ? 'products_id' : $gtinColumnConfigAlias;

            $selection[0]['GTIN'] = self::getDataFromConfig($selection[0]['products_id'], $gtinColumnConfigTable,
                $gtinColumnConfigAlias);
        }
    }

    public static function getDataFromConfig($productID, $table, $alias) {
        if (!isset($table['table']) || empty($table['table']) || empty($table['column'])) {
            return false;
        }

        if (empty($alias)) {
            $alias = $table['column'];
        }

        return (string)MagnaDB::gi()->fetchOne('
			SELECT `' . $table['column'] . '` 
			FROM `' . $table['table'] . '` 
			WHERE `' . $alias . '` = ' . MagnaDB::gi()->escape($productID) . '
				AND `' . $table['column'] . '` <> \'\'
		');
    }

    public static function getManufacturerPartNumber($product_id, $marketplace, $mpId) {
        $mfrmd = getDBConfigValue($marketplace . '.checkin.manufacturerpartnumber.table', $mpId, false);
        if (is_array($mfrmd) && !empty($mfrmd['column']) && !empty($mfrmd['table'])) {
            $mfrmd['alias'] = getDBConfigValue($marketplace . '.checkin.manufacturerpartnumber.alias', $mpId);
            if (empty($mfrmd['alias'])) {
                $mfrmd['alias'] = 'products_id';
            }
        } else {
            $mfrmd['alias'] = 'products_id';
            $mfrmd['column'] = 'products_model';
            $mfrmd['table'] = TABLE_PRODUCTS;
        }

        return self::getDataFromConfig($product_id, $mfrmd, $mfrmd['alias']);
    }

    public function getMPVariations($category, $prepare = false, $getDate = false, $customIdentifier = '') {
        $dbData = $this->getPreparedData($category, $prepare);
        $tableName = $this->getVariationMatchingTableName();

        // load default values from Variation Matching tab (global matching)
        $usedGlobal = false;
        $globalMatching = $this->getCategoryMatching($category);

        if ($dbData === false) {
            $dbData = $globalMatching;
            $usedGlobal = true;
        }

        $attributes = array();
        $numberOfAdditionalAttributes = $this->getNumberOfMaxAdditionalAttributes();

        if ($numberOfAdditionalAttributes > 0 || $numberOfAdditionalAttributes === -1) {
            $this->addAdditionalAttributesMP($attributes, $dbData);
        }

        $hasDifferentlyPreparedProducts = false;
        if (!$usedGlobal && !empty($globalMatching)) {
            $this->detectChanges($globalMatching, $attributes);
        } else if (!$prepare && !empty($globalMatching)) {
            // on variation matching tab. Check whether some products are prepared differently
            $hasDifferentlyPreparedProducts = $this->areProductsDifferentlyPrepared($category, $globalMatching);
        }

        // if there are saved values but they were removed from Marketplace, display warning to user
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

        if ($getDate) {
            return array(
                'Attributes' => $attributes,
                'ModificationDate' => MagnaDB::gi()->fetchOne(eecho('
                    SELECT ModificationDate
                    FROM ' . $tableName . '
                    WHERE MpId = ' . $this->mpId . '
                        AND MpIdentifier = "' . $category . '"
                ', false)),
                'DifferentProducts' => $hasDifferentlyPreparedProducts,
            );
        }

        return $attributes;
    }

    /**
     * Truncates HTML text without breaking HTML structure.
     *
     * @param string $text String to truncate.
     * @param integer $length Length of returned string, including ellipsis.
     *
     * @return string Trimmed string.
     */
    public static function truncateString($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
        if (strlen($text) <= $length) {
            return $text;
        }

        $textLength = min($length, strlen(preg_replace('/<.*?>/', '', $text)));
        $resultText = parent::truncateString($text, $textLength);
        while (strlen($resultText) > $length) {
            $textLength -= 100;
            $resultText = parent::truncateString($text, $textLength);
        }

        return $resultText;
    }

    private static function submitSessionCachedRequest($action) {
        global $_MagnaSession;
        $mpID = $_MagnaSession['mpID'];
        $data = array(
            'DATA' => false,
        );

        if (isset($_MagnaSession[$mpID][$action])) {
            return $_MagnaSession[$mpID][$action];
        }

        try {
            $data = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => $action,
            ));
        } catch (MagnaException $e) {
        }

        if (!is_array($data) || !isset($data['DATA'])) {
            return false;
        }

        $_MagnaSession[$mpID][$action] = $data['DATA'];

        return $_MagnaSession[$mpID][$action];
    }

    /**
     * @param $category
     * @param bool|array $prepare
     * @param string $customIdentifier
     * @return bool|mixed
     */
    protected function getPreparedData($category, $prepare = false, $customIdentifier = '') {
        $availableCustomConfigs = false;
        if ($prepare) {
            $availableCustomConfigs = MagnaDB::gi()->fetchOne(eecho('
				SELECT DISTINCT ShopVariation
				FROM ' . TABLE_MAGNA_CROWDFOX_PREPARE . '
				WHERE MpId = ' . $this->mpId . '
					AND products_model IN("' . implode('", "', $prepare) . '")', false));
        }

        return $availableCustomConfigs ? json_decode($availableCustomConfigs, true) : false;
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
				SELECT `CategoryAttributes`
				FROM ' . TABLE_MAGNA_CROWDFOX_PREPARE . '
				WHERE mpID = ' . $this->mpId . '
					AND MarketplaceCategories = "' . $category . '"
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

    public function getProductModel($selectionName) {
        $pIDs = MagnaDB::gi()->fetchArray('
             SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
             WHERE mpID=\'' . $this->mpId . '\' AND
                  selectionname=\'' . $selectionName . '\' AND
                  session_id=\'' . session_id() . '\'
        ', true);

        $productModels = MagnaDB::gi()->fetchArray('
            SELECT products_model
            FROM ' . TABLE_PRODUCTS . '
            WHERE products_id IN("' . implode('", "', $pIDs) . '")
        ', true);

        if ($productModels) {
            return $productModels;
        }

        return false;
    }

    public function renderMatchingTable($url, $categoryOptions, $addCategoryPick = true, $customIdentifierHtml = '') {
        $mpTitle = str_replace('%marketplace%', 'Crowdfox', ML_GENERIC_MP_CATEGORY);
        $mpAttributeTitle = str_replace('%marketplace%', 'Crowdfox', ML_GENERAL_VARMATCH_MP_ATTRIBUTE);
        $mpOptionalAttributeTitle = str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE);
        $mpCustomAttributeTitle = str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE);

        ob_start();
        ?>
        <form method="post" id="matchingForm" action="<?php echo toURL($url, array(), true); ?>">
            <table id="variationMatcher" class="attributesTable">
                <tbody style="display: none">
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
                                               value="<?php echo ML_GENERIC_CATEGORIES_CHOOSE ?>"
                                               id="selectPrimaryCategory"/>
                                    </td>
                                <?php } ?>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td class="info"></td>
                </tr>
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
                <tbody id="tbodyDynamicMatchingCustomHeadline" style="display:none;">
                <tr class="headline">
                    <td colspan="1"><h4><?php echo $mpCustomAttributeTitle ?></h4></td>
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
