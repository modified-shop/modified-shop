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
 *
 * (c) 2010 - 2025 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC_MODULE_CALL') or defined('_VALID_XTC') or defined('MAGNALISTER_PLUGIN') or die('Direct Access to this location is not allowed.');

/**
 * Helper class for preparing React component data structure
 * Transforms v2 data format to match the structure expected by AmazonVariations React component
 */
class ReactHelper {

    private $mpID;
    private $productID = 0;
    /**
     * @var array
     */
    private $productIDs = array();

    /**
     * Constructor
     * @param int $mpID Marketplace ID
     * @param int $productID Product ID
     */
    public function __construct($mpID, $productID) {
        $this->mpID = (int)$mpID;
        // PRODUCT-SPECIFIC MODE: Save to amazon_apply + longtext tables

        if($_GET['view'] === 'apply') {
            // Get product IDs from selection table
            $pIDs = MagnaDB::gi()->fetchArray('
                SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
                 WHERE mpID=\'' . $this->mpID . '\' AND
                       selectionname=\'apply\' AND
                       session_id=\'' . session_id() . '\'
            ');
            if (!empty($pIDs)) {
                // Extract product IDs from rows (fetchArray returns array of associative arrays)
                foreach ($pIDs as $row) {
                    $pid = current($row);
                    $this->productIDs[] = $pid;
                }
                $this->productID = (int)$this->productIDs[0];
            }
        }
    }

    /**
     * Get shop attributes from all available shop variations
     * Uses AttributesMatchingHelper to ensure compatibility with:
     * - Gambio 4.6+ (properties system)
     * - Gambio older versions
     * - osCommerce (options system)
     * - Modified shop
     *
     * @return array Shop attributes in grouped format for React component
     */
    public function getShopAttributes() {
        require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/AttributesMatchingHelper.php');

        $helper = new AttributesMatchingHelper($this->mpID);

        // Get all shop variations using existing helper
        // This method handles all shop systems, versions, languages, etc.
        $shopVariations = $helper->getShopVariations();

        // shopVariations structure from AttributesMatchingHelper:
        // [
        //   'Group Name' => [
        //     'attribute_code' => [
        //       'Code' => 'attribute_code',
        //       'Name' => 'Attribute Display Name',
        //       'Values' => ['value_id' => 'value_name', ...],
        //       'Type' => 'select|text|multiSelect',
        //       'Custom' => true|false (optional),
        //       'Disabled' => '' (optional)
        //     ]
        //   ]
        // ]

        // Transform to React component format (already grouped):
        // [
        //   'Group Name' => [
        //     'optGroupClass' => 'css-class',
        //     'attribute_code' => [
        //       'name' => 'Display Name',
        //       'type' => 'select|text|multiSelect',
        //       'values' => ['key' => 'value', ...]
        //     ]
        //   ]
        // ]

        $groupedAttributes = array();

        foreach ($shopVariations as $groupName => $attributes) {
            if (!isset($groupedAttributes[$groupName])) {
                // Determine optGroupClass based on group name
                $optGroupClass = '';
                if (strpos($groupName, 'Variation') !== false || strpos($groupName, ML_VARIATION) !== false) {
                    $optGroupClass = 'variations-group';
                } elseif (strpos($groupName, 'Product') !== false || strpos($groupName, ML_PRODUCT_DEFAULT_FIELDS) !== false) {
                    $optGroupClass = 'product-fields-group';
                } elseif (strpos($groupName, 'Additional') !== false || strpos($groupName, ML_GENERAL_VARMATCH_ADDITIONAL_OPTIONS) !== false) {
                    $optGroupClass = 'additional-options-group';
                }

                $groupedAttributes[$groupName] = array(
                    'optGroupClass' => $optGroupClass
                );
            }

            foreach ($attributes as $attrCode => $attr) {
                // Skip if this is metadata, not an actual attribute
                if (!is_array($attr) || !isset($attr['Code'])) {
                    continue;
                }

                // Check if attribute is disabled
                if (isset($attr['Disabled']) && !empty($attr['Disabled'])) {
                    continue;
                }

                // Transform to React format
                $groupedAttributes[$groupName][$attr['Code']] = array(
                    'name'   => isset($attr['Name']) ? $attr['Name'] : $attr['Code'],
                    'type'   => isset($attr['Type']) ? $attr['Type'] : 'select',
                    'values' => isset($attr['Values']) && is_array($attr['Values']) ? $attr['Values'] : array()
                );

                // Add custom flag if exists
                if (isset($attr['Custom']) && $attr['Custom']) {
                    $groupedAttributes[$groupName][$attr['Code']]['custom'] = true;
                }
            }
        }

        // Decode HTML entities for all group names, attribute names and values
        // AttributesMatchingHelper already calls arrayEntitiesToUTF8(), but we need to ensure
        // proper decoding for React component display
        $decodedAttributes = array();

        foreach ($groupedAttributes as $groupName => &$group) {
            // Decode group name
            $decodedGroupName = html_entity_decode($groupName, ENT_QUOTES, 'UTF-8');

            if (!isset($decodedAttributes[$decodedGroupName])) {
                $decodedAttributes[$decodedGroupName] = array();
            }

            foreach ($group as $attrCode => &$attr) {
                if ($attrCode === 'optGroupClass') {
                    // Copy optGroupClass as-is
                    $decodedAttributes[$decodedGroupName]['optGroupClass'] = $attr;
                    continue;
                }

                if (is_array($attr) && isset($attr['name'])) {
                    // Decode attribute name
                    $attr['name'] = html_entity_decode($attr['name'], ENT_QUOTES, 'UTF-8');

                    // Decode attribute values
                    if (isset($attr['values']) && is_array($attr['values'])) {
                        foreach ($attr['values'] as $key => &$value) {
                            $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
                        }
                    }

                    $decodedAttributes[$decodedGroupName][$attrCode] = $attr;
                }
            }
        }
        return $decodedAttributes;
    }

    /**
     * Retrieve marketplace attributes for a given category ID.
     *
     * This method fetches attribute details from the marketplace API using the category ID,
     * processes the response, and returns a structured array suitable for use in front-end components.
     *
     * @param string|int $categoryID The category ID for which attributes are to be retrieved.
     * @return array List of attributes with details such as 'value', 'required', 'dataType', 'desc', and 'values'.
     *               Returns an empty array if the category ID is invalid, the API response is empty, or an error occurs.
     */
    public function getMarketplaceAttributes($categoryID) {

        if (empty($categoryID)) {
            return array();
        }

        // Use AmazonHelper to get attributes from Amazon API (GetCategoryDetails)
        require_once(DIR_MAGNALISTER_MODULES . 'amazon/AmazonHelper.php');

        try {
            $helper = new AmazonHelper();
            $attributesData = $helper->getAttributesFromMP($categoryID);

            if (!empty($attributesData['attributes'])) {
                $attributes = array();

                // Transform API response to React component format
                foreach ($attributesData['attributes'] as $key => $attr) {
                    $attributes[$key] = array(
                        'value'    => isset($attr['title']) ? $attr['title'] : $key,
                        'required' => isset($attr['mandatory']) ? (bool)$attr['mandatory'] : false,
                        'dataType' => isset($attr['type']) ? $attr['type'] : 'text',
                        'desc'     => isset($attr['desc']) ? $attr['desc'] : '',
                        'values'   => isset($attr['values']) && is_array($attr['values']) ? $attr['values'] : array()
                    );
                }

                return $attributes;
            }
        } catch (Exception $e) {
            // Log error but don't break - fallback to empty
            if (defined('MAGNA_DEBUG') && MAGNA_DEBUG) {
                echo '<!-- GetCategoryDetails Error: ' . htmlspecialchars($e->getMessage()) . ' -->';
            }
        }

        return array();
    }

    /**
     * Get conditional rules from Amazon API (GetCategoryDetails)
     * @return array Conditional rules for attribute dependencies
     */
    public function getConditionalRules($categoryID) {
        if (empty($categoryID)) {
            return array();
        }

        // Get conditional rules from Amazon API via GetCategoryDetails
        try {
            $categoryResult = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'GetCategoryDetails',
                'DATA'   => array(
                    'PRODUCTTYPE'               => $categoryID,
                    'INCLUDE_CONDITIONAL_RULES' => true
                ),
            ));
            // Extract conditional rules (if available from backend)
            if (isset($categoryResult['DATA']['conditional_rules']) && is_array($categoryResult['DATA']['conditional_rules'])) {
                return $categoryResult['DATA']['conditional_rules'];
            }
        } catch (Exception $e) {
            // Log error but don't break - fallback to empty
            if (defined('MAGNA_DEBUG') && MAGNA_DEBUG) {
                echo '<!-- GetConditionalRules Error: ' . htmlspecialchars($e->getMessage()) . ' -->';
            }
        }

