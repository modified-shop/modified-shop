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
 * React-based variation matching renderer
 * This file provides functions for rendering the new React attribute matching component
 *
 * Usage: Include this file and call renderReactVariationMatching() instead of the old
 *        variation_matching.js approach
 */

/**
 * Build React component props
 * @param int $mpID Marketplace ID
 * @param int $productID Product ID
 * @param string $mainCategory Main category/product type
 * @param string $variationTheme Variation theme
 * @param array $shopAttributes Shop attributes
 * @param array $marketplaceAttributes Marketplace attributes
 * @param array $savedValues Saved attribute values
 * @param array $conditionalRules Conditional rules (optional)
 * @param string $apiEndpoint API endpoint URL
 * @return array React component props
 */
function buildReactComponentProps($mpID, $productID, $mainCategory, $variationTheme, $shopAttributes, $marketplaceAttributes, $savedValues, $conditionalRules = array(), $apiEndpoint = '') {
    $debugMode = ((defined('MAGNA_DEBUG') && MAGNA_DEBUG) || (isset($_GET['MLDEBUG']) && $_GET['MLDEBUG'] === 'true'));
    require_once(DIR_MAGNALISTER_MODULES . 'amazon/classes/ReactHelper.php');
    $helper = new ReactHelper($mpID, $productID);

    // Ensure empty arrays become empty objects in JSON (not [])
    if (empty($shopAttributes)) {
        $shopAttributes = new stdClass();
    }
    if (empty($marketplaceAttributes)) {
        $marketplaceAttributes = new stdClass();
    }
    if (empty($savedValues)) {
        $savedValues = new stdClass();
    }

    return array(
            'variationGroup'        => $mainCategory ? $mainCategory : 'none',
            'customIdentifier'      => (string)$productID,
            'variationTheme'        => $variationTheme,
            'marketplaceName'       => 'Amazon',
            'shopAttributes'        => $shopAttributes,
            'marketplaceAttributes' => $marketplaceAttributes,
            'savedValues'           => $savedValues,
            'conditionalRules'      => $conditionalRules,
            'neededFormFields'      => array(
                    'mpID'      => $mpID,
                    'productID' => $productID
            ),
            'i18n'                  => $helper->getTranslations(),
            'databaseTables' => $helper->getDatabaseTablesAndColumns(), // For database_value matching dropdowns
            'apiEndpoint'           => $apiEndpoint,
            'debugMode'             => $debugMode,
            // V2-specific props: render only tbody elements without wrapper table
            'wrapInTable'           => false,
            'hideHelpColumn'        => true
    );
}

/**
 * Unified React HTML Renderer
 * Renders complete HTML with React initialization code
 * Works for both preparation (productID > 0) and global template (productID = 0)
 *
 * @param array $reactProps React component props from buildReactComponentProps()
 * @param int $mpID Marketplace ID
 * @return string Complete HTML with React component and initialization
 */
function renderReactVariationMatchingHTML($reactProps, $mpID) {
    // FIXED container ID (consistent across reloads)
    $componentId = 'amazon-variations-root';

    // Encode props for JavaScript
    $reactDataJson = json_encode($reactProps, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

    // Build HTML output
    ob_start();
    ?>
    <!-- React Variation Matching Container -->
    <tbody id="<?php echo $componentId; ?>" class="amazon-variations-container" style="display: contents;"></tbody>

    <!-- Load React Component CSS (only once) -->
    <link rel="stylesheet"
          href="<?php echo DIR_MAGNALISTER_WS; ?>js/react/AmazonVariations.css?v=<?php echo CLIENT_BUILD_VERSION; ?>">

    <!-- Load React Component Bundle - V2 Wrapper (only once) -->
    <script src="<?php echo DIR_MAGNALISTER_WS; ?>js/react/AmazonVariationsV2.bundle.js?v=<?php echo CLIENT_BUILD_VERSION; ?>"></script>

    <!-- Initialize React Component -->
    <script type="text/javascript">
        (function () {
            var componentProps = <?php echo $reactDataJson; ?>;

            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () {
                    initComponent(componentProps);
                });
            } else {
                initComponent(componentProps);
            }

            function initComponent(props) {
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
                    console.error('[AmazonVariations] AmazonVariations component not found');
                    return;
                }

                // Add callbacks
                props.onValuesChange = function (values) {
                    console.log('[AmazonVariations] Values changed:', values);
                };

                props.onValidationError = function (errors) {
                    console.log('[AmazonVariations] Validation errors:', errors);
                    window.amazonVariationsValidationErrors = errors;
                };

                // Render component
                var element = React.createElement(AmazonVariations, props);
                var root = ReactDOM.createRoot(container);
                root.render(element);

                console.log('[AmazonVariations] Component initialized');
            }
        })();
    </script>
    <?php

    return ob_get_clean();
}

/**
 * Render React variation matching component (LEGACY - calls unified renderer)
 * @param int $productID Product ID (0 for multi-application)
 * @param array $data Product data
 * @return string HTML output
 */
