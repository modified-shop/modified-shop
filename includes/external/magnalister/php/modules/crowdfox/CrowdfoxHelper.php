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

	protected $numberOfMaxAdditionalAttributes = self::UNLIMITED_ADDITIONAL_ATTRIBUTES;

    private static $instance;

    public static function gi() {
        if (self::$instance === null) {
            self::$instance = new CrowdfoxHelper();
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

	protected function isProductPrepared($category, $prepare = false)
	{
        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sSQLAnd = ' AND products_model = "'.$prepare.'"';
        } else {
            $sSQLAnd = ' AND products_id = "'. $prepare . '"';
        }

		if ($prepare) {
			$productsId = MagnaDB::gi()->fetchOne(eecho('
                SELECT products_id
                FROM ' . TABLE_MAGNA_CROWDFOX_PREPARE . '
                WHERE MpId = ' . $this->mpId . '
                    ' . $sSQLAnd . '
                LIMIT 1
            '));

			return !empty($productsId);
		}

		return false;
	}

    public function getMPVariations($category, $prepare = false, $getDate = false, $additionalData = null, $customIdentifier = '') {
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

        $attributes = array();

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
					$attributes[$utf8Code]['IsDeletedOnShop'] = $this->detectIfAttributeIsDeletedOnShop($shopAttributes,
						$value, $attributes[$utf8Code]['WarningMessage']);
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

			return array(
				'Attributes' => $attributes,
				'ModificationDate' => $modificationDate,
                'DifferentProducts' => $hasDifferentlyPreparedProducts,
				'variation_theme_code' => $this->getSavedVariationThemeCode($category, $prepare),
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
        if (!$prepare) {
            return false;
        }

        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sSQLAnd = ' AND products_model = "'.$prepare.'"';
        } else {
            $sSQLAnd = ' AND products_id = "'. $prepare . '"';
        }

        $availableCustomConfigs = false;
        if ($prepare) {
            $availableCustomConfigs = MagnaDB::gi()->fetchOne(eecho('
				SELECT DISTINCT ShopVariation
				FROM ' . TABLE_MAGNA_CROWDFOX_PREPARE . '
				WHERE MpId = ' . $this->mpId . '
					' . $sSQLAnd . '
			'));
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
				SELECT `ShopVariation`
				FROM ' . TABLE_MAGNA_CROWDFOX_PREPARE . '
				WHERE mpID = ' . $this->mpId . '
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

    public function renderMatchingTable($url, $categoryOptions, $addCategoryPick = true, $displayCategory = true, $customIdentifierHtml = '') {
        // Crowdfox does not have categories.
        return parent::renderMatchingTable($url, $categoryOptions, $addCategoryPick, false, $customIdentifierHtml);
    }
}
