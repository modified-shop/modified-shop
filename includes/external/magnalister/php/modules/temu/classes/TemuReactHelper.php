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
 * (c) 2010 - 2026 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC_MODULE_CALL') or defined('_VALID_XTC') or defined('MAGNALISTER_PLUGIN') or die('Direct Access to this location is not allowed.');

/**
 * Helper class for preparing React component data structure for Temu attribute matching.
 * Adapts Amazon's ReactHelper pattern for Temu marketplace specifics.
 */
class TemuReactHelper {

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
        // Honor the explicit productID (0 = global template mode). The view=apply
        // block below may still derive it from the selection table for screen renders.
        $this->productID = (int)$productID;

        if (isset($_GET['view']) && $_GET['view'] === 'apply') {
            // PRODUCT-SPECIFIC MODE: resolve the selected products. The Product
            // Preparation screen uses the 'prepare' selection; the Create-New-Products
            // matching uses 'apply'. Prefer 'prepare' so saves/loads land in the
            // per-product prepare_longtext table instead of the category template.
            // The standalone Attributes Matching tab posts with view=varmatch and never
            // enters this block, so it stays in global-template mode.
            $this->productIDs = $this->getSelectionPIDs(array('prepare', 'apply'));
            if (!empty($this->productIDs)) {
                $this->productID = (int)$this->productIDs[0];
            }
        }
    }

    /**
     * Resolve all selected product ids for the current session, trying each given
     * selection name in priority order and returning the first non-empty match.
     *
     * @param array $aSelectionNames Selection names to try, in priority order.
     * @return array List of product ids (ints); empty when nothing is selected.
     */
    private function getSelectionPIDs($aSelectionNames) {
        $oDB = MagnaDB::gi();
        foreach ($aSelectionNames as $sName) {
            $aRows = $oDB->fetchArray("
                SELECT pID FROM " . TABLE_MAGNA_SELECTION . "
                 WHERE mpID = '" . (int)$this->mpID . "'
                       AND selectionname = '" . $oDB->escape($sName) . "'
                       AND session_id = '" . $oDB->escape(session_id()) . "'
            ", true);
            if (!empty($aRows)) {
                return array_map('intval', $aRows);
            }
        }
        return array();
    }

    /**
     * @return int Resolved product id (0 = global template mode).
     */
    public function getProductID() {
        return (int)$this->productID;
    }

    /**
     * Get shop attributes from all available shop variations.
     * Uses AttributesMatchingHelper for cross-shop compatibility (Gambio, osCommerce, etc.)
     *
     * @return array Shop attributes in grouped format for React component
     */
    public function getShopAttributes() {
        require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/AttributesMatchingHelper.php');

        $helper = new AttributesMatchingHelper($this->mpID);
        $shopVariations = $helper->getShopVariations();

        $groupedAttributes = array();

        foreach ($shopVariations as $groupName => $attributes) {
            if (!isset($groupedAttributes[$groupName])) {
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
                if (!is_array($attr) || !isset($attr['Code'])) {
                    continue;
                }

                if (isset($attr['Disabled']) && !empty($attr['Disabled'])) {
                    continue;
                }

                $groupedAttributes[$groupName][$attr['Code']] = array(
                    'name'   => isset($attr['Name']) ? $attr['Name'] : $attr['Code'],
                    'type'   => isset($attr['Type']) ? $attr['Type'] : 'select',
                    'values' => isset($attr['Values']) && is_array($attr['Values']) ? $attr['Values'] : array()
                );

                if (isset($attr['Custom']) && $attr['Custom']) {
                    $groupedAttributes[$groupName][$attr['Code']]['custom'] = true;
                }
            }
        }

        // Decode HTML entities for React component display
        $decodedAttributes = array();

        foreach ($groupedAttributes as $groupName => &$group) {
            $decodedGroupName = html_entity_decode($groupName, ENT_QUOTES, 'UTF-8');

            if (!isset($decodedAttributes[$decodedGroupName])) {
                $decodedAttributes[$decodedGroupName] = array();
            }

            foreach ($group as $attrCode => &$attr) {
                if ($attrCode === 'optGroupClass') {
                    $decodedAttributes[$decodedGroupName]['optGroupClass'] = $attr;
                    continue;
                }

                if (is_array($attr) && isset($attr['name'])) {
                    $attr['name'] = html_entity_decode($attr['name'], ENT_QUOTES, 'UTF-8');

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
     * Retrieve marketplace attributes for a given Temu category ID.
     *
     * Calls the Temu GetCategoryDetails API and transforms the response
     * (including variation_dim_* pseudo-attributes) into React component format.
     *
     * @param string|int $categoryID The Temu category ID
     * @return array Attributes in React component format, empty array on error
     */
    public function getMarketplaceAttributes($categoryID) {
        if (empty($categoryID)) {
            return array();
        }

        try {
            $attributesData = $this->fetchCategoryDetails($categoryID);

            if (!empty($attributesData['attributes'])) {
                $attributes = array();

                foreach ($attributesData['attributes'] as $key => $attr) {
                    $attributes[$key] = array(
                        'value'    => isset($attr['title']) ? $attr['title'] : $key,
                        'required' => isset($attr['mandatory']) ? (bool)$attr['mandatory'] : false,
                        'dataType' => isset($attr['type']) ? $attr['type'] : 'text',
                        'desc'     => isset($attr['desc']) ? $attr['desc'] : '',
                        'values'   => isset($attr['values']) && is_array($attr['values']) ? $attr['values'] : array()
                    );

                    // Pass through parent-child metadata fields for conditional rules
                    if (isset($attr['refPid'])) {
                        $attributes[$key]['refPid'] = $attr['refPid'];
                    }
                    if (isset($attr['childAttributes'])) {
                        $attributes[$key]['childAttributes'] = $attr['childAttributes'];
                    }
                    if (isset($attr['parentRefPid'])) {
                        $attributes[$key]['parentRefPid'] = $attr['parentRefPid'];
                    }
                    if (isset($attr['triggerVid'])) {
                        $attributes[$key]['triggerVid'] = $attr['triggerVid'];
                    }
                    if (isset($attr['groupId'])) {
                        $attributes[$key]['groupId'] = $attr['groupId'];
                    }
                }

                return $attributes;
            }
        } catch (Exception $e) {
            if (defined('MAGNA_DEBUG') && MAGNA_DEBUG) {
                echo '<!-- Temu GetCategoryDetails Error: ' . htmlspecialchars($e->getMessage()) . ' -->';
            }
        }

        return array();
    }

    /**
     * Fetch and transform Temu category details from the API.
     * Mirrors the logic in TemuHelper::getAttributesFromMP() to produce the same
     * data structure (with variation_dim_* pseudo-attributes injected).
     *
     * @param string|int $categoryID The Temu category ID
     * @return array Transformed category data with 'attributes' and 'variation_details'
     */
    private function fetchCategoryDetails($categoryID) {
        $data = array();

        $result = MagnaConnector::gi()->submitRequest(array(
            'ACTION' => 'GetCategoryDetails',
            'SUBSYSTEM' => 'Temu',
            'MARKETPLACEID' => $this->mpID,
            'DATA' => array('CategoryID' => $categoryID),
        ));

        if (!empty($result['DATA'])) {
            $data = $result['DATA'];

            // Add variation dimensions as additional attributes (same as TemuHelper)
            if (!empty($data['variation_details'])) {
                foreach ($data['variation_details'] as $aDimension) {
                    $sKey = 'variation_dim_' . $aDimension['id'];
                    $aValues = array();
                    if (!empty($aDimension['specList'])) {
                        foreach ($aDimension['specList'] as $aSpec) {
                            $aValues[(string)$aSpec['specId']] = $aSpec['specName'];
                        }
                    }
                    if (!isset($data['attributes'])) {
                        $data['attributes'] = array();
                    }
                    $data['attributes'][$sKey] = array(
                        'title' => $aDimension['name'],
                        'mandatory' => !empty($aDimension['required']),
                        'type' => !empty($aDimension['type']) ? $aDimension['type'] : 'text',
                        'multi' => !empty($aDimension['multi']),
                        'values' => $aValues,
                    );
                }
            }
        }

        if (!is_array($data) || !isset($data['attributes'])) {
            return array();
        }

        return $data;
    }

    /**
     * Get conditional rules from Temu API (parent-child attribute visibility)
     *
     * Temu attributes can have parent-child relationships where child attributes
     * become visible only when a specific parent value is selected.
     *
     * @param string|int $categoryID The Temu category ID
     * @return array Conditional rules for attribute dependencies
     */
    public function getConditionalRules($categoryID) {
        if (empty($categoryID)) {
            return array();
        }

        try {
            $result = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'GetCategoryDetails',
                'SUBSYSTEM' => 'Temu',
                'MARKETPLACEID' => $this->mpID,
                'DATA' => array(
                    'CategoryID' => $categoryID,
                ),
            ));

            $rules = array();

            if (!empty($result['DATA']['attributes'])) {
                foreach ($result['DATA']['attributes'] as $attrKey => $attr) {
                    // Collect attributes that define parent-child relationships
                    if (!empty($attr['refPid']) || !empty($attr['childAttributes']) || !empty($attr['parentRefPid'])) {
                        $rule = array('attributeKey' => $attrKey);

                        if (isset($attr['refPid'])) {
                            $rule['refPid'] = $attr['refPid'];
                        }
                        if (isset($attr['childAttributes'])) {
                            $rule['childAttributes'] = $attr['childAttributes'];
                        }
                        if (isset($attr['parentRefPid'])) {
                            $rule['parentRefPid'] = $attr['parentRefPid'];
                        }
                        if (isset($attr['triggerVid'])) {
                            $rule['triggerVid'] = $attr['triggerVid'];
                        }
                        if (isset($attr['groupId'])) {
                            $rule['groupId'] = $attr['groupId'];
                        }

                        $rules[] = $rule;
                    }
                }
            }

            return $rules;
        } catch (Exception $e) {
            if (defined('MAGNA_DEBUG') && MAGNA_DEBUG) {
                echo '<!-- Temu GetConditionalRules Error: ' . htmlspecialchars($e->getMessage()) . ' -->';
            }
        }

        return array();
    }

    /**
     * Load attribute matching from Temu prepare longtext table (product-specific)
     *
     * Temu stores ShopVariation JSON in the separate longtext table
     * (magnalister_temu_prepare_longtext), referenced by mpID + products_id.
     *
     * @param int $productID Product ID
     * @return array Attribute matching data (decoded JSON)
     */
    public function loadFromApplyTable($productID) {
        $oDB = MagnaDB::gi();

        // Check longtext table for ShopVariation data
        $row = $oDB->fetchRow("
            SELECT ShopVariationId
            FROM " . TABLE_MAGNA_TEMU_PREPARE_LONGTEXT . "
            WHERE mpID = " . (int)$this->mpID . "
              AND products_id = " . (int)$productID . "
        ");

        if (!empty($row) && !empty($row['ShopVariationId'])) {
            $decoded = json_decode($row['ShopVariationId'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Fallback: no product-specific data, try loading from variantmatching
        $variationGroup = isset($_POST['mainCategory']) ? $_POST['mainCategory'] : null;
        if (empty($variationGroup) && isset($_POST['PrimaryCategory'])) {
            $variationGroup = $_POST['PrimaryCategory'];
        }

        // If no category from POST, try getting it from the prepare table
        if (empty($variationGroup)) {
            $variationGroup = $oDB->fetchOne("
                SELECT PrimaryCategory
                FROM " . TABLE_MAGNA_TEMU_PREPARE . "
                WHERE mpID = " . (int)$this->mpID . "
                  AND products_id = " . (int)$productID . "
            ");
        }

        if (!empty($variationGroup)) {
            return $this->loadFromVariantMatchingTable($variationGroup);
        }

        return array();
    }

    /**
     * Load attribute matching from variantmatching table (category template)
     * @param string $category Category ID
     * @return array Attribute matching data (decoded JSON)
     */
    public function loadFromVariantMatchingTable($category) {
        $oDB = MagnaDB::gi();

        if (empty($category) || $category === 'none') {
            return array();
        }

        $row = $oDB->fetchRow("
            SELECT ShopVariation as data
            FROM " . TABLE_MAGNA_TEMU_VARIANTMATCHING . "
            WHERE mpID = " . (int)$this->mpID . "
              AND MpIdentifier = '" . $oDB->escape($category) . "'
        ");

        if (empty($row) || empty($row['data'])) {
            return array();
        }

        $decoded = json_decode($row['data'], true);
        return is_array($decoded) ? $decoded : array();
    }

    /**
     * Get current attribute matching configuration.
     * Routes to appropriate table based on product availability.
     * @return array Attribute matching rules
     */
    public function getAttributeMatching() {
        // The standalone Attributes Matching tab (view=varmatch) always edits the
        // category-level template, never per-product data.
        $blVarmatchTab = (isset($_GET['view']) && $_GET['view'] === 'varmatch');

        if (!$blVarmatchTab) {
            // Product-specific screens. Prefer the Product Preparation selection
            // ('prepare'), then the Create-New-Products selection ('apply'). When a
            // product is found, loadFromApplyTable() prefers its per-product
            // prepare_longtext row and only falls back to the category template when
            // that row is empty.
            $pid = $this->getFirstSelectionPID(array('prepare', 'apply'));
            if ($pid > 0) {
                return $this->loadFromApplyTable($pid);
            }
        }

        // GLOBAL TEMPLATE MODE: Load from variantmatching table
        $variationGroup = null;
        if (isset($_POST['ml']['variationGroup'])) {
            $variationGroup = $_POST['ml']['variationGroup'];
        } else if (isset($_POST['mainCategory'])) {
            $variationGroup = $_POST['mainCategory'];
        } else if (isset($_POST['PrimaryCategory'])) {
            $variationGroup = $_POST['PrimaryCategory'];
        }

        return $this->loadFromVariantMatchingTable($variationGroup);
    }

    /**
     * Resolve the first selected product id for the current session, trying each
     * given selection name in priority order. Returns 0 when none have a selection.
     *
     * @param array $aSelectionNames Selection names to try, in priority order.
     * @return int First product id, or 0 when nothing is selected.
     */
    private function getFirstSelectionPID($aSelectionNames) {
        $aPIDs = $this->getSelectionPIDs($aSelectionNames);
        return !empty($aPIDs) ? (int)$aPIDs[0] : 0;
    }

    /**
     * Remove child attributes orphaned by the current parent selection from the
     * merged blob before it is persisted.
     *
     * The save merge is non-destructive (it never removes a key simply absent from
     * the incoming payload), so a child whose parent value changed would otherwise
     * linger in the stored record — including legacy children left over after the
     * marketplace attribute-id format changed. The parent-child metadata is loaded
     * from GetCategoryDetails (cached 24h; same call filterToCategoryKeys already
     * makes). Fail-open: on the category-independent group / `none` / a missing
     * category / an API-cache miss / no hierarchy the data is returned unchanged.
     *
     * @param array  $aData    Merged matching blob: plain name => {Code, Values}.
     * @param string $category Prepared marketplace category id.
     * @return array
     */
    private function pruneStaleChildAttributes($aData, $category) {
        if (!is_array($aData) || empty($aData) || empty($category)
            || $category === 'none' || $category === 'category_independent_attributes') {
            return $aData;
        }
        require_once(DIR_MAGNALISTER_MODULES . 'temu/TemuHelper.php');
        $aCatDetails = TemuHelper::getCategoryAttributes($this->mpID, $category);
        if (!is_array($aCatDetails) || empty($aCatDetails)) {
            return $aData; // fail open on API/cache miss
        }
        return TemuParentChildVisibility::filterStaleChildren($aData, $aCatDetails);
    }

    /**
     * Drop attribute keys that do not belong to $category so a stored matching blob stays
     * scoped to a single category. Fails open (returns data unchanged) when the category's
     * key set is unavailable (API/cache miss) or for the category-independent template.
     *
     * @param array       $aData    Attribute matching blob (key => matching entry)
     * @param string|null $category Temu category identifier
     * @return array
     */
    private function filterToCategoryKeys($aData, $category) {
        if (empty($category) || $category === 'none' || $category === 'category_independent_attributes') {
            return $aData;
        }
        require_once(DIR_MAGNALISTER_MODULES . 'temu/TemuHelper.php');
        $aValidKeys = TemuHelper::getCategoryAttributeKeys($this->mpID, $category);
        if ($aValidKeys === null) {
            return $aData; // fail open — never drop everything on an API/cache miss
        }
        $aOut = array();
        foreach ($aData as $sKey => $mVal) {
            if (isset($aValidKeys[$sKey])) {
                $aOut[$sKey] = $mVal;
            }
        }
        return $aOut;
    }

    /**
     * Save attribute matching to variantmatching table (category template)
     * @param array $attributeMatching New attribute matching data to merge
     * @param string $category Category ID
     * @return bool Success status
     */
    public function saveToVariantMatchingTable($attributeMatching, $category) {
        $oDB = MagnaDB::gi();

        if (empty($category) || $category === 'none') {
            return false;
        }

        // Load existing data and merge
        $existingData = $this->loadFromVariantMatchingTable($category);

        foreach ($attributeMatching as $key => $value) {
            if ($value === null) {
                unset($existingData[$key]);
            } else {
                $existingData[$key] = $value;
            }
        }

        // Keep the stored blob scoped to this category — drop any keys carried over from a
        // different category (prevents cross-category contamination of the template).
        $existingData = $this->filterToCategoryKeys($existingData, $category);

        // Drop child attributes orphaned by the current parent selection before persisting.
        $existingData = $this->pruneStaleChildAttributes($existingData, $category);

        $jsonData = json_encode($existingData);

        $batchData = array(
            array(
                'mpID'             => $this->mpID,
                'MpIdentifier'     => $category,
                'ShopVariation'    => $jsonData,
                'MarketplaceVariation' => '',
                'CustomIdentifier' => '',
            )
        );

        $oDB->batchinsert(TABLE_MAGNA_TEMU_VARIANTMATCHING, $batchData, false,
            array('ShopVariation')
        );

        return true;
    }

    /**
     * Save attribute matching to Temu prepare longtext table (product-specific)
     *
     * @param array $attributeMatching New attribute matching data to merge
     * @param array $options Optional parameters: variationGroup, variationTheme
     * @return bool Success status
     */
    public function saveToApplyTable($attributeMatching, $options = array()) {
        $oDB = MagnaDB::gi();

        if (empty($this->productIDs)) {
            return false;
        }

        // Load existing data for the first product and merge
        $existingData = $this->loadFromApplyTable($this->productID);

        foreach ($attributeMatching as $key => $value) {
            if ($value === null) {
                unset($existingData[$key]);
            } else {
                $existingData[$key] = $value;
            }
        }

        // Get options
        $variationGroup = isset($options['variationGroup']) ? $options['variationGroup'] : null;
        $variationTheme = isset($options['variationTheme']) ? $options['variationTheme'] : null;

        // Keep the per-product blob scoped to the product's category — drop keys left over
        // from a previously-matched category (root fix for cross-category contamination).
        $existingData = $this->filterToCategoryKeys($existingData, $variationGroup);

        // Drop child attributes orphaned by the current parent selection before persisting.
        $existingData = $this->pruneStaleChildAttributes($existingData, $variationGroup);

        $jsonData = json_encode($existingData);

        // Save for each product
        foreach ($this->productIDs as $pID) {
            // Preserve the existing category-independent column for this product;
            // this save only owns the category-dependent ShopVariationId.
            $sExistingCI = (string)$oDB->fetchOne("
                SELECT CategoryIndependentShopVariationId
                  FROM " . TABLE_MAGNA_TEMU_PREPARE_LONGTEXT . "
                 WHERE mpID = " . (int)$this->mpID . "
                   AND products_id = " . (int)$pID . "
            ");

            $oDB->query("
                DELETE FROM " . TABLE_MAGNA_TEMU_PREPARE_LONGTEXT . "
                WHERE mpID = " . (int)$this->mpID . "
                  AND products_id = " . (int)$pID . "
            ");

            $oDB->query("
                INSERT INTO " . TABLE_MAGNA_TEMU_PREPARE_LONGTEXT . "
                (mpID, products_id, ShopVariationId, CategoryIndependentShopVariationId)
                VALUES (
                    " . (int)$this->mpID . ",
                    " . (int)$pID . ",
                    '" . $oDB->escape($jsonData) . "',
                    '" . $oDB->escape($sExistingCI) . "'
                )
            ");

            // Update prepare table if category is provided
            if (!empty($variationGroup)) {
                $existingRow = $oDB->fetchRow("
                    SELECT products_id
                    FROM " . TABLE_MAGNA_TEMU_PREPARE . "
                    WHERE mpID = " . (int)$this->mpID . "
                      AND products_id = " . (int)$pID . "
                ");

                if (!empty($existingRow)) {
                    // Update existing prepare row with category and variation theme
                    $updateData = array(
                        'PrimaryCategory' => $variationGroup,
                    );

                    if ($variationTheme !== null) {
                        $updateData['variation_theme'] = $variationTheme;
                    }

                    $oDB->update(TABLE_MAGNA_TEMU_PREPARE, $updateData, array(
                        'mpID' => $this->mpID,
                        'products_id' => (int)$pID,
                    ));
                }
            }
        }

        return true;
    }

    /**
     * Save attribute matching data from React component.
     * Routes to appropriate table based on productID.
     *
     * @param array $attributeMatching Attribute matching rules from React
     * @return bool Success status
     */
    public function saveAttributeMatching($attributeMatching) {
        if ($this->productID === 0) {
            // GLOBAL TEMPLATE MODE: Save to variantmatching table
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

        // PRODUCT-SPECIFIC MODE
        $options = array(
            'variationGroup' => isset($_POST['ml']['variationGroup']) ? $_POST['ml']['variationGroup'] : null,
            'variationTheme' => isset($_POST['ml']['variationTheme']) ? $_POST['ml']['variationTheme'] : null
        );

        return $this->saveToApplyTable($attributeMatching, $options);
    }

    /**
     * Validate attribute matching data
     * @param array $attributeMatching Attribute matching rules
     * @return array Validation result with 'valid' boolean and 'errors' array
     */
    public function validateAttributeMatching($attributeMatching) {
        $errors = array();

        if (!is_array($attributeMatching)) {
            $errors[] = 'Invalid data format';
            return array('valid' => false, 'errors' => $errors);
        }

        $firstKey = array_keys($attributeMatching);
        if (empty($firstKey)) {
            return array('valid' => true, 'errors' => array());
        }

        // Detect V3 format (React frontend always uses string keys)
        $isV3Format = (bool) count(array_filter($firstKey, 'is_string'));

        if ($isV3Format) {
            foreach ($attributeMatching as $attrKey => $attrData) {
                if ($attrData === null) {
                    continue; // Null means delete
                }

                if (!is_array($attrData)) {
                    $errors[] = "Attribute '$attrKey': Invalid data format";
                    continue;
                }

                if (!isset($attrData['Code'])) {
                    $errors[] = "Attribute '$attrKey': Missing 'Code' field";
                }
            }

            return array('valid' => count($errors) === 0, 'errors' => $errors);
        }

        // V2 format validation (legacy)
        foreach ($attributeMatching as $index => $mapping) {
            if (!is_array($mapping)) {
                $errors[] = "Rule #" . ((int)$index + 1) . ": Invalid entry";
                continue;
            }

            if (!isset($mapping['marketplaceAttribute']) || empty($mapping['marketplaceAttribute'])) {
                $errors[] = "Rule #" . ((int)$index + 1) . ": Marketplace attribute is required";
            }

            if (!isset($mapping['shopAttribute'])) {
                $errors[] = "Rule #" . ((int)$index + 1) . ": Shop attribute is required";
            }
        }

        return array(
            'valid'  => empty($errors),
            'errors' => $errors
        );
    }

    /**
     * Get translations for React component
     * @return array Translations
     */
    public function getTranslations() {
        require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/AttributesMatchingHelper.php');

        $helper = new AttributesMatchingHelper($this->mpID);
        $translations = $helper->getVarMatchTranslations();

        $translations['addRule'] = defined('ML_GENERAL_VARMATCH_ADD_RULE') ? ML_GENERAL_VARMATCH_ADD_RULE : 'Add Rule';
        $translations['deleteRule'] = defined('ML_GENERAL_VARMATCH_DELETE_RULE') ? ML_GENERAL_VARMATCH_DELETE_RULE : 'Delete Rule';
        $translations['saveSuccess'] = defined('ML_GENERAL_VARMATCH_SAVE_SUCCESS') ? ML_GENERAL_VARMATCH_SAVE_SUCCESS : 'Attribute matching saved successfully!';
        $translations['enterFreetext'] = defined('ML_GENERAL_VARMATCH_ENTER_FREETEXT') ? ML_GENERAL_VARMATCH_ENTER_FREETEXT : 'Enter custom value';
        $translations['pleaseSelect'] = html_entity_decode($translations['pleaseSelect'], ENT_QUOTES, 'UTF-8');
        $translations['valueMatchingTitle'] = html_entity_decode(defined('ML_GENERAL_VARMATCH_VALUE_MATCHING_TITLE') ? ML_GENERAL_VARMATCH_VALUE_MATCHING_TITLE : 'Value Matching', ENT_QUOTES, 'UTF-8');
        $translations['valueMatchingDescription'] = html_entity_decode(defined('ML_GENERAL_VARMATCH_VALUE_MATCHING_DESCRIPTION') ? ML_GENERAL_VARMATCH_VALUE_MATCHING_DESCRIPTION : 'Match your shop attribute values with Temu attribute values:', ENT_QUOTES, 'UTF-8');

        // The shared React bundle (AmazonVariations) reads several i18n keys still named
        // after Amazon and falls back to hard-coded "Amazon ..." strings when they are
        // missing. Provide Temu-worded overrides so the value-matching UI shows "Temu"
        // instead of "Amazon" (column header, value dropdown/placeholders, description).
        $sMp = 'Temu';
        $translations['valueMatchingDescription'] = str_ireplace('Amazon', $sMp, $translations['valueMatchingDescription']);
        $translations['amazonValueColumn']        = $sMp . ' Value';
        $translations['selectAmazonValue']        = 'Select ' . $sMp . ' value...';
        $translations['enterAmazonValue']         = 'Enter ' . $sMp . ' value';
        $translations['enterCustomAmazonValue']   = 'Enter custom ' . $sMp . ' value';

        // Database value input translations
        $translations['databaseTableLabel'] = defined('ML_GENERAL_VARMATCH_DATABASE_TABLE_LABEL') ? ML_GENERAL_VARMATCH_DATABASE_TABLE_LABEL : 'Table';
        $translations['databaseColumnLabel'] = defined('ML_GENERAL_VARMATCH_DATABASE_COLUMN_LABEL') ? ML_GENERAL_VARMATCH_DATABASE_COLUMN_LABEL : 'Column';
        $translations['databaseAliasLabel'] = defined('ML_GENERAL_VARMATCH_DATABASE_ALIAS_LABEL') ? ML_GENERAL_VARMATCH_DATABASE_ALIAS_LABEL : 'Alias';
        $translations['databaseTablePlaceholder'] = defined('ML_GENERAL_VARMATCH_DATABASE_TABLE_PLACEHOLDER') ? ML_GENERAL_VARMATCH_DATABASE_TABLE_PLACEHOLDER : 'Enter table name';
        $translations['databaseColumnPlaceholder'] = defined('ML_GENERAL_VARMATCH_DATABASE_COLUMN_PLACEHOLDER') ? ML_GENERAL_VARMATCH_DATABASE_COLUMN_PLACEHOLDER : 'Enter column name';
        $translations['databaseAliasPlaceholder'] = defined('ML_GENERAL_VARMATCH_DATABASE_ALIAS_PLACEHOLDER') ? ML_GENERAL_VARMATCH_DATABASE_ALIAS_PLACEHOLDER : 'Enter product ID alias';
        $translations['clearAllMatchings'] = defined('ML_GENERAL_VARMATCH_CLEAR_ALL_MATCHINGS') ? html_entity_decode(ML_GENERAL_VARMATCH_CLEAR_ALL_MATCHINGS) : 'Clear all matchings';

        // strictSave error toast (BUG-019): shown when a save did not persist. Without this
        // key the React bundle falls back to its hard-coded English string.
        $translations['saveFailed'] = html_entity_decode(
            defined('ML_TEMU_VARMATCH_SAVE_FAILED')
                ? ML_TEMU_VARMATCH_SAVE_FAILED
                : 'Saving the attribute matching failed &mdash; your changes have NOT been saved. Please try again.',
            ENT_QUOTES, 'UTF-8');

        return $translations;
    }

    /**
     * Get database tables and their columns for database_value matching
     *
     * @return array Array with 'tables' list and 'columns' map (tableName => columns[])
     */
    public function getDatabaseTablesAndColumns() {
        $oDB = MagnaDB::gi();

        $allTables = $oDB->fetchArray("SHOW TABLES", true);

        if (empty($allTables)) {
            return array(
                'tables'  => array(),
                'columns' => array()
            );
        }

        $excludePrefixes = array(
            'magnalister_',
            'magna_',
            'ml_'
        );

        $tables = array();
        $columns = array();

        foreach ($allTables as $tableName) {
            $skip = false;
            foreach ($excludePrefixes as $prefix) {
                if (stripos($tableName, $prefix) === 0) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) {
                continue;
            }

            $tables[] = $tableName;

            $tableColumns = $oDB->fetchArray("SHOW COLUMNS FROM `" . $oDB->escape($tableName) . "`");
            if (!empty($tableColumns)) {
                $columns[$tableName] = array();
                foreach ($tableColumns as $col) {
                    if (isset($col['Field'])) {
                        $columns[$tableName][] = $col['Field'];
                    } elseif (is_array($col) && isset($col[0])) {
                        $columns[$tableName][] = $col[0];
                    }
                }
            }
        }

        sort($tables);

        return array(
            'tables'  => $tables,
            'columns' => $columns
        );
    }

    /**
     * Get configuration options for React component
     * @return array Configuration
     */
    /**
     * Fetch category-independent attributes from Temu API (Brand, EAN, Weight, etc.)
     * These are displayed in a separate React component instance below the category-dependent attributes.
     * Cached for 24 hours.
     *
     * @return array Marketplace attributes in React format
     */
    public function getCategoryIndependentAttributes() {
        $attributes = array();
        try {
            $result = MagnaConnector::gi()->submitRequestCached(array(
                'ACTION' => 'GetCategoryIndependentAttributes',
                'SUBSYSTEM' => 'Temu',
                'MARKETPLACEID' => $this->mpID,
            ), 86400);

            if (!empty($result['DATA']['attributes']) && is_array($result['DATA']['attributes'])) {
                foreach ($result['DATA']['attributes'] as $key => $attr) {
                    $attrTitle = isset($attr['title']) ? $attr['title'] : (isset($attr['name']) ? $attr['name'] : $key);
                    $attrValues = array();
                    if (!empty($attr['values']) && is_array($attr['values'])) {
                        // Preserve the option id: the API sends `values` either as a
                        // map {id: label} (Shipping Template, Brand, Country of Origin,
                        // Tax Code, GPSR, …) or as a {id, name} object list. The id is
                        // the value Temu expects; the label is display-only. Iterating
                        // by value alone (as `as $val`) dropped the map key, so the label
                        // got stored+submitted instead of the id.
                        foreach ($attr['values'] as $vKey => $val) {
                            if (is_array($val)) {
                                $vId  = isset($val['id']) ? (string)$val['id'] : (isset($val['specId']) ? (string)$val['specId'] : '');
                                $vName = isset($val['name']) ? $val['name'] : (isset($val['specName']) ? $val['specName'] : $vId);
                                $attrValues[$vId] = $vName;
                            } else {
                                // Map {id: label} -> $vKey is the id. A sequential list
                                // [label, …] yields integer indices, which match the
                                // API's own 0/1/2 handling-time ids (json_encode collapses
                                // that map to a JSON array), so the index is the correct id.
                                $attrValues[(string)$vKey] = (string)$val;
                            }
                        }
                    }
                    $attributes[$key] = array(
                        'value'    => $attrTitle,
                        'required' => !empty($attr['mandatory']),
                        'dataType' => isset($attr['type']) ? $attr['type'] : 'text',
                        'desc'     => isset($attr['desc']) ? $attr['desc'] : '',
                        'values'   => $attrValues,
                    );
                }
            }
        } catch (MagnaException $e) {
            $e->setCriticalStatus(false);
        }
        return $attributes;
    }

    /**
     * Load category-independent attribute matching from variantmatching table.
     * Uses 'category_independent_attributes' as the MpIdentifier.
     *
     * @return array Saved attribute matching data
     */
    public function loadCategoryIndependentMatching() {
        // Per-product: prefer this product's saved CI JSON; fall back to the global
        // category-independent template for first-open prefill.
        if ($this->productID > 0) {
            $row = MagnaDB::gi()->fetchRow("
                SELECT CategoryIndependentShopVariationId
                  FROM " . TABLE_MAGNA_TEMU_PREPARE_LONGTEXT . "
                 WHERE mpID = " . (int)$this->mpID . "
                   AND products_id = " . (int)$this->productID . "
            ");
            if (!empty($row) && !empty($row['CategoryIndependentShopVariationId'])) {
                $decoded = json_decode($row['CategoryIndependentShopVariationId'], true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }
        $row = MagnaDB::gi()->fetchRow('
            SELECT ShopVariation
              FROM ' . TABLE_MAGNA_TEMU_VARIANTMATCHING . '
             WHERE mpID = "' . MagnaDB::gi()->escape($this->mpID) . '"
               AND MpIdentifier = "category_independent_attributes"
        ');
        if (!empty($row) && !empty($row['ShopVariation'])) {
            $decoded = json_decode($row['ShopVariation'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return array();
    }

    /**
     * Save category-independent attribute matching. Per-product (productID>0) writes
     * prepare_longtext.CategoryIndependentShopVariationId; global template mode
     * (productID=0) writes the variantmatching category_independent_attributes row.
     *
     * @param array $attributeMatching Attribute matching data
     */
    public function saveCategoryIndependentMatching($attributeMatching) {
        $existing = $this->loadCategoryIndependentMatching();
        $merged = array_merge($existing, $attributeMatching);
        foreach ($merged as $key => $val) {
            if ($val === null) {
                unset($merged[$key]);
            }
        }
        $json = json_encode($merged);
        $oDB = MagnaDB::gi();

        if ($this->productID > 0) {
            // Per-product: write this product's CI column, preserving ShopVariationId.
            $sExistingShop = (string)$oDB->fetchOne("
                SELECT ShopVariationId
                  FROM " . TABLE_MAGNA_TEMU_PREPARE_LONGTEXT . "
                 WHERE mpID = " . (int)$this->mpID . "
                   AND products_id = " . (int)$this->productID . "
            ");
            $oDB->query("
                DELETE FROM " . TABLE_MAGNA_TEMU_PREPARE_LONGTEXT . "
                WHERE mpID = " . (int)$this->mpID . "
                  AND products_id = " . (int)$this->productID . "
            ");
            $oDB->query("
                INSERT INTO " . TABLE_MAGNA_TEMU_PREPARE_LONGTEXT . "
                (mpID, products_id, ShopVariationId, CategoryIndependentShopVariationId)
                VALUES (
                    " . (int)$this->mpID . ",
                    " . (int)$this->productID . ",
                    '" . $oDB->escape($sExistingShop) . "',
                    '" . $oDB->escape($json) . "'
                )
            ");
            return true;
        }

        // Global template mode (productID = 0): unchanged behavior.
        $batchData = array(
            array(
                'mpID'                 => $this->mpID,
                'MpIdentifier'         => 'category_independent_attributes',
                'ShopVariation'        => $json,
                'MarketplaceVariation' => '',
                'CustomIdentifier'     => '',
            )
        );
        $oDB->batchinsert(TABLE_MAGNA_TEMU_VARIANTMATCHING, $batchData, false, array('ShopVariation'));
        return true;
    }

    public function getConfig() {
        return array(
            'mpID'              => $this->mpID,
            'productID'         => $this->productID,
            'saveUrl'           => toURL(array(
                'mp'     => 'temu',
                'mode'   => 'prepare',
                'action' => 'saveAttributeMatching'
            ), true),
            'allowCustomValues' => true,
            'autoSave'          => false
        );
    }
}