function renderReactVariationMatching($productID, $data) {
    global $_MagnaSession, $applyAction, $_url;
    $debugMode = ((defined('MAGNA_DEBUG') && MAGNA_DEBUG) || (isset($_GET['MLDEBUG']) && $_GET['MLDEBUG'] === 'true'));
    // Ensure $_url has 'view' set (in case it's not set yet)
    if (!isset($_url['view'])) {
        $_url['view'] = isset($_GET['view']) ? $_GET['view'] : 'apply';
    }

    // Load ReactHelper
    require_once(DIR_MAGNALISTER_MODULES . 'amazon/classes/ReactHelper.php');
    require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/AttributesMatchingHelper.php');

    $mpID = $_MagnaSession['mpID'];
    $helper = new ReactHelper($mpID, $productID);
    $attrHelper = new AttributesMatchingHelper($mpID);

    // V3 COMPATIBILITY: Get Main Category (variationGroup) and Variation Theme (variationTheme)
    // In V3, variationGroup = Main Category (Product Type like "3D_PRINTER")
    // In V3, variationTheme = Variation Theme (like "STYLE_NAME/SIZE_NAME")

    // Get Main Category (Product Type) - this is variationGroup in React props
    $mainCategory = 'none';
    if (isset($data['MainCategory']) && !empty($data['MainCategory']) && $data['MainCategory'] !== 'none') {
        $mainCategory = $data['MainCategory'];
    } elseif (isset($data['ProductType']) && !empty($data['ProductType']) && $data['ProductType'] !== 'none') {
        $mainCategory = $data['ProductType'];
    }

    // Get Variation Theme - this is variationTheme in React props
    $variationTheme = 'none';
    if (isset($data['VariationTheme']) && !empty($data['VariationTheme']) && $data['VariationTheme'] !== 'none') {
        $variationTheme = $data['VariationTheme'];
    } else {
        // Try to get from database (magnalister_amazon_apply table)
        // Only query database if we have a valid productID (not in global template mode)
        if (!empty($productID) && $productID > 0) {
            $oDB = MagnaDB::gi();
            $dbVariationTheme = $oDB->fetchOne("
                SELECT variation_theme
                FROM " . TABLE_MAGNA_AMAZON_APPLY . "
                WHERE mpID = " . (int)$mpID . "
                  AND products_id = " . (int)$productID);

            if (!empty($dbVariationTheme) && $dbVariationTheme !== 'none') {
                $variationTheme = $dbVariationTheme;
            }
        }
    }

    // If variation_theme is JSON (like '{"STYLE_NAME\/SIZE_NAME":null}'), extract the key
    if (!empty($variationTheme) && $variationTheme !== 'none') {
        $decoded = json_decode($variationTheme, true);
        if (is_array($decoded) && !empty($decoded)) {
            // Get first key from the JSON object
            $keys = array_keys($decoded);
            $variationTheme = $keys[0]; // e.g., "STYLE_NAME/SIZE_NAME"
        }
    }
    // Get data from helper
    $shopAttributes = $helper->getShopAttributes();
    $marketplaceAttributes = $helper->getMarketplaceAttributes($data['MainCategory']); // Now gets from Amazon API via GetCategoryDetails
    $savedValues = $helper->getAttributeMatching();
    $conditionalRules = $helper->getConditionalRules($data['MainCategory']); // Get conditional rules from GetCategoryDetails

    // Call VerifyItems API to detect mandatory attributes (attributes with missing values)
    // This marks attributes that would fail validation as required=true
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

    // DEBUG: Log what we got
    if ($debugMode) {
        echo '<!-- DEBUG: mainCategory = ' . $mainCategory . ' -->';
        echo '<!-- DEBUG: variationTheme = ' . $variationTheme . ' -->';
        echo '<!-- DEBUG: shopAttributes count = ' . count($shopAttributes) . ' -->';
        echo '<!-- DEBUG: marketplaceAttributes count = ' . count($marketplaceAttributes) . ' -->';
        echo '<!-- DEBUG: shopAttributes = ' . htmlspecialchars(print_r($shopAttributes, true)) . ' -->';
        echo '<!-- DEBUG: marketplaceAttributes = ' . htmlspecialchars(print_r($marketplaceAttributes, true)) . ' -->';
    }

    // Build API endpoint URL (add applyAction=react so apply.php routes to applicationviews.php)
    $apiEndpoint = isset($_url) ? toURL($_url, array(
            'view'        => $_GET['view'],
            'kind'        => 'ajax',
            'applyAction' => 'react',
            'MLDEBUG'     => $debugMode ? 'true' : 'false'
    ), true) : '';

    // Build React component props using helper function
    $reactProps = buildReactComponentProps($mpID, $productID, $mainCategory, $variationTheme, $shopAttributes, $marketplaceAttributes, $savedValues, $conditionalRules, $apiEndpoint);

    // Use unified HTML renderer + add category change listener and form validation
    $html = renderReactVariationMatchingHTML($reactProps, $mpID);

    // Prepare variables for JavaScript (outside HEREDOC)
    $ajaxUrl = toURL($_url, array(
            'view'        => $_GET['view'],
            'kind'        => 'ajax',
            'applyAction' => 'react'
    ), true);
    $productIDJs = (int)$productID;
    $mpIDJs = (int)$mpID;

    // Add category change listener and form validation (these are specific to preparation page)
    ob_start();
    ?>
    <script type="text/javascript">
        (function () {
            // Setup category change listener after jQuery is ready
            jQuery(document).ready(function () {
                setupCategoryChangeListener();
            });


            /**
             * Listen for category changes and reload React component with new data
             * Supports multiple selectors: #PrimaryCategory, #maincat, #variation_design, #variation-theme
             */
            function setupCategoryChangeListener() {
                console.log('[AmazonVariations] setupCategoryChangeListener called');

                // Try multiple selectors (V2 uses different IDs in different contexts)
                // #PrimaryCategory = V2 Variation Matching page (prepare/view=varmatch)
                // #maincat = V2 Application/Prepare page
                // #variation_design, #variation-theme = Other contexts
                var selectors = ['#PrimaryCategory', '#maincat', '#variation_design', '#variation-theme', '#variation-design'];
                var foundCount = 0;

                // Setup listener for ALL matching selectors, not just the first one
                for (var i = 0; i < selectors.length; i++) {
                    // Test if selector exists in DOM (jQuery returns empty object if not found)
                    var testSelect = jQuery(selectors[i]);
                    if (testSelect.length > 0) {
                        foundCount++;
                        console.log('[AmazonVariations] Found selector:', selectors[i], testSelect);

                        // Install change listener on this selector
                        testSelect.on('change', function () {
                            var selectedValue = jQuery(this).val();
                            var selectorId = this.id;
                            console.log('[AmazonVariations] ===== SELECTOR CHANGED =====');
                            console.log('[AmazonVariations] Selector:', selectorId, 'changed to:', selectedValue);

                            // If no value selected or "none", hide React component
                            if (!selectedValue || selectedValue === 'none' || selectedValue === 'null') {
                                console.log('[AmazonVariations] No value selected, hiding component');
                                var container = document.getElementById('amazon-variations-root');
                                if (container) {
                                    container.style.display = 'none';
                                }
                                return;
                            }

                            // IMPORTANT: Save pending changes BEFORE reloading component
                            console.log('[AmazonVariations] Checking for pending changes before reload...');

                            // Helper function to proceed with reload after saving
                            var proceedWithReload = function () {
                                console.log('[AmazonVariations] Proceeding with reload after save');

                                // Show blockUI loading indicator
                                if (typeof jQuery.blockUI === 'function') {
                                    console.log('[AmazonVariations] Showing blockUI loading indicator');
                                    mlShowLoading();

                                } else {
                                    console.log('[AmazonVariations] jQuery.blockUI not available');
                                }

                                // Determine if we're changing main category or variation theme
                                var variationTheme = '';
                                if (selectorId === 'maincat') {
                                    // Main category changed - get current variation theme if any
                                    variationTheme = '';
                                    console.log('[AmazonVariations] Main category changed, using variation theme:', variationTheme);
                                } else {
                                    // Variation theme changed
                                    variationTheme = selectedValue;
                                    console.log('[AmazonVariations] Variation theme changed to:', variationTheme);
                                }

                                // Reload component with new data
                                reloadReactComponent(variationTheme);
                            };

                            // Check if save function exists and call it before reloading
                            if (typeof window.magnalisterSaveAmazonVariations === 'function') {
                                console.log('[AmazonVariations] Saving pending changes before reload...');
                                // Save pending changes and wait for completion before reloading
                                window.magnalisterSaveAmazonVariations(proceedWithReload);
                            } else {
                                // No save function available, proceed immediately
                                console.log('[AmazonVariations] No save function available, reloading immediately');
                                proceedWithReload();
                            }
                        });
                    }
                }

                if (foundCount === 0) {
                    console.error('[AmazonVariations] No category selector found! Tried:', selectors.join(', '));

                    // Debug: Show all select elements in document
                    console.log('[AmazonVariations] ALL select elements in document:', jQuery('select').length);
                    jQuery('select').each(function (index) {
                        console.log('[AmazonVariations] Select #' + index + ':', {
                            id: this.id,
                            name: this.name,
                            classes: this.className,
                            element: this
                        });
                    });

                    // Check for select2 elements
                    console.log('[AmazonVariations] Select2 elements:', jQuery('.select2-hidden-accessible').length);
                    jQuery('.select2-hidden-accessible').each(function (index) {
                        console.log('[AmazonVariations] Select2 #' + index + ':', {
                            id: this.id,
                            name: this.name,
                            element: this
                        });
                    });

                    return;
                }

                console.log('[AmazonVariations] SUCCESS: Installed change listeners on', foundCount, 'selectors');
            }

            /**
             * Reload React component with new variation group/category data
             * V3-like approach: Get HTML from server and replace existing HTML
             */
            function reloadReactComponent(variationTheme) {
                console.log('[AmazonVariations] Reloading component for variation group:', variationTheme);

                // Get main category value
                var mainCategory = jQuery('#maincat').val();
                console.log('[AmazonVariations] Main category:', mainCategory);

                // Find current container to replace
                var oldContainer = document.getElementById('amazon-variations-root') ||
                    document.querySelector('.amazon-variations-container');

                // Make AJAX call to get new HTML
                jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo $ajaxUrl?>',
                    dataType: 'json',
                    data: {
                        'type': 'getReactComponentData',
                        'variationTheme': variationTheme,
                        'mainCategory': mainCategory,
                        'productID': <?php echo $productIDJs?>,
                        'mpID': <?php echo $mpIDJs?>
                    },
                    success: function (response) {
                        if (response.success && response.html) {
                            console.log('[AmazonVariations] Received new HTML');
                            console.log('[AmazonVariations] HTML length:', response.html.length);
                            console.log('[AmazonVariations] HTML preview:', response.html.substring(0, 200));

                            // V3-like approach: Replace HTML and execute scripts
                            if (oldContainer && oldContainer.parentNode) {
                                // Wrap HTML in <table> so DOMParser preserves <tbody>
                                var wrappedHTML = '<table>' + response.html + '</table>';

                                var parser = new DOMParser();
                                var doc = parser.parseFromString(wrappedHTML, 'text/html');

                                // Extract new tbody from table
                                var newTbody = doc.querySelector('tbody#amazon-variations-root');

                                console.log('[AmazonVariations] Found tbody:', newTbody ? 'YES' : 'NO');

                                // Extract scripts (they're siblings of tbody in original HTML)
                                var scripts = doc.querySelectorAll('script');
                                console.log('[AmazonVariations] Found scripts:', scripts.length);

                                var scriptElements = [];

                                scripts.forEach(function (oldScript) {
                                    // Create new script element (browsers only execute newly created scripts)
                                    var newScript = document.createElement('script');
                                    newScript.type = 'text/javascript';

                                    // Copy inline code
                                    if (oldScript.textContent) {
                                        newScript.textContent = oldScript.textContent;
                                    }

                                    // Copy src attribute
                                    if (oldScript.src) {
                                        newScript.src = oldScript.src;
                                    }

                                    scriptElements.push(newScript);
                                });

                                // Replace old container with new tbody
                                if (newTbody) {
                                    oldContainer.parentNode.replaceChild(newTbody, oldContainer);
                                    console.log('[AmazonVariations] HTML replaced successfully');

                                    // Execute scripts AFTER DOM replacement
                                    setTimeout(function () {
                                        scriptElements.forEach(function (script) {
                                            document.body.appendChild(script);
                                        });
                                    }, 10); // Small delay to let DOM settle
                                } else {
                                    console.error('[AmazonVariations] New tbody not found in response');
                                }
                            } else {
                                console.error('[AmazonVariations] Container not found for replacement');
                            }
                        } else {
                            console.error('[AmazonVariations] Failed to load component:', response.message || 'Unknown error');
                            if (oldContainer) {
                                oldContainer.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: red;">Error loading attributes. Please try again.</td></tr>';
                            }
                        }

                        // Unblock UI
                        if (typeof jQuery.unblockUI === 'function') {
                            mlHideLoading();
                        }
                    },
                    error: function (xhr, status, error) {
                        // Unblock UI on error
                        if (typeof jQuery.unblockUI === 'function') {
                            mlHideLoading();
                        }

                        console.error('[AmazonVariations] AJAX error:', status, error);
                        if (oldContainer) {
                            oldContainer.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: red;">Error loading attributes. Please try again.</td></tr>';
                        }
                    }
                });
            }

            // Note: Legacy saveAttributeMatching function removed
            // React component now handles saving internally via V3 format (ml[action], ml[attributeData])
            // See AmazonVariations.tsx saveAttributeMatchingInternal() method

            // FORM VALIDATION: Block submit if required attributes are not matched
            // This prevents form submission when React component has validation errors
            (function setupFormValidation() {
                // Wait for button to be available
                jQuery(document).ready(function () {
                    // Find the form
                    var form = document.querySelector('form[name="apply"]');
                    if (!form) {
                        console.log('[Amazon Variations] Form not found, skipping validation setup');
                        return;
                    }

                    // Find all submit buttons with mlbtn-action class
                    var submitButtons = document.querySelectorAll('.mlbtn-action[type="button"]');
                    if (submitButtons.length === 0) {
                        console.log('[Amazon Variations] Submit buttons not found, skipping validation setup');
                        return;
                    }

                    console.log('[Amazon Variations] Setting up validation handler on', submitButtons.length, 'buttons');

                    // Add click listener to each submit button
                    submitButtons.forEach(function (button) {
                        button.addEventListener('click', function (e) {
                            console.log('[Amazon Variations] Submit button clicked');

                            // Check if validation errors exist
                            if (typeof window.amazonVariationsValidationErrors !== 'undefined' &&
                                window.amazonVariationsValidationErrors.length > 0) {
                                console.log('[Amazon Variations] Validation errors found, blocking submit and scrolling to error');
                                console.log('[Amazon Variations] Errors:', window.amazonVariationsValidationErrors);

                                // Unblock UI (remove loading animation if it was shown)
                                if (typeof jQuery !== 'undefined' && typeof jQuery.unblockUI === 'function') {
                                    mlHideLoading();
                                }

                                // Trigger React to scroll to first error
                                var scrollEvent = new CustomEvent('amazon-variations-scroll-to-error');
                                document.dispatchEvent(scrollEvent);

                                // Prevent button action
                                e.preventDefault();
                                e.stopPropagation();
                                return false;
                            }

                            console.log('[Amazon Variations] Form validation passed');

                            // Show loading indicator before starting save/submit process
                            if (typeof mlShowLoading === 'function') {
                                mlShowLoading();
                            }

                            // Prevent default button action
                            e.preventDefault();
                            e.stopPropagation();

                            // Helper function to proceed with form submission after saving
                            var proceedWithSubmit = function () {
                                console.log('[Amazon Variations] Proceeding with form submission after save');

                                // V3 COMPATIBILITY: Disable old attribute matching inputs before form submission
                                // React saves attribute matching via AJAX, so we don't want old ml[match] fields to be submitted
                                // Find all inputs/selects with name starting with "ml[match]" and disable them
                                var attributeMatchingInputs = form.querySelectorAll('[name^="ml[match]"], [name^="ml[match"]');
                                console.log('[Amazon Variations] Disabling ' + attributeMatchingInputs.length + ' old attribute matching inputs');
                                attributeMatchingInputs.forEach(function (input) {
                                    input.disabled = true;
                                    console.log('[Amazon Variations] Disabled input:', input.name);
                                });

                                // Also disable any hidden inputs in the amazon-variations container
                                // (in case React component has any form fields - though it shouldn't)
                                var reactContainer = document.getElementById('amazon-variations-root');
                                if (reactContainer) {
                                    var reactInputs = reactContainer.querySelectorAll('input, select, textarea');
                                    reactInputs.forEach(function (input) {
                                        if (input.name) {
                                            input.disabled = true;
                                            console.log('[Amazon Variations] Disabled React input:', input.name);
                                        }
                                    });
                                }

                                // Submit the form
                                // Note: form.submit() bypasses event handlers but actually submits the form
                                // Dispatch event first for any listeners, then submit
                                var submitEvent = new Event('submit', {bubbles: true, cancelable: true});
                                var cancelled = !form.dispatchEvent(submitEvent);

                                if (!cancelled) {
                                    console.log('[Amazon Variations] Submitting form...');
                                    form.submit();
                                } else {
                                    console.log('[Amazon Variations] Form submission was cancelled by another handler');
                                }
                            };

                            // IMPORTANT: Save pending changes BEFORE submitting form
                            // Check if save function exists and call it before submitting
                            if (typeof window.magnalisterSaveAmazonVariations === 'function') {
                                console.log('[Amazon Variations] Saving pending changes before form submission...');
                                // Save pending changes and wait for completion before submitting
                                window.magnalisterSaveAmazonVariations(proceedWithSubmit);
                            } else {
                                // No save function available, proceed immediately
                                console.log('[Amazon Variations] No save function available, submitting form immediately');
                                proceedWithSubmit();
                            }

                            return false;
                        }, true); // Use capture phase to intercept early
                    });
                });
            })();
        })();
    </script>
    <?php
    $html .= ob_get_clean();
    return $html;
}

