import React from 'react';
import {
  ConditionalRule,
    DatabaseTablesData,
  I18nStrings,
  MarketplaceAttribute,
  MatchingValue,
  SavedAttributeValue,
  SavedValues,
  ShopAttribute,
  ShopAttributes
} from '@/types';
import AttributeSelector from '@/components/AmazonVariations/AttributeSelector';
import FreeTextInput from '@/components/AmazonVariations/ValueMatching/FreeTextInput';
// V2 OVERRIDE: Import DatabaseValueInput from v2-overrides (with i18n support)
import DatabaseValueInput, {DatabaseValue} from './DatabaseValueInput';
import AmazonValueSelector from '@/components/AmazonVariations/ValueMatching/AmazonValueSelector';
// V2 OVERRIDE: Import ValueMatchingTable from v2-overrides (with checkbox disabled)
import ValueMatchingTable from './ValueMatchingTable';
import {createSafeHtml} from '@/components/AmazonVariations/utils/htmlSanitizer';
import {evaluateConditionalRules, getAffectedTargetFields} from '@/utils/conditionalRules';
import {generateConditionalRulesHelpText, initializeConditionalRuleLinks} from '@/utils/conditionalRulesHelper';
import {UserChangeContext} from '@/AmazonVariations';
import {DESCRIPTION_BOX_STYLES} from '@/components/AmazonVariations/styles/infoBoxStyles';
import {applyStyleWithImportant} from '@/utils/styleUtils';
import {useHighlightController} from '@/components/AmazonVariations/AttributeRow/HighlightController';

interface AttributeRowProps {
    attributeKey: string;
    attribute: MarketplaceAttribute;
    isRequired: boolean;
    currentValue?: SavedAttributeValue;
    allAttributeValues: SavedValues; // All current attribute values for evaluating conditional rules
    conditionalRules?: ConditionalRule[]; // Conditional rules from Amazon API
    allMarketplaceAttributes?: Record<string, MarketplaceAttribute>; // All marketplace attributes for help text generation
    variationGroup: string;
    marketplaceName: string;
    shopAttributes: ShopAttributes;
    i18n: I18nStrings;
    databaseTables?: DatabaseTablesData; // Database tables/columns for database_value matching (v2 only)
    disabled?: boolean;
    debugMode?: boolean;
    hideHelpColumn?: boolean; // Hide help column (v2 compatibility)
    error?: string; // Error message to display inline
    onAttributeChange: (attributeKey: string, value: SavedAttributeValue) => void;
    onRemoveOptionalAttribute?: (attributeKey: string) => void;
    onFetchShopAttributeValues?: (attributeCode: string) => Promise<{ [key: string]: string }>;
}

/**
 * AttributeRow Component
 *
 * Renders a complete attribute row with:
 * - Attribute label with required indicator
 * - Shop attribute selector
 * - Dynamic value matching interface based on selection
 * - Description/info column
 */
