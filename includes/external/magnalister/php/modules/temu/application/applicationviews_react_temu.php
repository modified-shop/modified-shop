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
 * (c) 2010 - 2026 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or defined('_VALID_XTC_MODULE_CALL') or defined('MAGNALISTER_PLUGIN') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES . 'temu/classes/TemuReactHelper.php');
require_once(DIR_MAGNALISTER_MODULES . 'temu/TemuHelper.php');

/**
 * React-based variation matching renderer for Temu
 * Adapted from Amazon's applicationviews_react.php
 *
 * This file provides standalone functions for rendering the React attribute matching
 * component and handling AJAX requests for Temu marketplace.
 */

/**
 * Build React component props for Temu
 *
 * @param int $mpID Marketplace ID
 * @param int $productID Product ID
 * @param string $mainCategory Main category ID
 * @param array $shopAttributes Shop attributes
 * @param array $marketplaceAttributes Marketplace attributes
 * @param array $savedValues Saved attribute values
 * @param array $conditionalRules Conditional rules (optional)
 * @param string $apiEndpoint API endpoint URL
 * @return array React component props
 */
function buildTemuReactComponentProps($mpID, $productID, $mainCategory, $shopAttributes, $marketplaceAttributes, $savedValues, $conditionalRules, $apiEndpoint) {
    $debugMode = ((defined('MAGNA_DEBUG') && MAGNA_DEBUG) || (isset($_GET['MLDEBUG']) && $_GET['MLDEBUG'] === 'true'));

    $helper = new TemuReactHelper($mpID, $productID);

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
        'variationTheme'        => 'none',
        'marketplaceName'       => 'Temu',
        'shopAttributes'        => $shopAttributes,
        'marketplaceAttributes' => $marketplaceAttributes,
        'savedValues'           => $savedValues,
        'conditionalRules'      => $conditionalRules,
        'neededFormFields'      => array(
            'mpID'      => $mpID,
            'productID' => $productID
        ),
        'i18n'                  => $helper->getTranslations(),
        'databaseTables'        => $helper->getDatabaseTablesAndColumns(),
        'apiEndpoint'           => $apiEndpoint,
        'debugMode'             => $debugMode,
        // V2-specific props: render only tbody elements without wrapper table
        'wrapInTable'           => false,
        'hideHelpColumn'        => true
    );
}

/**
 * Render a single React section (variation or category attributes).
 *
 * @param array $reactProps React component props
 * @param string $containerId DOM element ID for the container
 * @param string $logPrefix Log prefix for console messages
 * @return string HTML with tbody container and initialization script
 */
function renderTemuReactSectionHTML($reactProps, $containerId, $logPrefix) {
    // Instance-scoped namespace so each React root (variation / category / category-independent)
    // exposes its own window['magnalisterSaveAmazonVariations_' + containerId] and can be flushed
    // independently on Save-and-Close (no shared-global race between the multiple instances).
    $reactProps['apiNamespace'] = $containerId;
    // Temu opt-in (BUG-019): report real save failures (error toast, Save aborts) instead of
    // the unconditional success toast. Amazon keeps the default (false).
    $reactProps['strictSave'] = true;
    ob_start();
    ?>
    <tbody id="<?php echo $containerId; ?>" style="display: contents;">
    </tbody>
    <script type="text/javascript">/*<![CDATA[*/
    (function() {
        var props = <?php echo json_encode($reactProps, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        function initSection() {
            var container = document.getElementById('<?php echo $containerId; ?>');
            if (!container) {
                console.error('[<?php echo $logPrefix; ?>] Container not found');
                return;
            }
            var AmazonVariations = window.MagnalisterAmazonVariations ? window.MagnalisterAmazonVariations.AmazonVariations : null;
            if (!AmazonVariations) {
                console.error('[<?php echo $logPrefix; ?>] React component not found');
                return;
            }

            props.onValuesChange = function(values) {
                window['<?php echo $containerId; ?>CurrentValues'] = values;
            };
            props.onValidationError = function(errors) {
                window['<?php echo $containerId; ?>ValidationErrors'] = errors;
                // Keep legacy global for form validation
                if (!window.temuVariationsValidationErrors) {
                    window.temuVariationsValidationErrors = [];
                }
                window.temuVariationsValidationErrors = [].concat(
                    window['temu-variation-rootValidationErrors'] || [],
                    window['temu-catattr-rootValidationErrors'] || []
                );
            };

            var element = React.createElement(AmazonVariations, props);
            if (!container._reactRoot) {
                container._reactRoot = ReactDOM.createRoot(container);
            }
            container._reactRoot.render(element);
        }
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(initSection, 50);
        } else {
            document.addEventListener('DOMContentLoaded', function() { setTimeout(initSection, 50); });
        }
    })();
    /*]]>*/</script>
    <?php
    return ob_get_clean();
}

/**
 * Split marketplace attributes and saved values into variation vs category groups.
 *
 * @param array $marketplaceAttributes All marketplace attributes
 * @param array $savedValues All saved values
 * @return array Keys: variationAttributes, categoryAttributes, variationSavedValues, categorySavedValues
 */
function splitTemuAttributesByType($marketplaceAttributes, $savedValues) {
    $variationAttributes = array();
    $categoryAttributes = array();
    foreach ($marketplaceAttributes as $key => $attr) {
        if (strpos($key, 'variation_dim_') === 0) {
            $variationAttributes[$key] = $attr;
        } else {
            $categoryAttributes[$key] = $attr;
        }
    }

    $variationSavedValues = array();
    $categorySavedValues = array();
    foreach ($savedValues as $key => $val) {
        if (strpos($key, 'variation_dim_') === 0) {
            $variationSavedValues[$key] = $val;
        } else {
            $categorySavedValues[$key] = $val;
        }
    }

    return array(
        'variationAttributes'  => $variationAttributes,
        'categoryAttributes'   => $categoryAttributes,
        'variationSavedValues' => $variationSavedValues,
        'categorySavedValues'  => $categorySavedValues,
    );
}

/**
 * Unified React HTML Renderer for Temu
 * Renders TWO separate React sections: variation attributes and category attributes.
 * Uses the shared AmazonVariations React bundle (same component, different props).
 *
 * @param int $mpID Marketplace ID
 * @param int $productID Product ID
 * @param string $mainCategory Main category ID
 * @param array $shopAttributes Shop attributes
 * @param array $marketplaceAttributes All marketplace attributes (variation + category)
 * @param array $savedValues All saved attribute values
 * @param array $conditionalRules Conditional rules (category attributes only)
 * @param string $apiEndpoint API endpoint URL
 * @return string Complete HTML with both React sections and shared bundle loading
 */