/**
 * Handle AJAX save request for attribute matching
 * Call this from applicationviews.php AJAX handler
 *
 * Supports both V2 and V3 formats:
 * - V2: $_POST['attributeMatching'] (JSON all attributes), $_POST['productID'], $_POST['mpID']
 * - V3: ml[action], ml[attributeKey], ml[attributeData], ml[variationGroup], ml[customIdentifier]
 */
function handleSaveAttributeMatching() {
    global $_MagnaSession;
    $debugMode = ((defined('MAGNA_DEBUG') && MAGNA_DEBUG) || (isset($_GET['MLDEBUG']) && $_GET['MLDEBUG'] === 'true'));

    // Check if V3 format (ml[action])
    if (isset($_POST['ml']['action']) && $_POST['ml']['action'] === 'saveAttributeMatching') {
        // V3 Format: Save single attribute
        if (!isset($_POST['ml']['attributeKey']) || !isset($_POST['ml']['variationGroup'])) {
            die(json_encode(array('success' => false, 'message' => 'Missing parameters (V3 format)')));
        }

        $attributeKey = $_POST['ml']['attributeKey'];
        $variationGroup = $_POST['ml']['variationGroup'];
        $actionType = isset($_POST['ml']['actionType']) ? $_POST['ml']['actionType'] : 'save';
        $customIdentifier = isset($_POST['ml']['customIdentifier']) ? (int)$_POST['ml']['customIdentifier'] : 0;
        $variationTheme = isset($_POST['ml']['variationTheme']) ? $_POST['ml']['variationTheme'] : null;

        // Validate variationGroup is not "none"
        if (empty($variationGroup) || $variationGroup === 'none') {
            die(json_encode(array(
                    'success' => false,
                    'message' => 'Please select a valid product type before saving'
            )));
        }

        // Get mpID and productID from session or POST
        $mpID = isset($_POST['mpID']) ? (int)$_POST['mpID'] : $_MagnaSession['mpID'];
        $productID = $customIdentifier > 0 ? $customIdentifier : (isset($_POST['productID']) ? (int)$_POST['productID'] : 0);

        if ($actionType === 'delete') {
            // Delete attribute matching
            $attributeMatching = array(
                    $attributeKey => null // Mark for deletion
            );
        } else {
            // Parse attribute data
            if (!isset($_POST['ml']['attributeData'])) {
                die(json_encode(array('success' => false, 'message' => 'Missing attribute data')));
            }

            $attributeData = json_decode($_POST['ml']['attributeData'], true);
            if (!is_array($attributeData)) {
                die(json_encode(array('success' => false, 'message' => 'Invalid attribute data format')));
            }

            // Build attribute matching array for single attribute
            $attributeMatching = array(
                    $attributeKey => $attributeData
            );
        }
    } else {
        // V2 Format: Save all attributes at once
        if (!isset($_POST['attributeMatching']) || !isset($_POST['productID']) || !isset($_POST['mpID'])) {
            die(json_encode(array('success' => false, 'message' => 'Missing parameters (V2 format)')));
        }

        $attributeMatching = json_decode($_POST['attributeMatching'], true);
        $productID = (int)$_POST['productID'];
        $mpID = (int)$_POST['mpID'];

        if (!is_array($attributeMatching)) {
            die(json_encode(array('success' => false, 'message' => 'Invalid attribute matching data')));
        }
    }

    // Load ReactHelper
    require_once(DIR_MAGNALISTER_MODULES . 'amazon/classes/ReactHelper.php');

    $helper = new ReactHelper($mpID, $productID);

    // Validate data
    $validation = $helper->validateAttributeMatching($attributeMatching);
    if (!$validation['valid']) {
        die(json_encode(array(
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validation['errors']
        )));
    }

    // Save data
    try {
        $result = $helper->saveAttributeMatching($attributeMatching);

        // Save variation_theme if provided
        if (isset($variationTheme) && !empty($variationTheme)) {
            $helper->saveVariationTheme($variationTheme);
        }

        $response = array('success' => $result);
        if ($debugMode) {
            $response['sql'] = MagnaDB::gi()->getTimePerQuery();
        }
        die(json_encode($response));
    } catch (Exception $e) {
        die(json_encode(array(
                'success' => false,
                'message' => $e->getMessage()
        )));
    }
}