        return array();
    }

    /**
     * Load attribute matching from amazon_apply table (product-specific)
     * @param int $productID Product ID
     * @return array Attribute matching data (decoded JSON)
     */
    public function loadFromApplyTable($productID) {
        $oDB = MagnaDB::gi();

        $row = $oDB->fetchRow("
            SELECT data, DataId
            FROM " . TABLE_MAGNA_AMAZON_APPLY . "
            WHERE mpID = " . (int)$this->mpID . "
              AND products_id = " . (int)$productID . "
        ");

        if (empty($row)) {
            $variationGroup = $_POST['mainCategory'];
            return $this->loadFromVariantMatchingTable($variationGroup);
        }

        // V3 approach: Check DataId first (new format), fallback to data column (old format)
        $data = '';
        if (!empty($row['DataId'])) {
            // NEW FORMAT: Load from longtext table
            $longtextRow = $oDB->fetchRow("
                SELECT Value
                FROM magnalister_amazon_prepare_longtext
                WHERE TextId = '" . $oDB->escape($row['DataId']) . "'
                  AND ReferenceFieldName = 'data'
            ");

            if (!empty($longtextRow['Value'])) {
                $data = $longtextRow['Value'];
            }
        }

        // Fallback to old format if DataId is empty or not found
        if (empty($data) && !empty($row['data'])) {
            // OLD FORMAT: data column contains base64 + serialized full prepare data
            // We need to extract ShopVariation from it
            $oldData = @unserialize(@base64_decode($row['data']));
            if (is_array($oldData) && isset($oldData['ShopVariation'])) {
                // ShopVariation is already JSON encoded
                $data = $oldData['ShopVariation'];
            }
        }

        // Decode JSON attribute matching
        if (!empty($data)) {
            $decoded = json_decode($data, true);
            return is_array($decoded) ? $decoded : array();
        }

        return array();
    }

    /**
     * Load attribute matching from variantmatching table (category template)
     * @param string $category Category/Product Type ID
     * @return array Attribute matching data (decoded JSON)
     */
    public function loadFromVariantMatchingTable($category) {
        $oDB = MagnaDB::gi();

        if (empty($category) || $category === 'none') {
            return array();
        }

        $row = $oDB->fetchRow("
            SELECT ShopVariation as data
            FROM " . TABLE_MAGNA_AMAZON_VARIANTMATCHING . "
            WHERE MpId = " . (int)$this->mpID . "
              AND MpIdentifier = '" . $oDB->escape($category) . "'
        ");

        if (empty($row) || empty($row['data'])) {
            return array();
        }

        // Decode JSON
        $decoded = json_decode($row['data'], true);
        return is_array($decoded) ? $decoded : array();
    }

    /**
     * Get current attribute matching configuration
     * Routes to appropriate table based on product availability (NOT on $_GET['view'])
     * @return array Attribute matching rules
     */
    public function getAttributeMatching() {
        // Check if multiple product IDs are provided via POST (for batch operations)
        $pIDs = MagnaDB::gi()->fetchArray('
		SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
		 WHERE mpID=\'' . $this->mpID . '\' AND
		       selectionname=\'apply\' AND
		       session_id=\'' . session_id() . '\'
	', true);

        if (empty($pIDs) || $_GET['view'] === 'varmatch') {
            // GLOBAL TEMPLATE MODE: No products, load from variantmatching table
            $variationGroup = null;
            if (isset($_POST['ml']['variationGroup'])) {
                $variationGroup = $_POST['ml']['variationGroup'];
            } else if (isset($_POST['mainCategory'])) {
                $variationGroup = $_POST['mainCategory'];
            } else if (isset($_POST['PrimaryCategory'])) {
                // V2 Variation Matching page uses 'PrimaryCategory'
                $variationGroup = $_POST['PrimaryCategory'];
            }

            return $this->loadFromVariantMatchingTable($variationGroup);
        } else {
            $pid = is_array($pIDs[0]) ? current($pIDs[0]) : $pIDs[0];
            // PRODUCT-SPECIFIC MODE: Products exist, load from apply table
            $data = $this->loadFromApplyTable($pid);
            return $data;
        }
    }

    /**
     * Save attribute matching to variantmatching table (category template)
     * @param array $attributeMatching New attribute matching data to merge
     * @param string $category Category/Product Type ID
     * @return bool Success status
     */
    public function saveToVariantMatchingTable($attributeMatching, $category) {
        $oDB = MagnaDB::gi();

        if (empty($category) || $category === 'none') {
            return false;
        }

        // Load existing attribute matching data
        $existingData = $this->loadFromVariantMatchingTable($category);

        // Merge new data with existing data
        foreach ($attributeMatching as $key => $value) {
            if ($value === null) {
                unset($existingData[$key]);
            } else {
                $existingData[$key] = $value;
            }
        }

        // Encode merged data to JSON
        $jsonData = json_encode($existingData);

        // Use batchinsert with ON DUPLICATE KEY UPDATE
        $batchData = array(
            array(
                'MpId'             => $this->mpID,
                'MpIdentifier'     => $category,
                'ShopVariation'    => $jsonData,
                'ModificationDate' => 'NOW()'
            )
        );

        $oDB->batchinsert(TABLE_MAGNA_AMAZON_VARIANTMATCHING, $batchData, false, // Don't use REPLACE
            array('ShopVariation', 'ModificationDate') // Update these fields on duplicate key
        );

        return true;
    }

    /**
     * Save attribute matching to amazon_apply table (product-specific)
     * @param array $attributeMatching New attribute matching data to merge
     * @param array $productIDs Array of product IDs to update
     * @param array $options Optional parameters: variationGroup, variationTheme
     * @return bool Success status
     */
    public function saveToApplyTable($attributeMatching, $options = array()) {
        $oDB = MagnaDB::gi();

        if (empty($this->productIDs)) {
            return false;
        }

        // Load existing attribute matching data for the first product
        $existingData = $this->loadFromApplyTable($this->productID);

        // Merge new data with existing data
        foreach ($attributeMatching as $key => $value) {
            if ($value === null) {
                unset($existingData[$key]);
            } else {
                $existingData[$key] = $value;
            }
        }

        // Encode merged data to JSON
        $jsonData = json_encode($existingData);

        // Generate new TextId (SHA256 hash)
        $newTextId = hash('sha256', $jsonData);

        // Insert into longtext table
        $oDB->query("
            INSERT IGNORE INTO magnalister_amazon_prepare_longtext
            (TextId, ReferenceFieldName, Value, CreatedAt)
            VALUES (
                '" . $oDB->escape($newTextId) . "',
                'data',
                '" . $oDB->escape($jsonData) . "',
                NOW()
            )
        ");

        // Get options
        $variationGroup = isset($options['variationGroup']) ? $options['variationGroup'] : null;
        $variationTheme = isset($options['variationTheme']) ? $options['variationTheme'] : null;

        // Build category structure
        $existingRow = null;
        if (!empty($this->productID)) {
            $existingRow = $oDB->fetchRow("
                SELECT category, topMainCategory, topProductType, topBrowseNode1, ConditionType, ConditionNote
                FROM " . TABLE_MAGNA_AMAZON_APPLY . "
                WHERE mpID = " . $this->mpID . "
                  AND products_id = " . (int)$this->productID . "
            ");
        }

        $existingCategoryData = null;
        if (!empty($existingRow) && !empty($existingRow['category'])) {
            $existingCategoryData = @unserialize(@base64_decode($existingRow['category']));
        }

        $c = array(
            'MainCategory'  => $variationGroup ? $variationGroup : (isset($existingCategoryData['MainCategory']) ? $existingCategoryData['MainCategory'] : null),
            'ProductType'   => isset($existingCategoryData['ProductType']) ? $existingCategoryData['ProductType'] : ($variationGroup ? $variationGroup : null),
            'BrowseNodes'   => isset($existingCategoryData['BrowseNodes']) ? $existingCategoryData['BrowseNodes'] : array(),
            'ConditionType' => isset($existingCategoryData['ConditionType']) ? $existingCategoryData['ConditionType'] : (($existingRow && isset($existingRow['ConditionType'])) ? $existingRow['ConditionType'] : ''),
            'ConditionNote' => isset($existingCategoryData['ConditionNote']) ? $existingCategoryData['ConditionNote'] : (($existingRow && isset($existingRow['ConditionNote'])) ? $existingRow['ConditionNote'] : '')
        );

        if (is_array($c['BrowseNodes'])) {
            $c['BrowseNodes'] = array_values($c['BrowseNodes']);
        }

        $categoryEncoded = base64_encode(serialize($c));

        // Format variation_theme
        $variationThemeValue = null;
        if (!empty($variationTheme) && $variationTheme !== 'none') {
            $decoded = json_decode($variationTheme, true);
            if (is_array($decoded) && !empty($decoded)) {
                $variationThemeValue = $variationTheme;
            } else {
                $variationThemeValue = json_encode(array($variationTheme => null));
            }
        }

        // Prepare batch data
        $batchData = array();
        foreach ($this->productIDs as $pID) {
            $productModel = $oDB->fetchOne("
                SELECT products_model
                FROM " . TABLE_PRODUCTS . "
                WHERE products_id = " . (int)$pID . "
            ");

            $existingDataRow = $oDB->fetchRow("
                SELECT data
                FROM " . TABLE_MAGNA_AMAZON_APPLY . "
                WHERE mpID = " . $this->mpID . "
                  AND products_id = " . (int)$pID . "
            ");

            $dataArray = array();
            if (!empty($existingDataRow['data'])) {
                $dataArray = @unserialize(@base64_decode($existingDataRow['data']));
                if (!is_array($dataArray)) {
                    $dataArray = array();
                }
            }

            // V3 approach: Clear ShopVariation from data column (moved to longtext table via DataId)
            // Keep other data (like price, quantity, etc.) but remove ShopVariation
            unset($dataArray['ShopVariation']);
            $dataEncoded = base64_encode(serialize($dataArray));

            $rowData = array(
                'mpID'            => $this->mpID,
                'products_id'     => $pID,
                'products_model'  => $productModel,
                'DataId'          => $newTextId,
                'category'        => $categoryEncoded,
                'data'            => $dataEncoded,
                'topMainCategory' => $c['MainCategory'] == null ? '' : $c['MainCategory'],
                'topProductType'  => $c['ProductType'] == null ? '' : $c['ProductType'],
                'topBrowseNode1'  => $c['BrowseNodes'] == null ? '' : json_encode([$c['MainCategory'] => $c['BrowseNodes']]),
                'ConditionType'   => $c['ConditionType'],
                'ConditionNote'   => $c['ConditionNote']
            );

            if ($variationThemeValue !== null) {
                $rowData['variation_theme'] = $variationThemeValue;
            }

            $batchData[] = $rowData;
        }

        // Batch insert/update
        if (!empty($batchData)) {
            $fieldsToUpdate =array_keys($batchData[0]);
            $oDB->batchinsert(TABLE_MAGNA_AMAZON_APPLY, $batchData, false, $fieldsToUpdate);
        }

        return true;
    }

    /**
     * Get translations for React component
     * @return array Translations
     */
    public function getTranslations() {
        require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/AttributesMatchingHelper.php');

        $helper = new AttributesMatchingHelper($this->mpID);
        $translations = $helper->getVarMatchTranslations();

        // Add any additional translations needed by React component
        $translations['addRule'] = defined('ML_GENERAL_VARMATCH_ADD_RULE') ? ML_GENERAL_VARMATCH_ADD_RULE : 'Add Rule';
        $translations['deleteRule'] = defined('ML_GENERAL_VARMATCH_DELETE_RULE') ? ML_GENERAL_VARMATCH_DELETE_RULE : 'Delete Rule';
        $translations['saveSuccess'] = defined('ML_GENERAL_VARMATCH_SAVE_SUCCESS') ? ML_GENERAL_VARMATCH_SAVE_SUCCESS : 'Attribute matching saved successfully!';
        $translations['enterFreetext'] = defined('ML_GENERAL_VARMATCH_ENTER_FREETEXT') ? ML_GENERAL_VARMATCH_ENTER_FREETEXT : 'Enter custom value';
        $translations['pleaseSelect'] = html_entity_decode($translations['pleaseSelect'], ENT_QUOTES, 'UTF-8');
        $translations['valueMatchingTitle'] = html_entity_decode(defined('ML_GENERAL_VARMATCH_VALUE_MATCHING_TITLE') ? ML_GENERAL_VARMATCH_VALUE_MATCHING_TITLE : 'Value Matching', ENT_QUOTES, 'UTF-8');
        $translations['valueMatchingDescription'] = html_entity_decode(defined('ML_GENERAL_VARMATCH_VALUE_MATCHING_DESCRIPTION') ? ML_GENERAL_VARMATCH_VALUE_MATCHING_DESCRIPTION : 'Match your shop attribute values with Amazon attribute values:', ENT_QUOTES, 'UTF-8');

        // Database value input translations (v2 only)
        $translations['databaseTableLabel'] = defined('ML_GENERAL_VARMATCH_DATABASE_TABLE_LABEL') ? ML_GENERAL_VARMATCH_DATABASE_TABLE_LABEL : 'Table';
        $translations['databaseColumnLabel'] = defined('ML_GENERAL_VARMATCH_DATABASE_COLUMN_LABEL') ? ML_GENERAL_VARMATCH_DATABASE_COLUMN_LABEL : 'Column';
        $translations['databaseAliasLabel'] = defined('ML_GENERAL_VARMATCH_DATABASE_ALIAS_LABEL') ? ML_GENERAL_VARMATCH_DATABASE_ALIAS_LABEL : 'Alias';
        $translations['databaseTablePlaceholder'] = defined('ML_GENERAL_VARMATCH_DATABASE_TABLE_PLACEHOLDER') ? ML_GENERAL_VARMATCH_DATABASE_TABLE_PLACEHOLDER : 'Enter table name';
        $translations['databaseColumnPlaceholder'] = defined('ML_GENERAL_VARMATCH_DATABASE_COLUMN_PLACEHOLDER') ? ML_GENERAL_VARMATCH_DATABASE_COLUMN_PLACEHOLDER : 'Enter column name';
        $translations['databaseAliasPlaceholder'] = defined('ML_GENERAL_VARMATCH_DATABASE_ALIAS_PLACEHOLDER') ? ML_GENERAL_VARMATCH_DATABASE_ALIAS_PLACEHOLDER : 'Enter product ID alias';
        $translations['clearAllMatchings'] = defined('ML_GENERAL_VARMATCH_CLEAR_ALL_MATCHINGS') ? html_entity_decode(ML_GENERAL_VARMATCH_CLEAR_ALL_MATCHINGS) : 'Clear all matchings';

        return $translations;
    }

    /**
     * Get configuration options for React component
     * @return array Configuration
     */
    public function getConfig() {
        return array(
            'mpID'              => $this->mpID,
            'productID'         => $this->productID,
            'saveUrl'           => toURL(array(
                'mp'     => 'amazon',
                'mode'   => 'prepare',
                'action' => 'saveAttributeMatching'
            ), true),
            'allowCustomValues' => true,
            'autoSave'          => false // Set to true if auto-save is desired
        );
    }

    /**
     * Generate marketplace attributes from variation theme
     * Since v2 doesn't have magnalister_amazon_attributes table,
     * we parse the variation theme to create basic attributes
     *
     * @param string $variationTheme e.g., "STYLE_NAME/SIZE_NAME" or "SizeColor"
     * @return array Marketplace attributes
     */
    public function generateMarketplaceAttributesFromTheme($variationTheme) {
        $attributes = array();

        // Common Amazon attribute mappings
        $attributeMap = array(
            'SIZE_NAME'   => array(
                'value'    => 'Size Name',
                'required' => true,
                'dataType' => 'text',
                'desc'     => 'The size of the item'
            ),
            'COLOR_NAME'  => array(
                'value'    => 'Color Name',
                'required' => true,
                'dataType' => 'text',
                'desc'     => 'The color of the item'
            ),
            'STYLE_NAME'  => array(
                'value'    => 'Style Name',
                'required' => true,
                'dataType' => 'text',
                'desc'     => 'The style name of the item'
            ),
            'FLAVOR_NAME' => array(
                'value'    => 'Flavor Name',
                'required' => true,
                'dataType' => 'text',
                'desc'     => 'The flavor of the item'
            ),
            'SCENT_NAME'  => array(
                'value'    => 'Scent Name',
                'required' => true,
                'dataType' => 'text',
                'desc'     => 'The scent of the item'
            )
        );

        // Parse variation theme (could be "STYLE_NAME/SIZE_NAME" or "SizeColor" etc.)
        $parts = array();
        if (strpos($variationTheme, '/') !== false) {
            // Format: "STYLE_NAME/SIZE_NAME"
            $parts = explode('/', $variationTheme);
        } else {
            // Format: "SizeColor" - try to split by common patterns
            if (preg_match_all('/[A-Z][a-z]+/', $variationTheme, $matches)) {
                foreach ($matches[0] as $part) {
                    $parts[] = strtoupper($part) . '_NAME';
                }
            }
        }

        // Create attributes from parsed parts
        foreach ($parts as $part) {
            $part = trim($part);
            $key = strtolower($part);

            if (isset($attributeMap[$part])) {
                $attributes[$key] = $attributeMap[$part];
                $attributes[$key]['values'] = array(); // No predefined values
            } else {
                // Unknown attribute, create basic structure
                $attributes[$key] = array(
                    'value'    => ucwords(str_replace('_', ' ', $part)),
                    'required' => true,
                    'dataType' => 'text',
                    'values'   => array()
                );
            }
        }

        // Add common optional attributes
        $attributes['item_name'] = array(
            'value'    => 'Item Name',
            'required' => false,
            'dataType' => 'text',
            'desc'     => 'Optional descriptive name for the item',
            'values'   => array()
        );

        return $attributes;
    }

    /**
     * Save attribute matching data from React component
     * Routes to appropriate table based on productID (NOT on $_GET['view'])
     * @param array $attributeMatching Attribute matching rules from React
     * @return bool Success status
     */
    public function saveAttributeMatching($attributeMatching) {
        // Check if this is GLOBAL TEMPLATE mode (productID === 0)
        // Global templates save to magnalister_amazon_variantmatching table
        // Product-specific matching saves to magnalister_amazon_apply + longtext tables

        if ($this->productID === 0) {
            // GLOBAL TEMPLATE MODE: Save to variantmatching table

            // Get variation group (category ID) from POST
            $variationGroup = null;
            if (isset($_POST['ml']['variationGroup'])) {
                $variationGroup = $_POST['ml']['variationGroup'];
            } else if (isset($_POST['mainCategory'])) {
                $variationGroup = $_POST['mainCategory'];
            } else if (isset($_POST['PrimaryCategory'])) {
                $variationGroup = $_POST['PrimaryCategory'];
            }

            if ($variationGroup === null || $variationGroup === 'none') {
                return false;
            }

            return $this->saveToVariantMatchingTable($attributeMatching, $variationGroup);
        }

        // Get options from POST
        $options = array(
            'variationGroup' => isset($_POST['ml']['variationGroup']) ? $_POST['ml']['variationGroup'] : null,
            'variationTheme' => isset($_POST['ml']['variationTheme']) ? $_POST['ml']['variationTheme'] : null
        );

        return $this->saveToApplyTable($attributeMatching, $options);
    }

    /**
     * Save variation theme to database
     * @param string $variationTheme Variation theme (e.g., "STYLE_NAME/SIZE_NAME")
     * @return bool Success status
     */
    public function saveVariationTheme($variationTheme) {
        // GLOBAL TEMPLATE MODE: Don't save variation theme
        // Global templates (productID = 0) only save attribute matching to variantmatching table
        // Variation theme is not relevant for global templates
        if ($this->productID === 0) {
            return true; // Success (no-op)
        }

        // PRODUCT-SPECIFIC MODE: Save variation theme to amazon_apply table
        $oDB = MagnaDB::gi();

        // Load existing category and data from database
        $existing = $oDB->fetchRow("
            SELECT category, data
            FROM " . TABLE_MAGNA_AMAZON_APPLY . "
            WHERE mpID = " . $this->mpID . "
              AND products_id = " . $this->productID . "
        ");

        // Decode existing category data (base64 + serialized)
        $categoryData = array();
        if (!empty($existing['category'])) {
            $categoryData = @unserialize(base64_decode($existing['category']));
            if (!is_array($categoryData)) {
                $categoryData = array();
            }
        }

        // Decode existing data column (base64 + serialized)
        $dataColumn = array();
        if (!empty($existing['data'])) {
            $dataColumn = @unserialize(base64_decode($existing['data']));
            if (!is_array($dataColumn)) {
                $dataColumn = array();
            }
        }

        // Update variation theme in both structures
        $categoryData['VariationTheme'] = $variationTheme;
        $dataColumn['VariationTheme'] = $variationTheme;

        // Format variation theme as JSON for variation_theme column (V2 format: {"STYLE_NAME/SIZE_NAME":null})
        $variationThemeJson = json_encode(array($variationTheme => null));

        // Update magnalister_amazon_apply table with all three formats
        $oDB->query("
            UPDATE " . TABLE_MAGNA_AMAZON_APPLY . "
            SET variation_theme = '" . $oDB->escape($variationThemeJson) . "',
                category = '" . $oDB->escape(base64_encode(serialize($categoryData))) . "',
                data = '" . $oDB->escape(base64_encode(serialize($dataColumn))) . "'
            WHERE mpID = " . $this->mpID . "
              AND products_id = " . $this->productID . "
        ");

        return true;
    }

    /**
     * Validate attribute matching data
     * @param array $attributeMatching Attribute matching rules
     * @return array Validation result with 'valid' boolean and 'errors' array
     *
     * Supports both V2 and V3 formats:
     * - V2: array of {marketplaceAttribute, shopAttribute} objects
     * - V3: object with attribute keys => {Code, UseShopValues, Kind, ...}
     */
    public function validateAttributeMatching($attributeMatching) {
        $errors = array();

        if (!is_array($attributeMatching)) {
            $errors[] = 'Invalid data format';
            return array('valid' => false, 'errors' => $errors);
        }

        // Detect format by checking first element
        $firstKey = array_keys($attributeMatching);
        if (empty($firstKey)) {
            // Empty array is valid
            return array('valid' => true, 'errors' => array());
        }

        $firstValue = $attributeMatching[$firstKey[0]];

        // V3 format: attribute keys => {Code, UseShopValues, Kind, ...}
        if (is_array($firstValue) && isset($firstValue['Code'])) {
            // V3 format validation (simplified - just check structure)
            foreach ($attributeMatching as $attrKey => $attrData) {
                if ($attrData === null) {
                    // Null means delete - this is valid
                    continue;
                }

                if (!is_array($attrData)) {
                    $errors[] = "Attribute '$attrKey': Invalid data format";
                    continue;
                }

                // Check required fields for V3 format
                if (!isset($attrData['Code'])) {
                    $errors[] = "Attribute '$attrKey': Missing 'Code' field";
                }
            }

            return array('valid' => count($errors) === 0, 'errors' => $errors);
        }

        // V2 format validation (legacy)
        // Validate each mapping
        foreach ($attributeMatching as $index => $mapping) {
            if (!isset($mapping['marketplaceAttribute']) || empty($mapping['marketplaceAttribute'])) {
                $errors[] = "Rule #" . ($index + 1) . ": Marketplace attribute is required";
            }

            if (!isset($mapping['shopAttribute'])) {
                $errors[] = "Rule #" . ($index + 1) . ": Shop attribute is required";
            }

            // Check for required attributes
            $marketplaceAttrs = $this->getMarketplaceAttributes();
            foreach ($marketplaceAttrs as $attr) {
                if ($attr['required'] === 'required') {
                    $found = false;
                    foreach ($attributeMatching as $attrMapping) {
                        if (isset($attrMapping['marketplaceAttribute']) && $attrMapping['marketplaceAttribute'] === $attr['name']) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $errors[] = "Required attribute '" . $attr['name'] . "' is not mapped";
                    }
                }
            }
        }

        return array(
            'valid'  => empty($errors),
            'errors' => $errors
        );
    }
}
