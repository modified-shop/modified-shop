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
 * (c) 2010 - 2025 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or defined('_VALID_XTC_MODULE_CALL') or defined('MAGNALISTER_PLUGIN') or die('Direct Access to this location is not allowed.');

/**
 * Simple V3-like Variations Renderer
 * Renders ONLY simple HTML for testing (no React, no scripts)
 *
 * This file will later contain the full V3 variations.php logic
 * For now, it just returns a simple test div
 */

/**
 * Render attribute matching HTML with React component
 * V3-like approach: Load data and render React component
 *
 * @param array $params Parameters (variationGroup, customIdentifier, variationTheme, mpID, productID)
 * @return string HTML output with React component
 */
function renderSimpleAttributeMatching($params = array()) {
    global $_MagnaSession;

    // Extract parameters
    $variationGroup = isset($params['variationGroup']) ? $params['variationGroup'] : 'none';
    $customIdentifier = isset($params['customIdentifier']) ? (int)$params['customIdentifier'] : 0;
    $variationTheme = isset($params['variationTheme']) ? $params['variationTheme'] : 'none';
    $mpID = isset($params['mpID']) ? (int)$params['mpID'] : (int)$_MagnaSession['mpID'];
    $productID = $customIdentifier; // productID same as customIdentifier

    // TEST MODE: Show simple test div if requested
    if (isset($params['testMode']) && $params['testMode'] === true) {
        ob_start();
        ?>
        <tbody id="amazon-variations-root" class="amazon-variations-container" style="display: contents;">
        <tr>
            <td colspan="4" style="padding: 20px; text-align: center; background: #f0f8ff; border: 2px solid #4CAF50;">
                <h3 style="color: #4CAF50; margin: 0 0 10px 0;">✅ Testing Attribute Matching</h3>
                <p style="margin: 5px 0;"><strong>Variation
                        Group:</strong> <?php echo htmlspecialchars($variationGroup); ?></p>
                <?php if ($variationTheme && $variationTheme !== 'none'): ?>
                    <p style="margin: 5px 0;"><strong>Variation Theme:</strong> <span
                                style="color: #FF5722;"><?php echo htmlspecialchars($variationTheme); ?></span></p>
                <?php endif; ?>
                <p style="margin: 5px 0;"><strong>Product
                        ID:</strong> <?php echo htmlspecialchars($customIdentifier); ?></p>
                <p style="margin: 5px 0; color: #666;">HTML replacement working! 🎉</p>
            </td>
        </tr>
        </tbody>
        <?php
        return ob_get_clean();
    }

    // PRODUCTION MODE: Load data and render React component
    try {
        // Load helper classes
        require_once(DIR_MAGNALISTER_MODULES . 'amazon/classes/ReactHelper.php');
        require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/AttributesMatchingHelper.php');

        $helper = new ReactHelper($mpID, $productID);
        $attrHelper = new AttributesMatchingHelper($mpID);

        // Get data (same as V3)
        $shopAttributes = $helper->getShopAttributes();
        $marketplaceAttributes = $helper->getMarketplaceAttributes($variationGroup);
        $savedValues = $helper->getAttributeMatching();
        $conditionalRules = $helper->getConditionalRules($variationGroup);

        // Call VerifyItems API to detect mandatory attributes (attributes with missing values)
        // This marks attributes that would fail validation as required=true
        if (!empty($marketplaceAttributes) && $variationGroup !== 'none') {
            require_once(DIR_MAGNALISTER_MODULES . 'amazon/AmazonHelper.php');
            $verifyErrors = AmazonHelper::verifyItemByMarketplaceToGetMandatoryAttributes($variationGroup, $variationTheme);

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

        // Build API endpoint URL
        $apiEndpoint = isset($params['apiEndpoint']) ? $params['apiEndpoint'] : '';

        // Build React component props using existing helper
        require_once(DIR_MAGNALISTER_MODULES . 'amazon/application/applicationviews_react.php');
        $reactProps = buildReactComponentProps($mpID, $productID, $variationGroup, $variationTheme, $shopAttributes, $marketplaceAttributes, $savedValues, $conditionalRules, $apiEndpoint);

        // Render React component (V2 structure: tbody + inline script)
        return renderReactComponentHTMLOnly($reactProps);

    } catch (Exception $e) {
        // Error fallback
        ob_start();
        ?>
        <tbody id="amazon-variations-root" class="amazon-variations-container" style="display: contents;">
        <tr>
            <td colspan="4" style="padding: 20px; text-align: center; background: #ffebee; border: 2px solid #f44336;">
                <h3 style="color: #f44336; margin: 0 0 10px 0;"> Error Loading Attributes</h3>
                <p style="margin: 5px 0; color: #666;"><?php echo htmlspecialchars($e->getMessage()); ?></p>
            </td>
        </tr>
        </tbody>
        <?php
        return ob_get_clean();
    }
}

/**
 * Helper function to render ONLY React component HTML (no wrapper scripts)
 * Used by AJAX responses
 */
function renderReactComponentHTMLOnly($reactProps) {
    // Encode props for JavaScript
    $reactDataJson = json_encode($reactProps, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

    ob_start();
    ?>
    <!-- React Variation Matching Container -->
    <tbody id="amazon-variations-root" class="amazon-variations-container" style="display: contents;"></tbody>

    <!-- Initialize React Component -->
    <script type="text/javascript">
        (function () {
            var componentProps = <?php echo $reactDataJson; ?>;
            var container = document.getElementById('amazon-variations-root');

            if (!container) {
                console.error('[AmazonVariations] Container not found');
                return;
            }

            if (typeof window.MagnalisterAmazonVariations === 'undefined') {
                console.error('[AmazonVariations] Component not loaded');
                return;
            }

            var AmazonVariations = window.MagnalisterAmazonVariations.AmazonVariations;
            if (typeof AmazonVariations === 'undefined') {
                console.error('[AmazonVariations] AmazonVariations not found');
                return;
            }

            // Add callbacks
            componentProps.onValuesChange = function (values) {
                console.log('[AmazonVariations] Values changed:', values);
            };

            componentProps.onValidationError = function (errors) {
                console.log('[AmazonVariations] Validation errors:', errors);
                window.amazonVariationsValidationErrors = errors;
            };

            // Render component
            var element = React.createElement(AmazonVariations, componentProps);
            var root = ReactDOM.createRoot(container);
            root.render(element);

            console.log('[AmazonVariations] Component rendered');
        })();
    </script>
    <?php

    return ob_get_clean();
}

/*
 * V3 variations.php CODE (COMMENTED OUT FOR NOW)
 *
 * This will be the full implementation later:
 *
 * // Get variation group and custom identifier
 * $variationGroup = isset($params['variationGroup']) ? $params['variationGroup'] : 'none';
 * $customIdentifier = isset($params['customIdentifier']) ? $params['customIdentifier'] : '0';
 *
 * // Get shop attributes
 * $shopAttributes = getShopAttributes();
 *
 * // Get Amazon marketplace attributes
 * $marketplaceAttributes = getMPVariationAttributes($variationGroup);
 *
 * // Get saved attribute values
 * $savedValues = getAttributeValues($variationGroup, $customIdentifier);
 *
 * // Prepare React component props
 * $reactProps = array(
 *     'variationGroup' => $variationGroup,
 *     'customIdentifier' => $customIdentifier,
 *     'shopAttributes' => $shopAttributes,
 *     'marketplaceAttributes' => $marketplaceAttributes,
 *     'savedValues' => $savedValues,
 *     // ... more props
 * );
 *
 * // Render React component
 * return renderReactComponent($reactProps);
 */