/**
 * Handle AJAX batch save request for attribute matching (V3 format only)
 * Saves multiple attributes at once
 *
 * Expected POST parameters:
 * - ml[action] = 'saveAttributeMatchingBatch'
 * - ml[attributesData] = JSON encoded object with multiple attributes
 * - ml[variationGroup] = Main category/variation group
 * - ml[customIdentifier] = Product ID
 * - ml[variationTheme] = Variation theme (optional)
 */
function handleSaveAttributeMatchingBatch() {
    global $_MagnaSession;
    $debugMode = ((defined('MAGNA_DEBUG') && MAGNA_DEBUG) || (isset($_GET['MLDEBUG']) && $_GET['MLDEBUG'] === 'true'));

    // V3 Format: Save multiple attributes at once
    if (!isset($_POST['ml']['attributesData']) || !isset($_POST['ml']['variationGroup'])) {
        die(json_encode(array('success' => false, 'message' => 'Missing parameters (batch save)')));
    }

    $attributesData = json_decode($_POST['ml']['attributesData'], true);
    if (!is_array($attributesData)) {
        die(json_encode(array('success' => false, 'message' => 'Invalid attributes data format')));
    }

    $variationGroup = $_POST['ml']['variationGroup'];
    $customIdentifier = isset($_POST['ml']['customIdentifier']) ? (int)$_POST['ml']['customIdentifier'] : 0;
    $variationTheme = isset($_POST['ml']['variationTheme']) ? $_POST['ml']['variationTheme'] : null;

    // Validate variationGroup is not "none"
    if (empty($variationGroup) || $variationGroup === 'none') {
        die(json_encode(array(
                'success' => false,
                'message' => 'Please select a valid product type before saving'
        )));
    }

    // Get mpID and productID from session or POST
    $mpID = isset($_POST['mpID']) ? (int)$_POST['mpID'] : $_MagnaSession['mpID'];
    $productID = $customIdentifier > 0 ? $customIdentifier : (isset($_POST['productID']) ? (int)$_POST['productID'] : 0);

    // Load ReactHelper
    require_once(DIR_MAGNALISTER_MODULES . 'amazon/classes/ReactHelper.php');

    $helper = new ReactHelper($mpID, $productID);

    // Validate data
    $validation = $helper->validateAttributeMatching($attributesData);
    if (!$validation['valid']) {
        die(json_encode(array(
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validation['errors']
        )));
    }

    // Save data
    try {
        $result = $helper->saveAttributeMatching($attributesData);

        // Save variation_theme if provided
        if (isset($variationTheme) && !empty($variationTheme)) {
            $helper->saveVariationTheme($variationTheme);
        }

        // Verify item with Amazon API (only for product-specific mode, not global template)
        $verificationResult = null;
        if ($productID > 0 && $variationGroup !== 'none') {
            // No verification needed (global template or no category selected)
            $response = array('success' => $result);
        }

        if ($debugMode) {
            $response['sql'] = MagnaDB::gi()->getTimePerQuery();
            if ($verificationResult !== null) {
                $response['verificationResult'] = $verificationResult;
            }
        }
        die(json_encode($response));
    } catch (Exception $e) {
        die(json_encode(array(
                'success' => false,
                'message' => $e->getMessage()
        )));
    }
}

