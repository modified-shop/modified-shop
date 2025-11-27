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
 * (c) 2010 - 2016 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
// äöüß

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/prepare/VariationMatching.php');
require_once(DIR_MAGNALISTER_MODULES . 'amazon/AmazonHelper.php');

class AmazonVariationMatching extends VariationMatching
{
    /**
     * @return AmazonHelper
     */
    protected function getAttributesMatchingHelper()
    {
        return AmazonHelper::gi();
    }

    /**
     * Fetches the options for the top 20 category selectors
     * @param string $sType
     *     Type of category (PrimaryCategory, SecondaryCategory, StoreCategory, StoreCategory2, StoreCategory3)
     * @param string $sCategory
     *     the selected category (empty for newly prepared items)
     * @returns string
     *     option tags for the select element
     */
    protected function renderCategoryOptions($sType, $sCategory)
    {
        $categories = array('DATA' => array());
        try {
            $categories = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'GetAllProductTypes',
            ));
        } catch (MagnaException $e) {
            //echo print_m($e->getErrorArray(), 'Error: '.$e->getMessage(), true);
        }

        $htmlCategories = renderAmazonTopTen('topMainCategory');
        $htmlCategories .= '<optgroup label="' . ML_LABEL_CATEGORY . '">';
        if (!empty($categories['DATA'])) {
            foreach ($categories['DATA'] as $catKey => $catName) {
                $catName = fixHTMLUTF8Entities($catName);
                if ($catKey === $sCategory) {
                    $htmlCategories .= '<option value="' . $catKey . '" selected="selected">' . $catName . '</option>';
                } else {
                    $htmlCategories .= '<option value="' . $catKey . '">' . $catName . '</option>';
                }
            }
        }
        $htmlCategories .= '</optgroup>';
        return $htmlCategories;
    }

    /**
     * Override renderMatchingTable to use React component
     * Renders category selector + React attribute matching component
     * @param string $categoryId Category ID
     * @return string HTML output
     */
    protected function renderMatchingTable($categoryId = '') {
        //return parent::renderMatchingTable($categoryId);//uncomment to use old attribute matching
        // Get category options HTML from parent
        $categoryOptions = $this->renderCategoryOptions('MarketplaceCategories', $categoryId);

        // Get variation theme from POST or session
        $variationTheme = 'none';
        if (!empty($_POST['variationTheme'])) {
            $variationTheme = $_POST['variationTheme'];
        } elseif (!empty($_POST['ml']['variationTheme'])) {
            $variationTheme = $_POST['ml']['variationTheme'];
        }

        // Get main category
        $mainCategory = $categoryId;
        if (empty($mainCategory) && !empty($_POST['PrimaryCategory'])) {
            $mainCategory = $_POST['PrimaryCategory'];
        }

        // Build data array for React component
        $data = array(
                'MainCategory'   => $mainCategory,
                'VariationTheme' => $variationTheme,
                'ProductType'    => $mainCategory
        );

        // Use product ID 0 for multi-product matching (variation group matching)
        $productID = 0;
        if (!empty($_POST['productID'])) {
            $productID = (int)$_POST['productID'];
        }

        // Load React-based variation matching renderer
        require_once(DIR_MAGNALISTER_MODULES . 'amazon/application/applicationviews_react.php');

        // Get React component HTML using TEMPLATE version (productID = 0, no product-specific data)
        // Pass URL resources so API endpoint points to this view (view=varmatch)
        $reactComponentHtml = renderReactVariationMatchingTemplate($mainCategory, $this->resources['url']);
        $translate = array(
                'mpTitle'                  => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_TITLE),
                'mpAttributeTitle'         => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_ATTRIBUTE),
                'mpOptionalAttributeTitle' => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE),
                'mpCustomAttributeTitle'   => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE),
        );

        // Build complete HTML: Category selector + React component
        ob_start();
        ?>
        <form method="post" id="matchingForm" action="<?php echo toURL($this->resources['url'], array(), true); ?>">
            <table id="variationMatcher" class="attributesTable">
                <tbody>
                <tr class="headline">
                    <td colspan="3"><h4><?php echo $translate['mpTitle'] ?></h4></td>
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
                                <td class="buttons">

                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td class="info">

                    </td>
                </tr>
                <tr class="spacer">
                    <td colspan="3">&nbsp;
                    </td>
                </tr>
                </tbody>

                <!-- Load React Component scripts AFTER form (so #PrimaryCategory exists in DOM) -->
                <?php echo $reactComponentHtml; ?>

            </table>
            <!-- Action Buttons (Footer) -->
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
                                        <?php echo ML_GENERAL_VARMATCH_RESET_MATCHING ?>
                                    </button>
                                </td>
                                <td></td>
                                <td class="lastChild">
                                    <input type="button" value="<?php echo ML_GENERAL_VARMATCH_SAVE_BUTTON ?>"
                                           class="ml-button mlbtn-action">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
                <tbody>

                <tr class="spacer">
                    <td colspan="3">&nbsp;
                    </td>
                </tr>
                </tbody>
            </table>
        </form>

        <?php

        return ob_get_clean();
    }

    /**
     * Override renderJs to prevent loading legacy variation_matching.js scripts
     * React component includes its own JavaScript
     * @return string JavaScript for form submit handling
     */
    protected function renderJs() {
        //return parent::renderJs();//uncomment to use old attribute matching
        // Don't load legacy variation_matching.js scripts
        // React component handles all JavaScript internally

        // Add script to prevent form submit and reset category selector
        ob_start();
        ?>
        <script type="text/javascript">
            (function ($) {
                $(document).ready(function () {
                    // Handle submit button click to trigger batch save
                    $('.mlbtn-action[type="button"]').on('click', function () {
                        console.log('[Variation Matching] Save button clicked');

                        // Check if React save function is available
                        if (typeof window.magnalisterSaveAmazonVariations === 'function') {
                            // Trigger React batch save with callback
                            window.magnalisterSaveAmazonVariations(function () {
                                console.log('[Variation Matching] Save completed, resetting category');
                                // Reset PrimaryCategory to "null" (Please select) after save
                                $('#PrimaryCategory').val('null').trigger('change');
                            });
                        } else {
                            console.error('[Variation Matching] React save function not found');
                            // Reset anyway
                            $('#PrimaryCategory').val('null').trigger('change');
                        }
                    });

                    // Handle reset button click to clear all matched attributes
                    $('.ml-reset-matching').on('click', function () {
                        console.log('[Variation Matching] Reset button clicked');

                        // Check if category is selected
                        var selectedCategory = $('#PrimaryCategory').val();
                        if (!selectedCategory || selectedCategory === 'null' || selectedCategory === '') {
                            console.log('[Variation Matching] No category selected, nothing to reset');
                            alert('<?php echo addslashes(ML_GENERAL_VARMATCH_SELECT_CATEGORY ?? 'Please select a category first'); ?>');
                            return;
                        }

                        // Confirm with user
                        if (!confirm('<?php echo addslashes(ML_GENERAL_VARMATCH_RESET_MATCHING_CONFIRM ?? 'Reset all matched attributes for this category?'); ?>')) {
                            console.log('[Variation Matching] Reset cancelled by user');
                            return;
                        }

                        // Send AJAX request to delete record from database
                        $.ajax({
                            url: '<?php echo toURL($this->resources['url'], array('kind' => 'ajax'), true); ?>',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                'ml[action]': 'resetAttributeMatching',
                                'ml[variationGroup]': selectedCategory,
                                'mpID': <?php echo $this->mpId; ?>
                            },
                            success: function (response) {
                                if (response.success) {
                                    console.log('[Variation Matching] Reset successful');
                                    alert('<?php echo addslashes(defined('ML_GENERAL_VARMATCH_RESET_SUCCESS') ? ML_GENERAL_VARMATCH_RESET_SUCCESS : 'Attribute matching reset successfully'); ?>');
                                    // Reload attributes by triggering category change
                                    $('#PrimaryCategory').trigger('change');
                                } else {
                                    console.error('[Variation Matching] Reset failed:', response.message);
                                    alert('<?php echo addslashes(defined('ML_GENERAL_VARMATCH_RESET_ERROR') ? ML_GENERAL_VARMATCH_RESET_ERROR : 'Error'); ?>: ' + response.message);
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error('[Variation Matching] AJAX error:', error);
                                alert('<?php echo addslashes(defined('ML_GENERAL_VARMATCH_RESET_ERROR') ? ML_GENERAL_VARMATCH_RESET_ERROR : 'Error'); ?>: ' + error);
                            }
                        });
                    });

                    // Prevent form submit in general attribute matching
                    $('#matchingForm').on('submit', function (e) {
                        e.preventDefault();
                        return false;
                    });
                });
            })(jQuery);
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Override saveMatching to handle React component saves
     * @param bool $redirect Whether to redirect after save
     */
    protected function saveMatching($redirect = true) {
        // Check if this is a React component save (V3 format)
        if (isset($_POST['ml']['action']) && ($_POST['ml']['action'] === 'saveAttributeMatching' || $_POST['ml']['action'] === 'saveAttributeMatchingBatch')) {

            // Load React save handler
            require_once(DIR_MAGNALISTER_MODULES . 'amazon/application/applicationviews_react.php');

            if ($_POST['ml']['action'] === 'saveAttributeMatchingBatch') {
                handleSaveAttributeMatchingBatch();
            } else {
                handleSaveAttributeMatching();
            }
            return;
        }

        // Fall back to parent implementation for legacy format
        parent::saveMatching($redirect);
    }

    /**
     * Override renderAjax to handle React component AJAX calls
     * Routes React calls to dedicated handlers, legacy calls to parent
     */
    public function renderAjax() {
        // Check if this is a React component AJAX call
        // React component uses $_POST['ml']['action'] for save operations and $_POST['type'] for get operations
        if (isset($_POST['ml']['action']) || isset($_POST['type'])) {
            $this->handleReactAjax();
            return;
        }

        // Fall back to parent implementation for legacy AJAX calls
        parent::renderAjax();
    }

    /**
     * Handle React component AJAX calls
     * Separated from apply.php to maintain complete independence
     */
    private function handleReactAjax() {
        global $_MagnaSession;

        // Load React AJAX handlers
        require_once(DIR_MAGNALISTER_MODULES . 'amazon/application/applicationviews_react.php');
        require_once(DIR_MAGNALISTER_MODULES . 'amazon/classes/ReactHelper.php');

        // Check for ml[action] format (save operations)
        if (isset($_POST['ml']['action'])) {
            $action = $_POST['ml']['action'];

            // Handle resetAttributeMatching (delete all matched attributes for category)
            if ($action === 'resetAttributeMatching') {
                if (!isset($_POST['ml']['variationGroup']) || !isset($_POST['mpID'])) {
                    die(json_encode(array('success' => false, 'message' => 'Missing parameters')));
                }

                $variationGroup = $_POST['ml']['variationGroup'];
                $mpID = (int)$_POST['mpID'];

                // Validate variationGroup is not "none"
                if (empty($variationGroup) || $variationGroup === 'none') {
                    die(json_encode(array(
                            'success' => false,
                            'message' => 'Please select a valid product type'
                    )));
                }

                try {
                    // Delete record from magnalister_amazon_variantmatching table
                    MagnaDB::gi()->delete(TABLE_MAGNA_AMAZON_VARIANTMATCHING, array(
                                    'MpId'         => $mpID,
                                    'MpIdentifier' => $variationGroup
                            ));

                    die(json_encode(array(
                            'success' => true,
                            'message' => 'Attribute matching reset successfully'
                    )));
                } catch (Exception $e) {
                    die(json_encode(array(
                            'success' => false,
                            'message' => 'Failed to reset: ' . $e->getMessage()
                    )));
                }
            }

            // Validate mainCategory/variationGroup is not "none" before any save operation
            $variationGroup = isset($_POST['ml']['variationGroup']) ? $_POST['ml']['variationGroup'] : '';
            if (empty($variationGroup) || $variationGroup === 'none') {
                die(json_encode(array(
                        'success' => false,
                        'message' => 'Please select a valid product type before saving'
                )));
            }

            // Handle saveAttributeMatching (V3 format)
            if ($action === 'saveAttributeMatching') {
                handleSaveAttributeMatching();
                return;
            }

            // Handle saveAttributeMatchingBatch (V3 format)
            if ($action === 'saveAttributeMatchingBatch') {
                handleSaveAttributeMatchingBatch();
                return;
            }
        }

        // Check for type format (get operations)
        if (isset($_POST['type'])) {
            $type = $_POST['type'];

            // Handle getReactComponentData (category change)
            if ($type === 'getReactComponentData') {
                if (!isset($_POST['mainCategory']) || !isset($_POST['mpID'])) {
                    die(json_encode(array('success' => false, 'message' => 'Missing parameters')));
                }

                $mainCategory = $_POST['mainCategory'];

                // Validate mainCategory is not "none" before loading data
                if (empty($mainCategory) || $mainCategory === 'none') {
                    die(json_encode(array(
                            'success' => false,
                            'message' => 'Please select a valid product type'
                    )));
                }

                $variationTheme = isset($_POST['variationTheme']) ? $_POST['variationTheme'] : 'none';
                $mpID = (int)$_POST['mpID'];
                $productID = 0; // Always 0 for global template

                try {
                    $helper = new ReactHelper($mpID, $productID);
                    require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/AttributesMatchingHelper.php');
                    $attrHelper = new AttributesMatchingHelper($mpID);

                    // Get data for the new category
                    $shopAttributes = $helper->getShopAttributes();
                    $marketplaceAttributes = $helper->getMarketplaceAttributes($mainCategory);
                    $savedValues = $helper->loadFromVariantMatchingTable($mainCategory);
                    $conditionalRules = $helper->getConditionalRules($mainCategory);

                    // Use VerifyItems API to detect mandatory attributes
                    if (!empty($marketplaceAttributes) && $mainCategory !== 'none') {
                        require_once(DIR_MAGNALISTER_MODULES . 'amazon/AmazonHelper.php');
                        $verifyErrors = AmazonHelper::verifyItemByMarketplaceToGetMandatoryAttributes($mainCategory, $variationTheme);

                        if (!empty($verifyErrors) && is_array($verifyErrors)) {
                            foreach ($verifyErrors as $error) {
                                if (isset($error['ERRORDATA'])) {
                                    $errorData = $error['ERRORDATA'];

                                    // Check if this is a MISSING_ATTRIBUTE error
                                    if (isset($error['ERRORLEVEL']) && $error['ERRORLEVEL'] === 'FATAL' && isset($errorData['error_categories']) && is_array($errorData['error_categories']) && in_array('MISSING_ATTRIBUTE', $errorData['error_categories'], true) && isset($errorData['error_attributeNames']) && is_array($errorData['error_attributeNames'])) {

                                        // Mark these attributes as required
                                        foreach ($errorData['error_attributeNames'] as $attributeName) {
                                            if (isset($marketplaceAttributes[$attributeName])) {
                                                $marketplaceAttributes[$attributeName]['required'] = true;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // V3-like approach: Use SIMPLE renderer (no React, no scripts)
                    require_once(DIR_MAGNALISTER_MODULES . 'amazon/application/variations_simple.php');

                    // Build API endpoint
                    $apiEndpoint = toURL($this->resources['url'], array('kind' => 'ajax'), true);

                    $params = array(
                            'variationGroup'   => $mainCategory,
                            'customIdentifier' => $productID,
                            'variationTheme'   => $variationTheme,
                            'mpID'             => $mpID,
                            'apiEndpoint'      => $apiEndpoint
                    );

                    $html = renderSimpleAttributeMatching($params);

                    die(json_encode(array(
                            'success' => true,
                            'html' => $html  // Return simple HTML
                    )));

                } catch (Exception $e) {
                    die(json_encode(array(
                            'success' => false,
                            'message' => $e->getMessage()
                    )));
                }
            }
        }

        // Unknown AJAX action
        die(json_encode(array('success' => false, 'message' => 'Unknown AJAX action')));
    }
}