function renderTemuReactVariationMatchingHTML_split($mpID, $productID, $mainCategory, $shopAttributes, $marketplaceAttributes, $savedValues, $conditionalRules, $apiEndpoint) {
    // Split attributes into variation vs category
    $split = splitTemuAttributesByType($marketplaceAttributes, $savedValues);

    $html = '';

    // Load shared CSS and JS bundle once.
    // Cache-bust by the bundle's file mtime so a rebuilt bundle is always re-fetched (the static
    // CLIENT_BUILD_VERSION does not change on a local rebuild). Falls back to CLIENT_BUILD_VERSION
    // when the file can't be stat'd. dirname(__FILE__) keeps this PHP 5.2 compatible.
    $sReactBundleFs  = dirname(__FILE__) . '/../../../../js/react/AmazonVariationsV2.bundle.js';
    $sReactCssFs     = dirname(__FILE__) . '/../../../../js/react/AmazonVariations.css';
    $sReactBundleVer = @filemtime($sReactBundleFs);
    $sReactCssVer    = @filemtime($sReactCssFs);
    if (empty($sReactBundleVer)) { $sReactBundleVer = CLIENT_BUILD_VERSION; }
    if (empty($sReactCssVer))    { $sReactCssVer    = CLIENT_BUILD_VERSION; }
    ob_start();
    ?>
    <!-- Load React Component CSS (shared bundle) -->
    <link rel="stylesheet"
          href="<?php echo DIR_MAGNALISTER_WS; ?>js/react/AmazonVariations.css?v=<?php echo $sReactCssVer; ?>">

    <!-- Load React Component Bundle - V2 Wrapper (shared bundle) -->
    <script src="<?php echo DIR_MAGNALISTER_WS; ?>js/react/AmazonVariationsV2.bundle.js?v=<?php echo $sReactBundleVer; ?>"></script>
    <script type="text/javascript">/*<![CDATA[*/
    /**
     * Capture the variation section's magnalisterAddOptionalAttribute before other
     * React instances (category, CI) overwrite the global.
     * Uses Object.defineProperty to intercept the setter on the first write after
     * the variation section renders.
     */
    (function() {
        if (window._temuVariationApiCaptureInstalled) return;
        window._temuVariationApiCaptureInstalled = true;

        var _currentFn = window.magnalisterAddOptionalAttribute;
        var _captureNext = false;

        window.temuStartCapturingVariationApi = function() {
            _captureNext = true;
            _currentFn = window.magnalisterAddOptionalAttribute;
        };

        // Watch for changes to the global function
        Object.defineProperty(window, 'magnalisterAddOptionalAttribute', {
            get: function() { return _currentFn; },
            set: function(fn) {
                _currentFn = fn;
                if (_captureNext && typeof fn === 'function') {
                    window.magnalisterAddOptionalAttribute_temuVariation = fn;
                    _captureNext = false;
                }
            },
            configurable: true
        });
    })();
    /*]]>*/</script>
    <?php
    $html .= ob_get_clean();

    // Section 1: Variation Attributes (variation_dim_* only)
    if (!empty($split['variationAttributes'])) {
        // Detect if all variation attributes are optional (none required)
        $blAllVariationOptional = true;
        foreach ($split['variationAttributes'] as $attr) {
            if (!empty($attr['required'])) {
                $blAllVariationOptional = false;
                break;
            }
        }

        $variationProps = buildTemuReactComponentProps(
            $mpID, $productID, $mainCategory,
            $shopAttributes,
            $split['variationAttributes'],
            $split['variationSavedValues'],
            array(), // No conditional rules for variation attributes
            $apiEndpoint
        );

        if ($blAllVariationOptional) {
            // When all variation attributes are optional, PHP renders the title + note + checkboxes
            // Hide React's title so it doesn't duplicate the PHP-rendered one
            $variationProps['i18n']['requiredAttributesTitle'] = ' ';

            // Build checkbox data
            $variationCheckboxes = array();
            foreach ($split['variationAttributes'] as $sKey => $aAttr) {
                $variationCheckboxes[] = array(
                    'key'   => $sKey,
                    'label' => isset($aAttr['value']) ? $aAttr['value'] : $sKey,
                    'saved' => isset($split['variationSavedValues'][$sKey]) && !empty($split['variationSavedValues'][$sKey]['Code']),
                );
            }

            // Render checkbox UI before the React component
            ob_start();
            ?>
            <style>
                #temu-variation-root .optional-attribute .attribute-label::after {
                    content: ' \2022';
                    color: #e31a1c;
                    font-size: 18px;
                    margin-left: 5px;
                }
                #temu-variation-root .optional-attribute-selector-section {
                    display: none;
                }
                #temu-variation-root .optional-attribute .remove-matching-row {
                    display: none;
                }
                #temu-variation-root .optional-attributes-section > .headline {
                    display: none;
                }
            </style>
            <tbody id="temu-variation-checkboxes-section">
                <tr class="headline">
                    <td colspan="3"><h4><?php echo defined('ML_TEMU_LABEL_VARIATION_TITLE') ? ML_TEMU_LABEL_VARIATION_TITLE : 'Variation'; ?></h4></td>
                </tr>
                <tr>
                    <td colspan="3" style="padding: 10px 15px;">
                        <p style="margin: 0; color: #666; font-style: italic;">
                            <?php echo defined('ML_TEMU_LABEL_VARIATION_ALL_OPTIONAL_NOTE') ? ML_TEMU_LABEL_VARIATION_ALL_OPTIONAL_NOTE : 'All variation attributes for this category are optional. However, you still need to select at least one variation attribute.'; ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div id="temu-variation-checkboxes" class="temu-variation-checkboxes" style="padding: 10px 15px; display: flex; flex-wrap: wrap; gap: 8px 20px;">
                            <?php foreach ($variationCheckboxes as $checkbox): ?>
                            <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer; font-size: 13px;">
                                <input type="checkbox"
                                       class="temu-variation-checkbox"
                                       data-variation-key="<?php echo htmlspecialchars($checkbox['key']); ?>"
                                       <?php echo $checkbox['saved'] ? 'checked' : ''; ?>
                                />
                                <?php echo htmlspecialchars(html_entity_decode($checkbox['label'], ENT_QUOTES, 'UTF-8')); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
            </tbody>
            <script type="text/javascript">/*<![CDATA[*/
            (function () {
                var VARIATION_MAX = 2;
                var checkboxContainerId = 'temu-variation-checkboxes';
                var reactContainerId = 'temu-variation-root';
                window.temuVariationCheckboxContainerId = checkboxContainerId;
                window.temuAllVariationOptional = true;

                function getCheckboxes() {
                    var container = document.getElementById(checkboxContainerId);
                    return container ? container.querySelectorAll('.temu-variation-checkbox') : [];
                }

                function getCheckedCount() {
                    var count = 0;
                    var boxes = getCheckboxes();
                    for (var i = 0; i < boxes.length; i++) {
                        if (boxes[i].checked) count++;
                    }
                    return count;
                }

                function updateDisabledState() {
                    var checkedCount = getCheckedCount();
                    var boxes = getCheckboxes();
                    for (var i = 0; i < boxes.length; i++) {
                        if (!boxes[i].checked) {
                            boxes[i].disabled = checkedCount >= VARIATION_MAX;
                        }
                    }
                }

                function waitForReactApi(callback, retries) {
                    retries = retries || 0;
                    if (typeof window.magnalisterAddOptionalAttribute_temuVariation === 'function') {
                        callback();
                    } else if (retries < 50) {
                        setTimeout(function () { waitForReactApi(callback, retries + 1); }, 100);
                    }
                }

                function addVariation(key) {
                    waitForReactApi(function () {
                        window.magnalisterAddOptionalAttribute_temuVariation(key);
                    });
                }

                function removeVariation(key) {
                    var row = document.querySelector('#' + reactContainerId + ' tr[data-attribute-key="' + key + '"] .remove-matching-row');
                    if (row) {
                        row.click();
                    }
                }

                function handleCheckboxChange(e) {
                    var checkbox = e.target;
                    var key = checkbox.getAttribute('data-variation-key');

                    if (checkbox.checked) {
                        addVariation(key);
                    } else {
                        removeVariation(key);
                    }

                    updateDisabledState();

                    // Remove error message if exists
                    var existingError = document.getElementById('temu-variation-checkbox-error');
                    if (existingError && getCheckedCount() > 0) {
                        existingError.parentNode.removeChild(existingError);
                    }
                }

                function init() {
                    var boxes = getCheckboxes();
                    for (var i = 0; i < boxes.length; i++) {
                        boxes[i].addEventListener('change', handleCheckboxChange);
                    }
                    updateDisabledState();
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                } else {
                    init();
                }
            })();
            /*]]>*/</script>
            <?php
            $html .= ob_get_clean();
        } else {
            // Override title for variation section
            $variationProps['i18n']['requiredAttributesTitle'] = 'Temu Variation Attributes';
        }

        // Start capturing the variation API before it renders
        $html .= '<script type="text/javascript">if(typeof temuStartCapturingVariationApi==="function")temuStartCapturingVariationApi();</script>';
        $html .= renderTemuReactSectionHTML($variationProps, 'temu-variation-root', 'TemuVariationAttrs');
    } else {
        // Empty placeholder so AJAX replace works on first category selection
        $html .= '<tbody id="temu-variation-root" style="display: contents;"></tbody>';
    }

    // Section 2: Category Attributes (everything else)
    if (!empty($split['categoryAttributes'])) {
        $categoryProps = buildTemuReactComponentProps(
            $mpID, $productID, $mainCategory,
            $shopAttributes,
            $split['categoryAttributes'],
            $split['categorySavedValues'],
            $conditionalRules,
            $apiEndpoint
        );
        // Override title for category section
        $categoryProps['i18n']['requiredAttributesTitle'] = 'Temu Category Attributes';

        $html .= renderTemuReactSectionHTML($categoryProps, 'temu-catattr-root', 'TemuCategoryAttrs');
    } else {
        // Empty placeholder so AJAX replace works on first category selection
        $html .= '<tbody id="temu-catattr-root" style="display: contents;"></tbody>';
    }

    return $html;
}