/**
 * Handle AJAX request for getting React component data when category changes
 * Call this from applicationviews.php AJAX handler
 */
function handleGetReactComponentData() {
    global $_MagnaSession, $_url;
    $debugMode = ((defined('MAGNA_DEBUG') && MAGNA_DEBUG) || (isset($_GET['MLDEBUG']) && $_GET['MLDEBUG'] === 'true'));
    if (!isset($_POST['variationTheme']) || !isset($_POST['productID']) || !isset($_POST['mpID'])) {
        die(json_encode(array('success' => false, 'message' => 'Missing parameters')));
    }

    $variationTheme = $_POST['variationTheme'];
    $mainCategory = isset($_POST['mainCategory']) ? $_POST['mainCategory'] : null;
    $productID = (int)$_POST['productID'];
    $mpID = (int)$_POST['mpID'];

    // Validate mainCategory is not "none"
    if (empty($mainCategory) || $mainCategory === 'none' || $mainCategory === 'null') {
        die(json_encode(array(
                'success' => false,
                'message' => 'Please select a valid product type'
        )));
    }

    try {
        // Load ReactHelper
        require_once(DIR_MAGNALISTER_MODULES . 'amazon/classes/ReactHelper.php');
        require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/AttributesMatchingHelper.php');

        $helper = new ReactHelper($mpID, $productID);
        $attrHelper = new AttributesMatchingHelper($mpID);

        // Get data for the new variation group
        $shopAttributes = $helper->getShopAttributes();

        // Get marketplace attributes: combine mandatory (from category) + optional (from variation theme)
        $marketplaceAttributes = array();
//var_dump($mainCategory);
        try {
            if ($mainCategory && $mainCategory !== 'null') {
                $categoryResult = MagnaConnector::gi()->submitRequest(array(
                        'ACTION' => 'GetCategoryDetails',
                        'DATA'   => array(
                                'PRODUCTTYPE'               => $mainCategory,
                                'INCLUDE_CONDITIONAL_RULES' => true
                        ),
                ));

                // Extract mandatory attributes from category
                // Look for 'attributes' field (not variation_details)
                if (isset($categoryResult['DATA']['attributes']) && is_array($categoryResult['DATA']['attributes'])) {
                    foreach ($categoryResult['DATA']['attributes'] as $attrName => $attrConfig) {
                        // Parse attribute configuration
                        $isRequired = false;
                        $displayName = $attrName;
                        $dataType = 'select';
                        $desc = '';

                        if (is_array($attrConfig)) {
                            // API uses 'mandatory' field, not 'required'
                            $isRequired = isset($attrConfig['mandatory']) ? (bool)$attrConfig['mandatory'] : false;
                            // Also check 'required' as fallback
                            if (!$isRequired && isset($attrConfig['required'])) {
                                $isRequired = (bool)$attrConfig['required'];
                            }
                            $displayName = isset($attrConfig['title']) ? $attrConfig['title'] : (isset($attrConfig['name']) ? $attrConfig['name'] : $attrName);
                            $dataType = isset($attrConfig['type']) ? $attrConfig['type'] : 'select';
                            $desc = isset($attrConfig['desc']) ? $attrConfig['desc'] : '';
                        }

                        // Clean attribute name
                        $cleanName = str_replace('__value', '', $attrName);
                        if ($displayName === $attrName) {
                            $displayName = ucwords(str_replace('_', ' ', $cleanName));
                        }

                        $marketplaceAttributes[$attrName] = array(
                                'value'    => $displayName,
                                'required' => $isRequired,
                                'dataType' => $dataType,
                                'desc'     => $desc,
                                'values' => !empty($attrConfig['values']) ? $attrConfig['values'] : array()
                        );
                    }
                }


                // Extract conditional rules (if available from backend)
                $conditionalRules = isset($categoryResult['DATA']['conditional_rules']) ? $categoryResult['DATA']['conditional_rules'] : array();


            }
        } catch (Exception $e) {
            // If API fails, continue with empty optional attributes
            if ($debugMode) {
                echo ('[ReactHelper] GetCategoryDetails Error: ' . $e->getMessage());
            }
        }

        // Step 3: Call VerifyItems API to detect mandatory attributes (attributes with missing values)
        // This marks attributes that would fail validation as required=true
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

        $savedValues = $helper->getAttributeMatching();

        // Fallback: If API didn't return attributes, generate from theme
        if (empty($marketplaceAttributes) && $variationTheme !== 'none') {
            $marketplaceAttributes = $helper->generateMarketplaceAttributesFromTheme($variationTheme);
        }

        // Build API endpoint URL (same as initial render)
        $apiEndpoint = isset($_url) ? toURL($_url, array(
                'view'        => $_GET['view'],
                'kind'        => 'ajax',
                'applyAction' => 'react',
                'MLDEBUG'     => $debugMode ? 'true' : 'false'
        ), true) : '';

        // V3-like approach: Use SIMPLE renderer (no React, no scripts)
        require_once(DIR_MAGNALISTER_MODULES . 'amazon/application/variations_simple.php');

        // Build API endpoint
        $apiEndpoint = isset($_url) ? toURL($_url, array(
                'view'        => $_GET['view'],
                'kind'        => 'ajax',
                'applyAction' => 'react',
                'MLDEBUG'     => $debugMode ? 'true' : 'false'
        ), true) : '';

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

/**
 * Render React variation matching for GLOBAL TEMPLATE (Variation Matching page)
 * Used when productID = 0 (no specific product, just category template)
 *
 * @param string $categoryId Category/Product Type ID
 * @param array $urlResources URL resources for API endpoint (optional, defaults to global $_url)
 * @return string HTML output
 */
function renderReactVariationMatchingTemplate($categoryId = '', $urlResources = null) {
    global $_MagnaSession, $_url;
    $debugMode = ((defined('MAGNA_DEBUG') && MAGNA_DEBUG) || (isset($_GET['MLDEBUG']) && $_GET['MLDEBUG'] === 'true'));
    // Ensure $_url has 'view' set (in case it's not set yet)
    if (!isset($_url['view'])) {
        $_url['view'] = isset($_GET['view']) ? $_GET['view'] : 'varmatch';
    }

    // Load ReactHelper
    require_once(DIR_MAGNALISTER_MODULES . 'amazon/classes/ReactHelper.php');
    require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/AttributesMatchingHelper.php');

    $mpID = $_MagnaSession['mpID'];
    $productID = 0; // Global template, no specific product
    $helper = new ReactHelper($mpID, $productID);
    $attrHelper = new AttributesMatchingHelper($mpID);

    // For global template, we ONLY have category ID (no variation theme, no product)
    $mainCategory = $categoryId;
    $variationTheme = 'none'; // No variation theme for global template

    // Get data from helper
    $shopAttributes = $helper->getShopAttributes();
    $marketplaceAttributes = $helper->getMarketplaceAttributes($mainCategory);

    // Get saved values from variantmatching table (not apply table)
    // Override $_POST temporarily so getAttributeMatching() uses correct table
    $oldPost = $_POST;
    $_POST['PrimaryCategory'] = $categoryId;
    $_POST['mainCategory'] = $categoryId;
    $savedValues = $helper->getAttributeMatching();
    $_POST = $oldPost;

    $conditionalRules = $helper->getConditionalRules($mainCategory);

    // Call VerifyItems to detect mandatory attributes
    if (!empty($marketplaceAttributes) && $mainCategory !== 'none') {
        require_once(DIR_MAGNALISTER_MODULES . 'amazon/AmazonHelper.php');
        $verifyErrors = AmazonHelper::verifyItemByMarketplaceToGetMandatoryAttributes($mainCategory, $variationTheme);

        if (!empty($verifyErrors) && is_array($verifyErrors)) {
            foreach ($verifyErrors as $error) {
                if (isset($error['ERRORDATA'])) {
                    $errorData = $error['ERRORDATA'];
                    if (isset($error['ERRORLEVEL']) && $error['ERRORLEVEL'] === 'FATAL' && isset($errorData['error_categories']) && is_array($errorData['error_categories']) && in_array('MISSING_ATTRIBUTE', $errorData['error_categories'], true) && isset($errorData['error_attributeNames']) && is_array($errorData['error_attributeNames'])) {

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
    // Use provided URL resources if available (for view=varmatch), otherwise use global $_url (for view=apply)
    if ($urlResources !== null) {
        // varmatch path: use dedicated URL without applyAction
        $apiEndpoint = toURL($urlResources, array('kind' => 'ajax',
                                                  'MLDEBUG'     => $debugMode ? 'true' : 'false'), true);
    } else {
        // apply path: use global $_url with applyAction=react
        $apiEndpoint = isset($_url) ? toURL($_url, array(
                'view'        => isset($_GET['view']) ? $_GET['view'] : 'apply',
                'kind'        => 'ajax',
                'applyAction' => 'react',
                'MLDEBUG'     => $debugMode ? 'true' : 'false'
        ), true) : '';
    }

    // Build React component props
    $reactProps = buildReactComponentProps($mpID, $productID, $mainCategory, $variationTheme, $shopAttributes, $marketplaceAttributes, $savedValues, $conditionalRules, $apiEndpoint);

    // Use unified HTML renderer
    $html = renderReactVariationMatchingHTML($reactProps, $mpID);

    // Prepare variables for JavaScript (for global template)
    $ajaxUrlTemplate = isset($_url) ? toURL($_url, array(
            'view'        => $_GET['view'],
            'kind'        => 'ajax',
            'applyAction' => 'react'
    ), true) : '';
    $mpIDJsTemplate = (int)$mpID;

    // Add category change listener specific to global template (view=varmatch)
    ob_start();
    ?>
    <script type="text/javascript">
        (function () {
            // Setup category change listener after jQuery is ready
            jQuery(document).ready(function () {
                setupCategoryChangeListener();
            });

            /**
             * Listen for category changes (only #PrimaryCategory for global template)
             */
            function setupCategoryChangeListener() {
                console.log('[AmazonVariations] setupCategoryChangeListener (Global Template Mode)');

                var selector = jQuery('#PrimaryCategory');
                if (selector.length === 0) {
                    console.error('[AmazonVariations] #PrimaryCategory not found!');
                    return;
                }

                console.log('[AmazonVariations] Found #PrimaryCategory, installing change listener');

                selector.on('change', function () {
                    var selectedValue = jQuery(this).val();
                    console.log('[AmazonVariations] Category changed to:', selectedValue);

                    if (!selectedValue || selectedValue === 'none' || selectedValue === 'null') {
                        var container = document.getElementById('amazon-variations-root');
                        if (container) {
                            container.style.display = 'none';
                        }
                        return;
                    }

                    // Reload component with new category
                    reloadReactComponent(selectedValue);
                });
            }

            /**
             * Reload React component with new category data
             * V3-like approach: Get HTML from server and replace existing HTML
             */
            function reloadReactComponent(categoryId) {
                console.log('[AmazonVariations] Reloading for category:', categoryId);

                // Show blockUI loading indicator
                if (typeof jQuery.blockUI === 'function' && typeof blockUILoading !== 'undefined') {
                    mlShowLoading();
                }

                // Find current container to replace
                var oldContainer = document.getElementById('amazon-variations-root') ||
                    document.querySelector('.amazon-variations-container');

                // Make AJAX call to get new HTML
                jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo $ajaxUrlTemplate?>',
                    dataType: 'json',
                    data: {
                        'type': 'getReactComponentData',
                        'variationTheme': 'none',
                        'mainCategory': categoryId,
                        'productID': 0, // Global template
                        'mpID': <?php echo $mpIDJsTemplate?>
                    },
                    success: function (response) {
                        if (response.success && response.html) {
                            console.log('[AmazonVariations] Received new HTML');
                            console.log('[AmazonVariations] HTML length:', response.html.length);
                            console.log('[AmazonVariations] HTML preview:', response.html.substring(0, 200));

                            // V3-like approach: Replace HTML and execute scripts
                            if (oldContainer && oldContainer.parentNode) {
                                // Wrap HTML in <table> so DOMParser preserves <tbody>
                                var wrappedHTML = '<table>' + response.html + '</table>';

                                var parser = new DOMParser();
                                var doc = parser.parseFromString(wrappedHTML, 'text/html');

                                // Extract new tbody from table
                                var newTbody = doc.querySelector('tbody#amazon-variations-root');

                                console.log('[AmazonVariations] Found tbody:', newTbody ? 'YES' : 'NO');

                                // Extract scripts (they're siblings of tbody in original HTML)
                                var scripts = doc.querySelectorAll('script');
                                console.log('[AmazonVariations] Found scripts:', scripts.length);

                                var scriptElements = [];

                                scripts.forEach(function (oldScript) {
                                    // Create new script element (browsers only execute newly created scripts)
                                    var newScript = document.createElement('script');
                                    newScript.type = 'text/javascript';

                                    // Copy inline code
                                    if (oldScript.textContent) {
                                        newScript.textContent = oldScript.textContent;
                                    }

                                    // Copy src attribute
                                    if (oldScript.src) {
                                        newScript.src = oldScript.src;
                                    }

                                    scriptElements.push(newScript);
                                });

                                // Replace old container with new tbody
                                if (newTbody) {
                                    oldContainer.parentNode.replaceChild(newTbody, oldContainer);
                                    console.log('[AmazonVariations] HTML replaced successfully');

                                    // Execute scripts AFTER DOM replacement
                                    setTimeout(function () {
                                        scriptElements.forEach(function (script) {
                                            document.body.appendChild(script);
                                        });
                                    }, 10); // Small delay to let DOM settle
                                } else {
                                    console.error('[AmazonVariations] New tbody not found in response');
                                }
                            } else {
                                console.error('[AmazonVariations] Container not found for replacement');
                            }
                        } else {
                            console.error('[AmazonVariations] Failed to load:', response.message || 'Unknown error');
                            if (oldContainer) {
                                oldContainer.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 20px; color: red;">Error loading attributes.</td></tr>';
                            }
                        }

                        // Unblock UI
                        if (typeof jQuery.unblockUI === 'function') {
                            mlHideLoading();
                        }
                    },
                    error: function (xhr, status, error) {
                        // Unblock UI on error
                        if (typeof jQuery.unblockUI === 'function') {
                            mlHideLoading();
                        }

                        console.error('[AmazonVariations] AJAX error:', status, error);
                        if (oldContainer) {
                            oldContainer.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 20px; color: red;">Error loading attributes.</td></tr>';
                        }
                    }
                });
            }
        })();
    </script>
    <?php
    $html .= ob_get_clean();

    return $html;
}

/**
 * Check if React variation matching should be used
 * @return bool
 */
function shouldUseReactVariationMatching() {
    return true;
    global $_MagnaSession;

    // Check feature flag from config
    $useReact = getDBConfigValue('amazon.variation.use_react', $_MagnaSession['mpID'], 'false');
    return $useReact === 'true';
}