const AttributeRow: React.FC<AttributeRowProps> = ({
                                                       attributeKey,
                                                       attribute,
                                                       isRequired,
                                                       currentValue,
                                                       allAttributeValues,
                                                       conditionalRules = [],
                                                       allMarketplaceAttributes = {},
                                                       variationGroup,
                                                       marketplaceName,
                                                       shopAttributes,
                                                       i18n,
                                                       databaseTables,
                                                       disabled = false,
                                                       debugMode = false,
                                                       hideHelpColumn = false,
                                                       error,
                                                       onAttributeChange,
                                                       onRemoveOptionalAttribute,
                                                       onFetchShopAttributeValues
                                                   }) => {
    const selectedCode = currentValue?.Code;

    // Get user change context to know which attribute user just changed
    const {lastChangedAttribute} = React.useContext(UserChangeContext);

    // Evaluate conditional rules to get filtered allowed values for this attribute
    const filteredAllowedValues = React.useMemo(() => {
        if (!conditionalRules || conditionalRules.length === 0) {
            return null; // No rules, no restrictions
        }

        // Enable debug for this specific attribute to see why rules aren't matching
        const shouldDebug = attributeKey === 'shirt_size__size_class';

        const allowedValues = evaluateConditionalRules(
            conditionalRules,
            allAttributeValues,
            attributeKey,
            shouldDebug // debug enabled for shirt_size__size_class
        );

        if (shouldDebug) {
            console.log(`[AttributeRow] ${attributeKey}: filteredAllowedValues =`, allowedValues);
            console.log(`[AttributeRow] ${attributeKey}: allAttributeValues =`, allAttributeValues);
        }

        return allowedValues;
    }, [conditionalRules, allAttributeValues, attributeKey, attribute.values]);

    // Create filtered attribute object if conditional rules apply
    const filteredAttribute = React.useMemo(() => {
        if (!filteredAllowedValues || !attribute.values) {
            return attribute; // No filtering needed
        }

        // Filter the attribute values based on allowed values
        const filteredValues: { [key: string]: string } = {};
        filteredAllowedValues.forEach(allowedKey => {
            if (attribute.values && attribute.values[allowedKey]) {
                filteredValues[allowedKey] = attribute.values[allowedKey];
            }
        });

        return {
            ...attribute,
            values: filteredValues
        };
    }, [attribute, filteredAllowedValues]);

    // Handle shop attribute selection change
    const handleShopAttributeChange = (value: string) => {
        onAttributeChange(attributeKey, {
            ...currentValue,
            Code: value,
            Values: undefined // Reset values when changing selection
        });
    };

    // Track if component has mounted to prevent initial save
    const hasMounted = React.useRef(false);

    // Track initial render specifically for highlight animation
    const isInitialRender = React.useRef(true);

    // Track previous filteredAllowedValues to detect actual changes
    const prevFilteredValuesRef = React.useRef<string[] | null>(null);

    // Effect to mark component as mounted after first render
    React.useEffect(() => {
        hasMounted.current = true;
        // Mark as not initial render after first render completes
        isInitialRender.current = false;
    }, []);

    // Ref for the entire attribute row (for scrolling)
    const rowRef = React.useRef<HTMLTableRowElement>(null);

    // Ref for description box styling with !important
    const descriptionBoxRef = React.useRef<HTMLDivElement>(null);

    // Use HighlightController for managing highlight animation state
    // This provides a trackable, IDE-friendly API instead of anonymous useState
    // You can Ctrl+Click on controller.enable() to see implementation
    const {controller: highlightController, isActive: shouldHighlight} = useHighlightController(attributeKey);

    /**
     * Scroll & Highlight Effect for Conditional Rules
     *
     * When conditional rules filter the Amazon dropdown options, this effect:
     * 1. Scrolls to the target field (smooth scroll to center)
     * 2. Waits for scroll to complete (600ms)
     * 3. Triggers yellow border highlight animation (3 flashes, 2.4s total)
     *
     * This provides visual feedback to the user that the field's options have been
     * automatically filtered based on their selection in another field.
     *
     * Example:
     * - User selects shirt_form_type = "Polo-Hemd"
     * - collar_style options get filtered to only ["Polokragen"]
     * - Page scrolls to collar_style field
     * - Border flashes yellow 3 times to draw attention
     *
     * Technical Notes:
     * - Uses isInitialRender to prevent scroll on page load
     * - Checks if filteredAllowedValues has changed from initial load value
     * - Only triggers when selectedCode is 'attribute_value' (Amazon selector is visible)
     * - Only triggers when filteredAllowedValues is not null (rules matched)
     * - Scroll timing: 600ms (to allow smooth scroll to complete)
     * - Highlight timing: 2400ms animation + 100ms buffer = 2500ms total
     * - IMPORTANT: Cleanup timers on unmount to prevent memory leaks
     */
    React.useEffect(() => {
        // Skip on initial render to prevent unwanted scroll when page loads
        if (isInitialRender.current) {
            //console.log(`[Scroll/Highlight] ${attributeKey}: Skipped - initial render`);
            prevFilteredValuesRef.current = filteredAllowedValues;
            return;
        }

        // Check if filteredAllowedValues has actually changed from previous value
        const prevValue = prevFilteredValuesRef.current;
        const hasChanged = JSON.stringify(prevValue) !== JSON.stringify(filteredAllowedValues);

        // Check if THIS field is affected by the field user just changed
        const isAffectedByUserChange = lastChangedAttribute ?
            getAffectedTargetFields(conditionalRules, lastChangedAttribute).includes(attributeKey) :
            false;

        // console.log(`[Scroll/Highlight] ${attributeKey}: Checking conditions...`, {
        //   selectedCode,
        //   filteredAllowedValues,
        //   prevValue,
        //   hasChanged,
        //   lastChangedAttribute,
        //   isAffectedByUserChange
        // });

        // Update ref for next comparison
        prevFilteredValuesRef.current = filteredAllowedValues;

        // Store timer IDs for cleanup
        let scrollTimer: NodeJS.Timeout | null = null;
        let highlightTimer: NodeJS.Timeout | null = null;

        // Only trigger if:
        // 1. Conditional rules have been applied (filteredAllowedValues !== null)
        // 2. Values have actually changed from previous render (hasChanged === true)
        // 3. This field is affected by the field user just changed (isAffectedByUserChange === true)
        // Note: We don't check selectedCode because we want to scroll even if user hasn't selected shop attribute yet
        if (filteredAllowedValues !== null && hasChanged && isAffectedByUserChange) {
            // console.log(`[Scroll/Highlight] ${attributeKey}: ✅ All conditions met! Scrolling...`);
            // Scroll to the target field (attribute row)
            if (rowRef.current) {
                const targetElement = rowRef.current;

                // Scroll with 'center' positioning so field is visible with space above/below
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                // Use scroll event listener to detect when scrolling is complete
                // This is more reliable than setTimeout as it waits for actual scroll end
                let scrollEndTimer: NodeJS.Timeout | null = null;
                const handleScrollEnd = () => {
                    // Clear any existing timer
                    if (scrollEndTimer) {
                        clearTimeout(scrollEndTimer);
                    }

                    // Set new timer - if no scroll event for 150ms, consider scroll complete
                    scrollEndTimer = setTimeout(() => {
                        // console.log(`[Scroll/Highlight] ${attributeKey}: Scroll completed, triggering highlight`);

                        // Remove scroll listener
                        window.removeEventListener('scroll', handleScrollEnd, true);

                        // Trigger highlight animation (yellow border flash 3 times)
                        // Only highlight if Amazon selector is visible (has selectedCode)
                        if (selectedCode === 'attribute_value') {
                            highlightController.enable();

                            // Reset after animation completes
                            // Animation duration: 2400ms (6 flashes × 400ms each)
                            // Buffer: 100ms for safety
                            highlightTimer = setTimeout(() => {
                                highlightController.disable();
                            }, 2500);
                        }
                    }, 150);

                    // Store timer for cleanup
                    if (scrollEndTimer) {
                        scrollTimer = scrollEndTimer;
                    }
                };

                // Listen for scroll events on window with capture phase (to catch all scrolls)
                window.addEventListener('scroll', handleScrollEnd, true);

                // Fallback: if scroll doesn't trigger (e.g., element already in view),
                // trigger highlight after short delay
                const fallbackTimer = setTimeout(() => {
                    // console.log(`[Scroll/Highlight] ${attributeKey}: Fallback triggered (element may already be in view)`);
                    window.removeEventListener('scroll', handleScrollEnd, true);
                    handleScrollEnd();
                }, 100);

                scrollTimer = fallbackTimer;
            }
        } else {
            // console.log(`[Scroll/Highlight] ${attributeKey}: ❌ Conditions not met`, {
            //   isNull: filteredAllowedValues === null,
            //   hasChanged,
            //   isAffectedByUserChange
            // });
        }

        // Cleanup function to prevent memory leaks
        return () => {
            if (scrollTimer) clearTimeout(scrollTimer);
            if (highlightTimer) clearTimeout(highlightTimer);
        };
    }, [filteredAllowedValues, selectedCode, attributeKey, lastChangedAttribute, conditionalRules, highlightController]);

    // Normalize currentValue to handle auto-selection synchronously
    const normalizedCurrentValue = React.useMemo(() => {
        if (!currentValue?.Kind || !currentValue?.Values || !selectedCode) {
            return currentValue;
        }

        // Handle Kind="Matching" - convert string to AttributeValue object
        if (currentValue.Kind === 'Matching' && selectedCode === 'attribute_value') {
            if (typeof currentValue.Values === 'string') {
                return {
                    ...currentValue,
                    Values: {AttributeValue: currentValue.Values}
                };
            }
        }

        // Handle Kind="Freetext" - convert string to FreeText object
        if ((currentValue.Kind === 'Freetext' || currentValue.Kind === 'FreeText') && selectedCode === 'freetext') {
            if (typeof currentValue.Values === 'string') {
                return {
                    ...currentValue,
                    Values: {FreeText: currentValue.Values}
                };
            }
        }

        return currentValue;
    }, [currentValue, selectedCode]);

    // Persistence effect - save normalized values back to parent
    // Skip on initial mount to prevent unwanted save calls
    React.useEffect(() => {
        if (!hasMounted.current) {
            return;
        }

        // Only persist if we actually normalized the value and it's not undefined
        if (normalizedCurrentValue && normalizedCurrentValue !== currentValue && normalizedCurrentValue?.Values !== currentValue?.Values) {
            onAttributeChange(attributeKey, normalizedCurrentValue);
        }
    }, [normalizedCurrentValue, currentValue, attributeKey, onAttributeChange]);

    // Handle freetext value change
    const handleFreeTextChange = (value: string) => {
        onAttributeChange(attributeKey, {
            ...currentValue,
            Values: {FreeText: value}
        });
    };

    // Handle database value change (v2 compatibility - conditional on database_value Code)
    const handleDatabaseValueChange = (value: DatabaseValue) => {
        onAttributeChange(attributeKey, {
            ...currentValue,
            Values: value
        });
    };

    // Handle Amazon attribute value change
    const handleAmazonValueChange = (value: string) => {
        onAttributeChange(attributeKey, {
            ...currentValue,
            Values: {AttributeValue: value}
        });
    };

    // Handle value matchings change for select attributes
    const handleMatchingsChange = (matchings: MatchingValue[]) => {
        onAttributeChange(attributeKey, {
            ...currentValue,
            Values: matchings
        });
    };

    // Handle UseShopValues checkbox change
    const handleUseShopValuesChange = (useShopValues: boolean) => {
        onAttributeChange(attributeKey, {
            ...currentValue,
            UseShopValues: useShopValues,
            // When checked, clear matchings array; when unchecked, keep current matchings
            Values: useShopValues ? [] : (currentValue?.Values || [])
        });
    };

    // Find the selected shop attribute to determine its type
    const selectedShopAttribute = React.useMemo((): ShopAttribute | null => {
        if (!selectedCode) {
            return null;
        }

        for (const [groupName, group] of Object.entries(shopAttributes)) {
            if (typeof group === 'object' && group.optGroupClass) {
                for (const [key, attr] of Object.entries(group)) {
                    if (key === selectedCode && typeof attr === 'object') {
                        const shopAttr = attr as ShopAttribute;
                        return shopAttr;
                    }
                }
            }
        }

        return null;
    }, [selectedCode, shopAttributes]);

    // Check if we should show the matching table
    // Only show matching table when:
    // 1. Selected shop attribute is type "select" (has values to match)
    // 2. Amazon attribute has predefined values OR is type "text" (allows shop values)
    const amazonDataType = filteredAttribute.dataType?.toLowerCase() || '';
    const shouldShowMatchingTable = selectedShopAttribute?.type === 'select' &&
        (amazonDataType === 'text' ||
            (filteredAttribute.values && Object.keys(filteredAttribute.values).length > 0));


    // Get current matchings for the table
    const currentMatchings = React.useMemo(() => {
        if (!shouldShowMatchingTable) {
            return [];
        }

        const values = normalizedCurrentValue?.Values;

        // If Values is already an array, use it directly
        if (Array.isArray(values)) {
            return values as MatchingValue[];
        }

        // If Values is an object with numeric keys (backend format), convert to array
        if (values && typeof values === 'object' && !Array.isArray(values)) {
            const keys = Object.keys(values);
            // Check if keys are numeric (e.g., "1", "2", "3")
            if (keys.length > 0 && keys.every(k => !isNaN(Number(k)))) {
                return keys
                    .sort((a, b) => Number(a) - Number(b))
                    .map(key => (values as any)[key] as MatchingValue);
            }
        }

        return [];
    }, [shouldShowMatchingTable, normalizedCurrentValue?.Values]);

    // Get UseShopValues state - convert string "0"/"1" to boolean
    const useShopValues = React.useMemo(() => {
        const value = normalizedCurrentValue?.UseShopValues as string | number | boolean | undefined;

        // If it's a string "1" or "0", convert to boolean
        if (value === "1" || value === 1) return true;
        if (value === "0" || value === 0) return false;

        // If it's already boolean, use it
        if (typeof value === 'boolean') return value;

        // Default to false
        return false;
    }, [normalizedCurrentValue?.UseShopValues]);

    // Generate conditional rules help text
    const conditionalRulesHelpHtml = React.useMemo(() => {
        if (debugMode) {
            console.log('[conditionalRules]:', conditionalRules);
        }
        return generateConditionalRulesHelpText(
            attributeKey,
            conditionalRules,
            allMarketplaceAttributes,
            i18n
        );
    }, [attributeKey, conditionalRules, allMarketplaceAttributes, i18n]);

    // Initialize click handlers for conditional rule links after render
    React.useEffect(() => {
        if (debugMode) {
            console.log('[conditionalRulesHelpHtml]:', conditionalRulesHelpHtml);
        }
        if (conditionalRulesHelpHtml) {
            // Wait for DOM to update, then initialize links
            const timer = setTimeout(() => {
                initializeConditionalRuleLinks();
            }, 100);

            return () => clearTimeout(timer);
        }
    }, [conditionalRulesHelpHtml]);

    // Apply styles with !important to description box to override PrestaShop CSS
    React.useEffect(() => {
        if (descriptionBoxRef.current && attribute.desc) {
            applyStyleWithImportant(descriptionBoxRef.current, DESCRIPTION_BOX_STYLES.container);
        }
    });

    return (
        <tr
            ref={rowRef}
            id={`attr-row-${attributeKey}`}
            data-attribute-key={attributeKey}
            className={`js-field attribute-row ${isRequired ? 'required-attribute' : 'optional-attribute'}`}
        >
            {/* Attribute Name Column */}
            <th className="attribute-name-column">
                <label htmlFor={`${marketplaceName.toLowerCase()}_${variationGroup}_${attributeKey}`}>
                    <p className="attribute-label">
                        {attribute.value || attributeKey}

                        {isRequired && (
                            <span
                                className="required-indicator"
                                aria-label="Required field"
                            />
                        )}
                    </p>
                </label>
            </th>

            {/* Help/Remove Column (conditional - hidden when hideHelpColumn=true) */}
            {!hideHelpColumn && (
                <td className="mlhelp ml-js-noBlockUi help-column">
                    {/* Minus button moved to input column */}
                </td>
            )}

            {/* Input Column */}
            <td className="input attribute-input-column">
                {/* Shop Attribute Selector with optional remove button */}
                <div className="shop-attribute-container">
                    <AttributeSelector
                        attributeKey={attributeKey}
                        variationGroup={variationGroup}
                        marketplaceName={marketplaceName}
                        shopAttributes={shopAttributes}
                        selectedCode={selectedCode}
                        dataType={attribute.dataType}
                        hasAmazonValues={attribute.values && Object.keys(attribute.values).length > 0}
                        i18n={i18n}
                        disabled={disabled}
                        debugMode={debugMode}
                        hasError={Boolean(error)}
                        onChange={handleShopAttributeChange}
                    />
                    {!isRequired && onRemoveOptionalAttribute && (
                        <button
                            type="button"
                            onClick={() => onRemoveOptionalAttribute(attributeKey)}
                            disabled={disabled}
                            className="mlbtn action remove-matching-row"
                            title={i18n.removeOptionalAttribute || 'Remove optional attribute'}
                        >
                            -
                        </button>
                    )}
                </div>

                {/* Conditional Value Matching Interfaces */}
                {selectedCode === 'freetext' && (
                    <FreeTextInput
                        value={
                            typeof normalizedCurrentValue?.Values === 'string'
                                ? normalizedCurrentValue.Values
                                : (normalizedCurrentValue?.Values as any)?.FreeText || ''
                        }
                        onChange={handleFreeTextChange}
                        disabled={disabled}
                        i18n={i18n}
                        className="attribute-freetext-input"
                    />
                )}

                {selectedCode === 'database_value' && (
                    <DatabaseValueInput
                        value={
                            typeof normalizedCurrentValue?.Values === 'object' && normalizedCurrentValue?.Values !== null && !Array.isArray(normalizedCurrentValue.Values)
                                ? (normalizedCurrentValue.Values as DatabaseValue)
                                : {}
                        }
                        onChange={handleDatabaseValueChange}
                        disabled={disabled}
                        i18n={i18n}
                        databaseTables={databaseTables}
                        className="attribute-database-input"
                    />
                )}

                {selectedCode === 'attribute_value' && (
                    <AmazonValueSelector
                        attribute={filteredAttribute}
                        value={
                            typeof normalizedCurrentValue?.Values === 'string'
                                ? normalizedCurrentValue.Values
                                : (normalizedCurrentValue?.Values as any)?.AttributeValue || ''
                        }
                        onChange={handleAmazonValueChange}
                        disabled={disabled}
                        debugMode={debugMode}
                        i18n={i18n}
                        className="attribute-amazon-value"
                        shouldHighlight={shouldHighlight}
                    />
                )}

                {shouldShowMatchingTable && selectedShopAttribute && selectedCode && (
                    <ValueMatchingTable
                        attributeKey={attributeKey}
                        amazonAttribute={filteredAttribute}
                        shopAttribute={selectedShopAttribute}
                        shopAttributeCode={selectedCode}
                        variationGroup={variationGroup}
                        currentMatchings={currentMatchings}
                        disabled={disabled}
                        debugMode={debugMode}
                        useShopValues={useShopValues}
                        i18n={i18n}
                        onMatchingsChange={handleMatchingsChange}
                        onUseShopValuesChange={handleUseShopValuesChange}
                        onFetchShopAttributeValues={onFetchShopAttributeValues}
                    />
                )}
            </td>

            {/* Info Column - Always rendered (not dependent on hideHelpColumn) */}
            <td className="info attribute-info-column">
                {attribute.desc && (
                    <div
                        ref={descriptionBoxRef}
                        className="attribute-description"
                        style={DESCRIPTION_BOX_STYLES.container}
                        dangerouslySetInnerHTML={createSafeHtml(attribute.desc)}
                    />
                )}
                {/* Conditional Rules Help Panel */}
                {conditionalRulesHelpHtml && (
                    <div
                        className="conditional-rules-help-container"
                        dangerouslySetInnerHTML={{__html: conditionalRulesHelpHtml}}
                    />
                )}
            </td>
        </tr>
    );
};

export default AttributeRow;