/**
 * Render React variation matching for Temu preparation page (product-specific)
 *
 * @param int $productID Product ID (0 for multi-application)
 * @param array $data Product data with MainCategory etc.
 * @return string HTML output
 */
function renderTemuReactVariationMatching($productID, $data) {
    global $_MagnaSession, $_url;
    $debugMode = ((defined('MAGNA_DEBUG') && MAGNA_DEBUG) || (isset($_GET['MLDEBUG']) && $_GET['MLDEBUG'] === 'true'));

    if (!isset($_url['view'])) {
        $_url['view'] = isset($_GET['view']) ? $_GET['view'] : 'apply';
    }

    $mpID = $_MagnaSession['mpID'];
    $helper = new TemuReactHelper($mpID, $productID);

    // Get Main Category
    $mainCategory = 'none';
    if (isset($data['MainCategory']) && !empty($data['MainCategory']) && $data['MainCategory'] !== 'none') {
        $mainCategory = $data['MainCategory'];
    } elseif (isset($data['PrimaryCategory']) && !empty($data['PrimaryCategory']) && $data['PrimaryCategory'] !== 'none') {
        $mainCategory = $data['PrimaryCategory'];
    }

    // Get data from helper
    $shopAttributes = $helper->getShopAttributes();
    $marketplaceAttributes = $helper->getMarketplaceAttributes($mainCategory);
    $savedValues = $helper->getAttributeMatching();
    $conditionalRules = $helper->getConditionalRules($mainCategory);

    // DEBUG
    if ($debugMode) {
        echo '<!-- DEBUG Temu: mainCategory = ' . $mainCategory . ' -->';
        echo '<!-- DEBUG Temu: shopAttributes count = ' . count($shopAttributes) . ' -->';
        echo '<!-- DEBUG Temu: marketplaceAttributes count = ' . count($marketplaceAttributes) . ' -->';
    }

    // Build API endpoint URL
    $apiEndpoint = isset($_url) ? toURL($_url, array(
        'view'        => $_GET['view'],
        'kind'        => 'ajax',
        'applyAction' => 'react',
        'MLDEBUG'     => $debugMode ? 'true' : 'false'
    ), true) : '';

    // Render HTML (split into variation + category sections)
    $html = renderTemuReactVariationMatchingHTML_split($mpID, $productID, $mainCategory, $shopAttributes, $marketplaceAttributes, $savedValues, $conditionalRules, $apiEndpoint);

    // Prepare variables for JavaScript
    $ajaxUrl = toURL($_url, array(
        'view'        => $_GET['view'],
        'kind'        => 'ajax',
        'applyAction' => 'react'
    ), true);
    $productIDJs = (int)$productID;
    $mpIDJs = (int)$mpID;

    // Add category change listener and form validation
    ob_start();
    ?>
    <script type="text/javascript">
        (function () {
            jQuery(document).ready(function () {
                setupCategoryChangeListener();
            });

            /**
             * Listen for category changes and reload React component with new data
             */
            function setupCategoryChangeListener() {

                var selectors = ['#PrimaryCategory', '#maincat'];
                var foundCount = 0;

                for (var i = 0; i < selectors.length; i++) {
                    var testSelect = jQuery(selectors[i]);
                    if (testSelect.length > 0) {
                        foundCount++;

                        testSelect.on('change', function () {
                            var selectedValue = jQuery(this).val();

                            if (!selectedValue || selectedValue === 'none' || selectedValue === 'null') {
                                var containers = ['temu-variation-root', 'temu-catattr-root'];
                                for (var j = 0; j < containers.length; j++) {
                                    var container = document.getElementById(containers[j]);
                                    if (container) {
                                        container.style.display = 'none';
                                    }
                                }
                                return;
                            }

                            // Save pending changes before reloading
                            var proceedWithReload = function () {
                                if (typeof jQuery.blockUI === 'function') {
                                    mlShowLoading();
                                }
                                reloadReactComponent(selectedValue);
                            };

                            if (typeof window.magnalisterSaveAmazonVariations === 'function') {
                                window.magnalisterSaveAmazonVariations(proceedWithReload);
                            } else {
                                proceedWithReload();
                            }
                        });
                    }
                }

                if (foundCount === 0) {
                    console.error('[TemuVariations] No category selector found!');
                }
            }

            /**
             * Re-render a single React section using the already-loaded bundle.
             * Reuses existing ReactDOM root to avoid conflicts with multiple React instances.
             */
            function renderSection(containerId, props, logPrefix) {
                var container = document.getElementById(containerId);
                if (!container) return;

                if (!props || !props.marketplaceAttributes || (typeof props.marketplaceAttributes === 'object' && Object.keys(props.marketplaceAttributes).length === 0)) {
                    // No attributes for this section — unmount and clear
                    if (container._reactRoot) {
                        container._reactRoot.unmount();
                        container._reactRoot = null;
                    }
                    container.innerHTML = '';
                    return;
                }

                var AmazonVariations = window.MagnalisterAmazonVariations ? window.MagnalisterAmazonVariations.AmazonVariations : null;
                if (!AmazonVariations) {
                    console.error('[' + logPrefix + '] React component not found');
                    return;
                }

                props.onValuesChange = function(values) {
                    window[containerId + 'CurrentValues'] = values;
                };
                props.onValidationError = function(errors) {
                    window[containerId + 'ValidationErrors'] = errors;
                    window.temuVariationsValidationErrors = [].concat(
                        window['temu-variation-rootValidationErrors'] || [],
                        window['temu-catattr-rootValidationErrors'] || []
                    );
                };
                // Instance-scoped save namespace (see renderTemuReactSectionHTML) so this root
                // can be flushed independently on Save-and-Close without racing shared globals.
                props.apiNamespace = containerId;
                // Temu opt-in (BUG-019): strict save-failure reporting (see renderTemuReactSectionHTML)
                props.strictSave = true;

                var element = React.createElement(AmazonVariations, props);
                if (!container._reactRoot) {
                    container._reactRoot = ReactDOM.createRoot(container);
                }
                container._reactRoot.render(element);
            }

            /**
             * Reload React components with new category data via JSON props
             */
            function reloadReactComponent(categoryId) {

                var mainCategory = jQuery('#maincat').length > 0 ? jQuery('#maincat').val() : categoryId;

                jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo $ajaxUrl; ?>',
                    dataType: 'json',
                    data: {
                        'type': 'getReactComponentData',
                        'mainCategory': mainCategory,
                        'productID': <?php echo $productIDJs; ?>,
                        'mpID': <?php echo $mpIDJs; ?>
                    },
                    success: function (response) {
                        if (response.success) {
                            updateVariationCheckboxSection(response);
                            // Capture variation API before category section overwrites it
                            if (typeof temuStartCapturingVariationApi === 'function') temuStartCapturingVariationApi();
                            renderSection('temu-variation-root', response.variationProps, 'TemuVariationAttrs');
                            // Delay category render so variation API capture completes
                            setTimeout(function() {
                                renderSection('temu-catattr-root', response.categoryProps, 'TemuCategoryAttrs');
                            }, 100);
                        } else {
                            console.error('[TemuVariations] Failed to load:', response.message || 'Unknown error');
                            var errorContainers = ['temu-variation-root', 'temu-catattr-root'];
                            for (var k = 0; k < errorContainers.length; k++) {
                                var errContainer = document.getElementById(errorContainers[k]);
                                if (errContainer) {
                                    errContainer.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: red;">Error loading attributes. Please try again.</td></tr>';
                                }
                            }
                        }

                        if (typeof jQuery.unblockUI === 'function') {
                            mlHideLoading();
                        }
                    },
                    error: function (xhr, status, error) {
                        if (typeof jQuery.unblockUI === 'function') {
                            mlHideLoading();
                        }
                        console.error('[TemuVariations] AJAX error:', status, error);
                        var errorContainers = ['temu-variation-root', 'temu-catattr-root'];
                        for (var k = 0; k < errorContainers.length; k++) {
                            var errContainer = document.getElementById(errorContainers[k]);
                            if (errContainer) {
                                errContainer.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: red;">Error loading attributes. Please try again.</td></tr>';
                            }
                        }
                    }
                });
            }

            /**
             * Update the variation checkbox section after AJAX category change.
             * Creates or removes the checkbox UI based on whether all variation attributes are optional.
             */
            function updateVariationCheckboxSection(response) {
                var existingSection = document.getElementById('temu-variation-checkboxes-section');
                var variationRoot = document.getElementById('temu-variation-root');

                // Update CSS: add/remove styles for hiding React's optional selector
                var styleId = 'temu-variation-optional-styles';
                var existingStyle = document.getElementById(styleId);
                if (existingStyle) {
                    existingStyle.parentNode.removeChild(existingStyle);
                }

                if (response.allVariationOptional && response.variationCheckboxes && response.variationCheckboxes.length > 0) {
                    window.temuAllVariationOptional = true;

                    // Add CSS to hide React optional selector
                    var style = document.createElement('style');
                    style.id = styleId;
                    style.textContent = '#temu-variation-root .optional-attribute .attribute-label::after { content: " \\2022"; color: #e31a1c; font-size: 18px; margin-left: 5px; } #temu-variation-root .optional-attribute-selector-section { display: none; } #temu-variation-root .optional-attribute .remove-matching-row { display: none; } #temu-variation-root .optional-attributes-section > .headline { display: none; }';
                    document.head.appendChild(style);

                    // Build checkbox HTML
                    var titleText = '<?php echo defined("ML_TEMU_LABEL_VARIATION_TITLE") ? addslashes(ML_TEMU_LABEL_VARIATION_TITLE) : "Variation"; ?>';
                    var noteText = '<?php echo defined("ML_TEMU_LABEL_VARIATION_ALL_OPTIONAL_NOTE") ? addslashes(ML_TEMU_LABEL_VARIATION_ALL_OPTIONAL_NOTE) : "All variation attributes for this category are optional. However, you still need to select at least one variation attribute."; ?>';

                    var rows = '<tr class="headline"><td colspan="3"><h4>' + titleText + '</h4></td></tr>';
                    rows += '<tr><td colspan="3" style="padding: 10px 15px;"><p style="margin: 0; color: #666; font-style: italic;">' + noteText + '</p></td></tr>';
                    rows += '<tr><td colspan="3"><div id="temu-variation-checkboxes" class="temu-variation-checkboxes" style="padding: 10px 15px; display: flex; flex-wrap: wrap; gap: 8px 20px;">';

                    for (var i = 0; i < response.variationCheckboxes.length; i++) {
                        var cb = response.variationCheckboxes[i];
                        rows += '<label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer; font-size: 13px;">';
                        rows += '<input type="checkbox" class="temu-variation-checkbox" data-variation-key="' + cb.key + '"' + (cb.saved ? ' checked' : '') + ' />';
                        rows += cb.label;
                        rows += '</label>';
                    }

                    rows += '</div></td></tr>';

                    if (existingSection) {
                        existingSection.innerHTML = rows;
                    } else {
                        // Create new tbody before the variation React root
                        var newTbody = document.createElement('tbody');
                        newTbody.id = 'temu-variation-checkboxes-section';
                        newTbody.innerHTML = rows;
                        if (variationRoot) {
                            variationRoot.parentNode.insertBefore(newTbody, variationRoot);
                        }
                    }

                    window.temuVariationCheckboxContainerId = 'temu-variation-checkboxes';

                    // Bind checkbox event handlers
                    var VARIATION_MAX = 2;
                    var boxes = document.querySelectorAll('#temu-variation-checkboxes .temu-variation-checkbox');
                    function getCheckedCount() {
                        var count = 0;
                        for (var j = 0; j < boxes.length; j++) {
                            if (boxes[j].checked) count++;
                        }
                        return count;
                    }
                    function updateDisabled() {
                        var cc = getCheckedCount();
                        for (var j = 0; j < boxes.length; j++) {
                            if (!boxes[j].checked) boxes[j].disabled = cc >= VARIATION_MAX;
                        }
                    }
                    for (var b = 0; b < boxes.length; b++) {
                        boxes[b].addEventListener('change', function () {
                            var key = this.getAttribute('data-variation-key');
                            if (this.checked) {
                                (function waitApi(retries) {
                                    if (typeof window.magnalisterAddOptionalAttribute_temuVariation === 'function') {
                                        window.magnalisterAddOptionalAttribute_temuVariation(key);
                                    } else if (retries < 50) {
                                        setTimeout(function () { waitApi(retries + 1); }, 100);
                                    }
                                })(0);
                            } else {
                                var row = document.querySelector('#temu-variation-root tr[data-attribute-key="' + key + '"] .remove-matching-row');
                                if (row) row.click();
                            }
                            updateDisabled();
                            var err = document.getElementById('temu-variation-checkbox-error');
                            if (err && getCheckedCount() > 0) err.parentNode.removeChild(err);
                        });
                    }
                    updateDisabled();
                } else {
                    window.temuAllVariationOptional = false;
                    window.temuVariationCheckboxContainerId = null;

                    // Remove checkbox section if it exists
                    if (existingSection) {
                        existingSection.parentNode.removeChild(existingSection);
                    }
                }
            }

            // Form validation: block submit if required attributes are not matched
            (function setupFormValidation() {
                jQuery(document).ready(function () {
                    var form = document.querySelector('form[name="apply"]');
                    if (!form) {
                        return;
                    }

                    var submitButtons = document.querySelectorAll('.mlbtn-action[type="button"]');
                    if (submitButtons.length === 0) {
                        return;
                    }

                    submitButtons.forEach(function (button) {
                        button.addEventListener('click', function (e) {
                            // Check if variation checkboxes exist and at least one must be selected
                            if (window.temuAllVariationOptional && window.temuVariationCheckboxContainerId) {
                                var checkboxContainer = document.getElementById(window.temuVariationCheckboxContainerId);
                                if (checkboxContainer) {
                                    var checkboxes = checkboxContainer.querySelectorAll('.temu-variation-checkbox');
                                    var checkedCount = 0;
                                    for (var ci = 0; ci < checkboxes.length; ci++) {
                                        if (checkboxes[ci].checked) checkedCount++;
                                    }

                                    if (checkboxes.length > 0 && checkedCount === 0) {

                                        var errorId = 'temu-variation-checkbox-error';
                                        var existingError = document.getElementById(errorId);
                                        if (!existingError) {
                                            var errorDiv = document.createElement('div');
                                            errorDiv.id = errorId;
                                            errorDiv.style.cssText = 'padding: 8px 15px; color: #e31a1c; font-size: 13px; font-weight: bold;';
                                            errorDiv.textContent = '<?php echo defined('ML_TEMU_LABEL_VARIATION_SPECDETAILS_REQUIRED') ? ML_TEMU_LABEL_VARIATION_SPECDETAILS_REQUIRED : 'Please select at least one variation attribute.'; ?>';
                                            checkboxContainer.parentNode.insertBefore(errorDiv, checkboxContainer.nextSibling);
                                        }

                                        checkboxContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                        checkboxContainer.style.border = '2px solid #e31a1c';
                                        checkboxContainer.style.borderRadius = '4px';
                                        setTimeout(function () {
                                            checkboxContainer.style.border = '';
                                            checkboxContainer.style.borderRadius = '';
                                        }, 2500);

                                        e.preventDefault();
                                        e.stopPropagation();
                                        return false;
                                    } else {
                                        var existingError = document.getElementById('temu-variation-checkbox-error');
                                        if (existingError) {
                                            existingError.parentNode.removeChild(existingError);
                                        }
                                    }
                                }
                            }

                            // Check validation errors
                            if (typeof window.temuVariationsValidationErrors !== 'undefined' &&
                                window.temuVariationsValidationErrors.length > 0) {

                                if (typeof jQuery !== 'undefined' && typeof jQuery.unblockUI === 'function') {
                                    mlHideLoading();
                                }

                                var scrollEvent = new CustomEvent('amazon-variations-scroll-to-error');
                                document.dispatchEvent(scrollEvent);

                                e.preventDefault();
                                e.stopPropagation();
                                return false;
                            }

                            if (typeof mlShowLoading === 'function') {
                                mlShowLoading();
                            }

                            e.preventDefault();
                            e.stopPropagation();

                            var proceedWithSubmit = function () {
                                // Disable old attribute matching inputs before form submission
                                var attributeMatchingInputs = form.querySelectorAll('[name^="ml[match]"]');
                                attributeMatchingInputs.forEach(function (input) {
                                    input.disabled = true;
                                });

                                var reactContainerIds = ['temu-variation-root', 'temu-catattr-root'];
                                for (var ci = 0; ci < reactContainerIds.length; ci++) {
                                    var reactContainer = document.getElementById(reactContainerIds[ci]);
                                    if (reactContainer) {
                                        var reactInputs = reactContainer.querySelectorAll('input, select, textarea');
                                        reactInputs.forEach(function (input) {
                                            if (input.name) {
                                                input.disabled = true;
                                            }
                                        });
                                    }
                                }

                                var submitEvent = new Event('submit', {bubbles: true, cancelable: true});
                                var cancelled = !form.dispatchEvent(submitEvent);
                                if (!cancelled) {
                                    form.submit();
                                }
                            };

                            if (typeof window.magnalisterSaveAmazonVariations === 'function') {
                                window.magnalisterSaveAmazonVariations(proceedWithSubmit);
                            } else {
                                proceedWithSubmit();
                            }

                            return false;
                        }, true);
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
 * Handle AJAX save request for Temu attribute matching
 *
 * Supports V3 format: ml[action], ml[attributeKey], ml[attributeData], ml[variationGroup]
 */
function handleTemuSaveAttributeMatching() {
    global $_MagnaSession;
    $debugMode = ((defined('MAGNA_DEBUG') && MAGNA_DEBUG) || (isset($_GET['MLDEBUG']) && $_GET['MLDEBUG'] === 'true'));

    // V3 format (ml[action])
    if (isset($_POST['ml']['action']) && $_POST['ml']['action'] === 'saveAttributeMatching') {
        if (!isset($_POST['ml']['attributeKey']) || !isset($_POST['ml']['variationGroup'])) {
            die(json_encode(array('success' => false, 'message' => 'Missing parameters (V3 format)')));
        }

        $attributeKey = $_POST['ml']['attributeKey'];
        $variationGroup = $_POST['ml']['variationGroup'];
        $actionType = isset($_POST['ml']['actionType']) ? $_POST['ml']['actionType'] : 'save';
        $customIdentifier = isset($_POST['ml']['customIdentifier']) ? (int)$_POST['ml']['customIdentifier'] : 0;

        if (empty($variationGroup) || $variationGroup === 'none') {
            die(json_encode(array(
                'success' => false,
                'message' => 'Please select a valid category before saving'
            )));
        }

        $mpID = isset($_POST['mpID']) ? (int)$_POST['mpID'] : $_MagnaSession['mpID'];
        $productID = $customIdentifier > 0 ? $customIdentifier : (isset($_POST['productID']) ? (int)$_POST['productID'] : 0);

        if ($actionType === 'delete') {
            $attributeMatching = array(
                $attributeKey => null
            );
        } else {
            if (!isset($_POST['ml']['attributeData'])) {
                die(json_encode(array('success' => false, 'message' => 'Missing attribute data')));
            }

            $attributeData = json_decode($_POST['ml']['attributeData'], true);
            if (!is_array($attributeData)) {
                die(json_encode(array('success' => false, 'message' => 'Invalid attribute data format')));
            }

            $attributeMatching = array(
                $attributeKey => $attributeData
            );
        }
    } else {
        // V2 format fallback
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

    $helper = new TemuReactHelper($mpID, $productID);

    // Validate
    $validation = $helper->validateAttributeMatching($attributeMatching);
    if (!$validation['valid']) {
        die(json_encode(array(
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validation['errors']
        )));
    }

    // Save
    try {
        $result = $helper->saveAttributeMatching($attributeMatching);

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
 * Handle AJAX batch save request for Temu attribute matching (V3 format)
 * Saves multiple attributes at once.
 *
 * Expected POST parameters:
 * - ml[action] = 'saveAttributeMatchingBatch'
 * - ml[attributesData] = JSON encoded object with multiple attributes
 * - ml[variationGroup] = Category ID
 * - ml[customIdentifier] = Product ID
 */
function handleTemuSaveAttributeMatchingBatch() {
    global $_MagnaSession;
    $debugMode = ((defined('MAGNA_DEBUG') && MAGNA_DEBUG) || (isset($_GET['MLDEBUG']) && $_GET['MLDEBUG'] === 'true'));

    if (!isset($_POST['ml']['attributesData']) || !isset($_POST['ml']['variationGroup'])) {
        die(json_encode(array('success' => false, 'message' => 'Missing parameters (batch save)')));
    }

    $attributesData = json_decode($_POST['ml']['attributesData'], true);
    if (!is_array($attributesData)) {
        die(json_encode(array('success' => false, 'message' => 'Invalid attributes data format')));
    }

    $variationGroup = $_POST['ml']['variationGroup'];
    $customIdentifier = isset($_POST['ml']['customIdentifier']) ? (int)$_POST['ml']['customIdentifier'] : 0;

    if (empty($variationGroup) || $variationGroup === 'none') {
        die(json_encode(array(
            'success' => false,
            'message' => 'Please select a valid category before saving'
        )));
    }

    $mpID = isset($_POST['mpID']) ? (int)$_POST['mpID'] : $_MagnaSession['mpID'];
    $productID = $customIdentifier > 0 ? $customIdentifier : (isset($_POST['productID']) ? (int)$_POST['productID'] : 0);

    $helper = new TemuReactHelper($mpID, $productID);

    // Validate
    $validation = $helper->validateAttributeMatching($attributesData);
    if (!$validation['valid']) {
        die(json_encode(array(
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validation['errors']
        )));
    }

    // Save
    try {
        $result = $helper->saveAttributeMatching($attributesData);

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
 * Handle AJAX request for getting React component data when category changes
 */
function handleTemuGetReactComponentData() {
    global $_MagnaSession, $_url;
    $debugMode = ((defined('MAGNA_DEBUG') && MAGNA_DEBUG) || (isset($_GET['MLDEBUG']) && $_GET['MLDEBUG'] === 'true'));

    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_POST['productID']) || !isset($_POST['mpID'])) {
        die(json_encode(array('success' => false, 'message' => 'Missing parameters')));
    }

    $mainCategory = isset($_POST['mainCategory']) ? $_POST['mainCategory'] : null;
    $productID = (int)$_POST['productID'];
    $mpID = (int)$_POST['mpID'];

    if (empty($mainCategory) || $mainCategory === 'none' || $mainCategory === 'null') {
        die(json_encode(array(
            'success' => false,
            'message' => 'Please select a valid category'
        )));
    }

    try {
        $helper = new TemuReactHelper($mpID, $productID);

        $shopAttributes = $helper->getShopAttributes();
        $marketplaceAttributes = $helper->getMarketplaceAttributes($mainCategory);
        $savedValues = $helper->getAttributeMatching();
        $conditionalRules = $helper->getConditionalRules($mainCategory);

        // Build API endpoint URL
        $apiEndpoint = isset($_url) ? toURL($_url, array(
            'view'        => $_GET['view'],
            'kind'        => 'ajax',
            'applyAction' => 'react',
            'MLDEBUG'     => $debugMode ? 'true' : 'false'
        ), true) : '';

        // Split attributes into variation vs category
        $split = splitTemuAttributesByType($marketplaceAttributes, $savedValues);

        // Detect if all variation attributes are optional
        $blAllVariationOptional = true;
        if (!empty($split['variationAttributes'])) {
            foreach ($split['variationAttributes'] as $attr) {
                if (!empty($attr['required'])) {
                    $blAllVariationOptional = false;
                    break;
                }
            }
        } else {
            $blAllVariationOptional = false;
        }

        // Build checkbox data for all-optional case
        $variationCheckboxes = array();
        if ($blAllVariationOptional) {
            foreach ($split['variationAttributes'] as $sKey => $aAttr) {
                $variationCheckboxes[] = array(
                    'key'   => $sKey,
                    'label' => isset($aAttr['value']) ? $aAttr['value'] : $sKey,
                    'saved' => isset($split['variationSavedValues'][$sKey]) && !empty($split['variationSavedValues'][$sKey]['Code']),
                );
            }
        }

        // Build props for variation section
        $variationProps = null;
        if (!empty($split['variationAttributes'])) {
            $variationProps = buildTemuReactComponentProps(
                $mpID, $productID, $mainCategory,
                $shopAttributes,
                $split['variationAttributes'],
                $split['variationSavedValues'],
                array(),
                $apiEndpoint
            );
            if ($blAllVariationOptional) {
                $variationProps['i18n']['requiredAttributesTitle'] = ' ';
            } else {
                $variationProps['i18n']['requiredAttributesTitle'] = 'Temu Variation Attributes';
            }
        }

        // Build props for category section
        $categoryProps = null;
        if (!empty($split['categoryAttributes'])) {
            $categoryProps = buildTemuReactComponentProps(
                $mpID, $productID, $mainCategory,
                $shopAttributes,
                $split['categoryAttributes'],
                $split['categorySavedValues'],
                $conditionalRules,
                $apiEndpoint
            );
            $categoryProps['i18n']['requiredAttributesTitle'] = 'Temu Category Attributes';
        }

        die(json_encode(array(
            'success'                => true,
            'variationProps'         => $variationProps,
            'categoryProps'          => $categoryProps,
            'allVariationOptional'   => $blAllVariationOptional,
            'variationCheckboxes'    => $variationCheckboxes,
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
 * @param string $categoryId Category ID
 * @param array $urlResources URL resources for API endpoint (optional)
 * @return string HTML output
 */
function renderTemuReactVariationMatchingTemplate($categoryId, $urlResources) {
    global $_MagnaSession, $_url;
    $debugMode = ((defined('MAGNA_DEBUG') && MAGNA_DEBUG) || (isset($_GET['MLDEBUG']) && $_GET['MLDEBUG'] === 'true'));

    if (!isset($_url['view'])) {
        $_url['view'] = isset($_GET['view']) ? $_GET['view'] : 'varmatch';
    }

    $mpID = $_MagnaSession['mpID'];
    $productID = 0; // Global template
    $helper = new TemuReactHelper($mpID, $productID);

    $mainCategory = $categoryId;

    // Get data from helper
    $shopAttributes = $helper->getShopAttributes();
    $marketplaceAttributes = $helper->getMarketplaceAttributes($mainCategory);

    // Get saved values from variantmatching table
    $oldPost = $_POST;
    $_POST['PrimaryCategory'] = $categoryId;
    $_POST['mainCategory'] = $categoryId;
    $savedValues = $helper->getAttributeMatching();
    $_POST = $oldPost;

    $conditionalRules = $helper->getConditionalRules($mainCategory);

    // Build API endpoint URL
    if ($urlResources !== null) {
        $apiEndpoint = toURL($urlResources, array(
            'kind'        => 'ajax',
            'applyAction' => 'react',
            'MLDEBUG'     => $debugMode ? 'true' : 'false'
        ), true);
    } else {
        $apiEndpoint = isset($_url) ? toURL($_url, array(
            'view'        => isset($_GET['view']) ? $_GET['view'] : 'apply',
            'kind'        => 'ajax',
            'applyAction' => 'react',
            'MLDEBUG'     => $debugMode ? 'true' : 'false'
        ), true) : '';
    }

    // Render HTML (split into variation + category sections)
    $html = renderTemuReactVariationMatchingHTML_split($mpID, $productID, $mainCategory, $shopAttributes, $marketplaceAttributes, $savedValues, $conditionalRules, $apiEndpoint);

    // Prepare variables for JavaScript
    $ajaxUrlTemplate = isset($_url) ? toURL($_url, array(
        'view'        => isset($_GET['view']) ? $_GET['view'] : 'varmatch',
        'kind'        => 'ajax',
        'applyAction' => 'react'
    ), true) : '';
    $mpIDJsTemplate = (int)$mpID;

    // Add category change listener for global template
    ob_start();
    ?>
    <script type="text/javascript">
        (function () {
            jQuery(document).ready(function () {
                setupCategoryChangeListener();
            });

            function setupCategoryChangeListener() {

                var selector = jQuery('#PrimaryCategory');
                if (selector.length === 0) {
                    console.error('[TemuVariations] #PrimaryCategory not found!');
                    return;
                }

                selector.on('change', function () {
                    var selectedValue = jQuery(this).val();

                    if (!selectedValue || selectedValue === 'none' || selectedValue === 'null') {
                        var containers = ['temu-variation-root', 'temu-catattr-root'];
                        for (var j = 0; j < containers.length; j++) {
                            var container = document.getElementById(containers[j]);
                            if (container) {
                                container.style.display = 'none';
                            }
                        }
                        return;
                    }

                    reloadReactComponent(selectedValue);
                });
            }

            /**
             * Re-render a single React section using the already-loaded bundle.
             */
            function renderSection(containerId, props, logPrefix) {
                var container = document.getElementById(containerId);
                if (!container) return;

                if (!props || !props.marketplaceAttributes || (typeof props.marketplaceAttributes === 'object' && Object.keys(props.marketplaceAttributes).length === 0)) {
                    if (container._reactRoot) {
                        container._reactRoot.unmount();
                        container._reactRoot = null;
                    }
                    container.innerHTML = '';
                    return;
                }

                var AmazonVariations = window.MagnalisterAmazonVariations ? window.MagnalisterAmazonVariations.AmazonVariations : null;
                if (!AmazonVariations) {
                    console.error('[' + logPrefix + '] React component not found');
                    return;
                }

                props.onValuesChange = function(values) {
                    window[containerId + 'CurrentValues'] = values;
                };
                props.onValidationError = function(errors) {
                    window[containerId + 'ValidationErrors'] = errors;
                    window.temuVariationsValidationErrors = [].concat(
                        window['temu-variation-rootValidationErrors'] || [],
                        window['temu-catattr-rootValidationErrors'] || []
                    );
                };
                // Instance-scoped save namespace (see renderTemuReactSectionHTML) so this root
                // can be flushed independently on Save-and-Close without racing shared globals.
                props.apiNamespace = containerId;
                // Temu opt-in (BUG-019): strict save-failure reporting (see renderTemuReactSectionHTML)
                props.strictSave = true;

                var element = React.createElement(AmazonVariations, props);
                if (!container._reactRoot) {
                    container._reactRoot = ReactDOM.createRoot(container);
                }
                container._reactRoot.render(element);
            }

            /**
             * Update the variation checkbox section after AJAX category change (Template mode).
             */
            function updateVariationCheckboxSection(response) {
                var existingSection = document.getElementById('temu-variation-checkboxes-section');
                var variationRoot = document.getElementById('temu-variation-root');

                var styleId = 'temu-variation-optional-styles';
                var existingStyle = document.getElementById(styleId);
                if (existingStyle) existingStyle.parentNode.removeChild(existingStyle);

                if (response.allVariationOptional && response.variationCheckboxes && response.variationCheckboxes.length > 0) {
                    window.temuAllVariationOptional = true;

                    var style = document.createElement('style');
                    style.id = styleId;
                    style.textContent = '#temu-variation-root .optional-attribute .attribute-label::after { content: " \\2022"; color: #e31a1c; font-size: 18px; margin-left: 5px; } #temu-variation-root .optional-attribute-selector-section { display: none; } #temu-variation-root .optional-attribute .remove-matching-row { display: none; } #temu-variation-root .optional-attributes-section > .headline { display: none; }';
                    document.head.appendChild(style);

                    var titleText = '<?php echo defined("ML_TEMU_LABEL_VARIATION_TITLE") ? addslashes(ML_TEMU_LABEL_VARIATION_TITLE) : "Variation"; ?>';
                    var noteText = '<?php echo defined("ML_TEMU_LABEL_VARIATION_ALL_OPTIONAL_NOTE") ? addslashes(ML_TEMU_LABEL_VARIATION_ALL_OPTIONAL_NOTE) : "All variation attributes for this category are optional. However, you still need to select at least one variation attribute."; ?>';

                    var rows = '<tr class="headline"><td colspan="3"><h4>' + titleText + '</h4></td></tr>';
                    rows += '<tr><td colspan="3" style="padding: 10px 15px;"><p style="margin: 0; color: #666; font-style: italic;">' + noteText + '</p></td></tr>';
                    rows += '<tr><td colspan="3"><div id="temu-variation-checkboxes" class="temu-variation-checkboxes" style="padding: 10px 15px; display: flex; flex-wrap: wrap; gap: 8px 20px;">';

                    for (var i = 0; i < response.variationCheckboxes.length; i++) {
                        var cb = response.variationCheckboxes[i];
                        rows += '<label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer; font-size: 13px;">';
                        rows += '<input type="checkbox" class="temu-variation-checkbox" data-variation-key="' + cb.key + '"' + (cb.saved ? ' checked' : '') + ' />';
                        rows += cb.label;
                        rows += '</label>';
                    }
                    rows += '</div></td></tr>';

                    if (existingSection) {
                        existingSection.innerHTML = rows;
                    } else {
                        var newTbody = document.createElement('tbody');
                        newTbody.id = 'temu-variation-checkboxes-section';
                        newTbody.innerHTML = rows;
                        if (variationRoot) {
                            variationRoot.parentNode.insertBefore(newTbody, variationRoot);
                        }
                    }

                    window.temuVariationCheckboxContainerId = 'temu-variation-checkboxes';

                    var VARIATION_MAX = 2;
                    var boxes = document.querySelectorAll('#temu-variation-checkboxes .temu-variation-checkbox');
                    function getCheckedCount() {
                        var count = 0;
                        for (var j = 0; j < boxes.length; j++) {
                            if (boxes[j].checked) count++;
                        }
                        return count;
                    }
                    function updateDisabled() {
                        var cc = getCheckedCount();
                        for (var j = 0; j < boxes.length; j++) {
                            if (!boxes[j].checked) boxes[j].disabled = cc >= VARIATION_MAX;
                        }
                    }
                    for (var b = 0; b < boxes.length; b++) {
                        boxes[b].addEventListener('change', function () {
                            var key = this.getAttribute('data-variation-key');
                            if (this.checked) {
                                (function waitApi(retries) {
                                    if (typeof window.magnalisterAddOptionalAttribute_temuVariation === 'function') {
                                        window.magnalisterAddOptionalAttribute_temuVariation(key);
                                    } else if (retries < 50) {
                                        setTimeout(function () { waitApi(retries + 1); }, 100);
                                    }
                                })(0);
                            } else {
                                var row = document.querySelector('#temu-variation-root tr[data-attribute-key="' + key + '"] .remove-matching-row');
                                if (row) row.click();
                            }
                            updateDisabled();
                            var err = document.getElementById('temu-variation-checkbox-error');
                            if (err && getCheckedCount() > 0) err.parentNode.removeChild(err);
                        });
                    }
                    updateDisabled();
                } else {
                    window.temuAllVariationOptional = false;
                    window.temuVariationCheckboxContainerId = null;
                    if (existingSection) existingSection.parentNode.removeChild(existingSection);
                }
            }

            function reloadReactComponent(categoryId) {

                if (typeof jQuery.blockUI === 'function' && typeof blockUILoading !== 'undefined') {
                    mlShowLoading();
                }

                jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo $ajaxUrlTemplate; ?>',
                    dataType: 'text',
                    data: {
                        'type': 'getReactComponentData',
                        'mainCategory': categoryId,
                        'productID': 0,
                        'mpID': <?php echo $mpIDJsTemplate; ?>
                    },
                    success: function (responseText) {
                        var response = null;
                        try {
                            response = JSON.parse(responseText);
                        } catch (e) {
                            console.error('[TemuVariations] JSON parse error:', e.message);
                        }
                        if (response && response.success) {
                            updateVariationCheckboxSection(response);
                            // Capture variation API before category section overwrites it
                            if (typeof temuStartCapturingVariationApi === 'function') temuStartCapturingVariationApi();
                            renderSection('temu-variation-root', response.variationProps, 'TemuVariationAttrs');
                            // Delay category render so variation API capture completes
                            setTimeout(function() {
                                renderSection('temu-catattr-root', response.categoryProps, 'TemuCategoryAttrs');
                            }, 100);
                        } else {
                            console.error('[TemuVariations] Failed to load:', (response && response.message) || 'Unknown error');
                            var errorContainers = ['temu-variation-root', 'temu-catattr-root'];
                            for (var k = 0; k < errorContainers.length; k++) {
                                var errContainer = document.getElementById(errorContainers[k]);
                                if (errContainer) {
                                    errContainer.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 20px; color: red;">Error loading attributes.</td></tr>';
                                }
                            }
                        }

                        if (typeof jQuery.unblockUI === 'function') {
                            mlHideLoading();
                        }
                    },
                    error: function (xhr, status, error) {
                        if (typeof jQuery.unblockUI === 'function') {
                            mlHideLoading();
                        }
                        console.error('[TemuVariations] AJAX error:', status, error);
                        var errorContainers = ['temu-variation-root', 'temu-catattr-root'];
                        for (var k = 0; k < errorContainers.length; k++) {
                            var errContainer = document.getElementById(errorContainers[k]);
                            if (errContainer) {
                                errContainer.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 20px; color: red;">Error loading attributes.</td></tr>';
                            }
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
 * Render the category-independent attributes section as a separate React component instance.
 * Uses variationGroup = 'category_independent_attributes' and loads data from GetCategoryIndependentAttributes API.
 *
 * @param array $urlResources URL resources for AJAX endpoint
 * @return string HTML output
 */
function renderTemuCategoryIndependentAttributes($urlResources) {
    global $_MagnaSession;
    $mpID = $_MagnaSession['mpID'];

    $helper = new TemuReactHelper($mpID, 0); // view=apply constructor derives the product id
    $iProductID = $helper->getProductID();

    // Fetch CI attributes from API
    $ciAttributes = $helper->getCategoryIndependentAttributes();
    if (empty($ciAttributes)) {
        return ''; // No CI attributes available
    }

    // Load saved values
    $savedValues = $helper->loadCategoryIndependentMatching();

    // Get shop attributes (same as for category-dependent)
    $shopAttributes = $helper->getShopAttributes();

    // Build React props
    $reactProps = buildTemuReactComponentProps(
        $mpID,
        $iProductID,
        'category_independent_attributes',
        $shopAttributes,
        $ciAttributes,
        $savedValues,
        array(), // No conditional rules for CI attributes
        toURL($urlResources, array('mode' => 'prepare', 'kind' => 'ajax', 'applyAction' => 'react'), true)
    );

    // Reuse the shared section renderer which properly stores _reactRoot
    return renderTemuReactSectionHTML($reactProps, 'temu-ci-attributes-root', 'TemuCIAttributes');
}

/**
 * Handle save for category-independent attributes via AJAX.
 */
function handleTemuSaveCategoryIndependentMatching() {
    global $_MagnaSession;
    $mpID = $_MagnaSession['mpID'];

    $customIdentifier = isset($_POST['ml']['customIdentifier']) ? (int)$_POST['ml']['customIdentifier'] : 0;
    $productID = $customIdentifier > 0 ? $customIdentifier : (isset($_POST['productID']) ? (int)$_POST['productID'] : 0);
    $helper = new TemuReactHelper($mpID, $productID);

    $response = array('success' => false);

    if (isset($_POST['ml']['attributesData'])) {
        // Batch save
        $attributesData = json_decode($_POST['ml']['attributesData'], true);
        if (is_array($attributesData)) {
            $helper->saveCategoryIndependentMatching($attributesData);
            $response['success'] = true;
        }
    } elseif (isset($_POST['ml']['attributeKey']) && isset($_POST['ml']['attributeData'])) {
        // Single save
        $key = $_POST['ml']['attributeKey'];
        $data = json_decode($_POST['ml']['attributeData'], true);
        if (is_array($data)) {
            $helper->saveCategoryIndependentMatching(array($key => $data));
            $response['success'] = true;
        }
    }

    echo json_encode($response);
    die();
}

/**
 * Check if React variation matching should be used for Temu
 * @return bool
 */
function shouldUseTemuReactVariationMatching() {
    return true;
}
