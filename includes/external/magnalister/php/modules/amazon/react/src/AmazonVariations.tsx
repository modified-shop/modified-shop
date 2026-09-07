import React from 'react';
import {
    AmazonVariationsProps,
    ConditionalRule,
    I18nStrings,
    MarketplaceAttributes,
    SavedAttributeValue,
    SavedValues,
    ShopAttributes,
    ValidationError
} from './types';
import AttributeRow from './components/AmazonVariations/AttributeRow';
import OptionalAttributeSelector from './components/AmazonVariations/OptionalAttributeSelector';
import CustomAttributeRow, {CustomAttributeValue} from './components/ebay/CustomAttributeRow';
import {createSafeHtml} from './components/AmazonVariations/utils/htmlSanitizer';
import {createShopAttributeValuesFetcher} from './utils/shopAttributeApi';
import {getVisibleAttributeKeys, hasParentChildAttributes} from './utils/parentChildVisibility';
import './components/AmazonVariations/styles.css';

// Context to track which attribute was last changed by user
const UserChangeContext = React.createContext<{ lastChangedAttribute: string | null }>({
    lastChangedAttribute: null
});

// Stable default prop values. Inline defaults ({} / []) create a NEW object on every
// render; several hooks list these props as dependencies (validateAttributes → i18n,
// the savedValues reset effect, fetchShopAttributeValues → neededFormFields), so an
// unstable default re-runs those effects on every render — setValidationErrors then
// re-renders and the component loops ("Maximum update depth exceeded") whenever the
// host omits one of these props.
const EMPTY_SHOP_ATTRIBUTES: ShopAttributes = {};
const EMPTY_MARKETPLACE_ATTRIBUTES: MarketplaceAttributes = {};
const EMPTY_SAVED_VALUES: SavedValues = {};
const EMPTY_CONDITIONAL_RULES: ConditionalRule[] = [];
const EMPTY_FORM_FIELDS: { [key: string]: string } = {};
const EMPTY_I18N: I18nStrings = {};

// Counter for auto-generated save-registry keys (instances without apiNamespace)
let mlSaveInstanceCounter = 0;

// Per-instance external save functions, shared across every mounted AmazonVariations
// instance on the page. The unscoped window globals (magnalisterSaveVariations etc.)
// flush EVERY registered instance, so a page with multiple instances (Temu: variation +
// category + category-independent) no longer loses the pending changes of all but the
// last-mounted one. Kept on window (not module scope) so it survives a second bundle load.
type InstanceSaveFn = () => Promise<'ok' | 'failed'>;

function getSaveRegistry(): Record<string, InstanceSaveFn> {
    const w = window as any;
    if (!w.__mlVariationsSaveRegistry) {
        w.__mlVariationsSaveRegistry = {};
    }
    return w.__mlVariationsSaveRegistry;
}

/**
 * Amazon Variations Component
 *
 * Main component that orchestrates attribute matching functionality
 * Uses modular sub-components for better organization and reusability
 */
const AmazonVariations: React.FC<AmazonVariationsProps> = ({
                                                               variationGroup,
                                                               customIdentifier,
                                                               variationTheme,
                                                               marketplaceName = 'Amazon',
                                                               shopAttributes: rawShopAttributes = EMPTY_SHOP_ATTRIBUTES,
                                                               marketplaceAttributes: rawMarketplaceAttributes = EMPTY_MARKETPLACE_ATTRIBUTES,
                                                               savedValues: rawSavedValues = EMPTY_SAVED_VALUES,
                                                               conditionalRules = EMPTY_CONDITIONAL_RULES,
                                                               neededFormFields = EMPTY_FORM_FIELDS,
                                                               i18n = EMPTY_I18N,
                                                               databaseTables,
                                                               onValuesChange,
                                                               onValidationError,
                                                               className,
                                                               disabled = false,
                                                               onFetchShopAttributeValues,
                                                               apiEndpoint,
                                                               apiNamespace,
                                                               strictSave = false,
                                                               debugMode = false,
                                                               wrapInTable = true,
                                                               hideHelpColumn = false,
                                                               enableCustomAttributes = false
                                                           }) => {
    // Normalize props that might be null from PHP (default params don't work with explicit
    // null). Use the stable constants — a fresh {} here changes identity every render and
    // re-triggers every effect depending on these props (see EMPTY_* comment above).
    const savedValues = rawSavedValues ?? EMPTY_SAVED_VALUES;
    const shopAttributes = rawShopAttributes ?? EMPTY_SHOP_ATTRIBUTES;
    const marketplaceAttributes = rawMarketplaceAttributes ?? EMPTY_MARKETPLACE_ATTRIBUTES;

    // Create the fetch function if apiEndpoint is provided but onFetchShopAttributeValues is not
    const fetchShopAttributeValues = React.useMemo(() => {
        if (onFetchShopAttributeValues) {
            return onFetchShopAttributeValues;
        } else if (apiEndpoint && variationGroup) {
            return createShopAttributeValuesFetcher(apiEndpoint, variationGroup, neededFormFields);
        }
        return undefined;
    }, [onFetchShopAttributeValues, apiEndpoint, variationGroup, neededFormFields]);

    // Initialize attribute values with defaults for selectAndText types
    const initializeAttributeValues = React.useCallback((savedVals: SavedValues | null | undefined): SavedValues => {
        // Handle null/undefined savedVals (can happen when PHP passes null)
        const normalizedSavedVals = savedVals ?? {};
        const initialized: SavedValues = {...normalizedSavedVals};

        // For each marketplace attribute, set defaults if not already saved
        Object.entries(marketplaceAttributes).forEach(([key, attribute]) => {
            const dataType = attribute.dataType?.toLowerCase() || '';
            const isTextType = dataType.includes('text');

            if (!initialized[key]) {
                // New attribute - initialize with defaults

                // Only auto-initialize for:
                // 1. Required attributes (always shown)
                // 2. Optional attributes that have been added (exist in savedVals)
                // Skip optional attributes that haven't been added yet
                const isRequired = attribute.required === true;
                const hasSavedData = key in normalizedSavedVals;

                if (!isRequired && !hasSavedData) {
                    // Skip initialization for optional attributes that haven't been added yet
                    return;
                }

                // Auto-select single Amazon value if available
                if (attribute.values) {
                    const amazonValueKeys = Object.keys(attribute.values);
                    if (amazonValueKeys.length === 1) {
                        // Only one Amazon value available - auto-select it
                        initialized[key] = {
                            Code: 'attribute_value',
                            Values: {
                                AttributeValue: amazonValueKeys[0]
                            }
                        };
                        return; // Skip other defaults for this attribute
                    }
                }

                // Auto-select "0" for list_price__value_with_tax
                if (key === 'list_price__value_with_tax') {
                    initialized[key] = {
                        Code: 'freetext',
                        Values: {
                            FreeText: '0'
                        }
                    };
                    return; // Skip other defaults for this attribute
                }

                if (isTextType) {
                    initialized[key] = {
                        Code: '',
                        UseShopValues: true,
                        Values: []
                    };
                }
            } else if (isTextType && initialized[key].UseShopValues === undefined) {
                // Existing attribute but missing UseShopValues - add it with default true
                // This handles old saved data that doesn't have UseShopValues field
                initialized[key] = {
                    ...initialized[key],
                    UseShopValues: true
                };
            }
        });

        return initialized;
    }, [marketplaceAttributes]);

    // State management
    const [attributeValues, setAttributeValues] = React.useState<SavedValues>(() =>
        initializeAttributeValues(savedValues)
    );
    const [validationErrors, setValidationErrors] = React.useState<ValidationError[]>([]);

    // Track which optional attributes are currently visible/active (in order of addition)
    const [activeOptionalAttributes, setActiveOptionalAttributes] = React.useState<string[]>(() => {
        // Initialize with optional attributes that have saved values (excluding custom
        // attributes and child attributes — children are always rendered in the required
        // section grouped under their parent, so seeding them here would duplicate them).
        const savedOptionalKeys = Object.keys(savedValues ?? {}).filter(key => {
            const attribute = marketplaceAttributes[key];
            return attribute && !attribute.required && !attribute.custom
                && (attribute as any).parentRefPid === undefined;
        });
        return savedOptionalKeys;
    });

    // State for custom attributes (eBay-specific)
    // Map from attribute key (e.g., "additional_attribute_0") to CustomAttributeValue
    const [customAttributeValues, setCustomAttributeValues] = React.useState<Record<string, CustomAttributeValue>>(() => {
        // Initialize from saved values for custom attributes
        const customValues: Record<string, CustomAttributeValue> = {};
        Object.entries(savedValues ?? {}).forEach(([key, value]) => {
            const attribute = marketplaceAttributes[key];
            if (attribute && attribute.custom) {
                // For custom attributes:
                // - attributeCode = CustomAttributeNameCode (NAME shop attribute)
                // - valueCode = Code (VALUE shop attribute)
                // - matchings = Values if it's an array
                const matchings = Array.isArray(value.Values) ? value.Values : undefined;

                // Convert UseShopValues from string/number to boolean
                const useShopValuesRaw = (value as any).UseShopValues;
                const useShopValues = useShopValuesRaw === "1" || useShopValuesRaw === 1 || useShopValuesRaw === true;

                customValues[key] = {
                    attributeCode: (value as any).CustomAttributeNameCode || '',
                    // Use CustomAttributeName if available, otherwise fallback to AttributeName
                    customName: (value as any).CustomAttributeName || (value as any).AttributeName || '',
                    valueCode: value.Code || '',
                    customValue: (value.Values && typeof value.Values === 'string') ? value.Values : '',
                    matchings: matchings,
                    useShopValues: useShopValuesRaw !== undefined ? useShopValues : true, // Default to true
                    dateFormat: value.DateFormat || undefined
                };
            }
        });
        return customValues;
    });

    // Track dynamically added custom attribute slots (beyond what backend provides)
    // This allows users to add multiple custom attributes without page reload
    const [dynamicCustomAttributeSlots, setDynamicCustomAttributeSlots] = React.useState<string[]>([]);

    // Helper function to get the next custom attribute index
    const getNextCustomAttributeIndex = React.useCallback((): number => {
        // Get all existing custom attribute keys (from backend + dynamic)
        const allKeys = [
            ...Object.keys(marketplaceAttributes).filter(k => k.startsWith('additional_attribute_')),
            ...dynamicCustomAttributeSlots
        ];

        let maxIndex = -1;
        allKeys.forEach(key => {
            const match = key.match(/additional_attribute_(\d+)/);
            if (match) {
                const index = parseInt(match[1], 10);
                if (index > maxIndex) {
                    maxIndex = index;
                }
            }
        });

        return maxIndex + 1;
    }, [marketplaceAttributes, dynamicCustomAttributeSlots]);

    // Handle custom attribute change
    const handleCustomAttributeChange = React.useCallback((rowId: string, value: CustomAttributeValue) => {
        setCustomAttributeValues(prev => {
            const newValues = {
                ...prev,
                [rowId]: value
            };

            // Check if we need to add a new empty slot
            // Add new slot when current attribute has both attributeCode and valueCode set
            if (value.attributeCode && value.valueCode) {
                // Get all custom attribute keys
                const backendCustomKeys = Object.keys(marketplaceAttributes).filter(k =>
                    marketplaceAttributes[k]?.custom === true
                );
                const allCustomKeys = [...backendCustomKeys, ...dynamicCustomAttributeSlots];

                // Check if there's at least one empty slot available
                const hasEmptySlot = allCustomKeys.some(key => {
                    const val = newValues[key];
                    return !val || !val.attributeCode || !val.valueCode;
                });

                // If no empty slot, add a new one
                if (!hasEmptySlot) {
                    const nextIndex = getNextCustomAttributeIndex();
                    const newSlotKey = `additional_attribute_${nextIndex}`;

                    if (debugMode) {
                        console.log(`[AmazonVariations] 📦 Adding new custom attribute slot: ${newSlotKey}`);
                    }

                    setDynamicCustomAttributeSlots(prev => [...prev, newSlotKey]);
                }
            }

            return newValues;
        });

        // Check if this custom attribute should be saved
        // Don't save if:
        // 1. attributeCode is empty (nothing selected on LEFT)
        // 2. attributeCode is 'freetext' but customName is empty
        const shouldSave = value.attributeCode &&
            (value.attributeCode !== 'freetext' || (value.customName && value.customName.trim() !== ''));

        if (!shouldSave) {
            // Remove from pending changes if it was previously added
            if (pendingChangesRef.current[rowId]) {
                delete pendingChangesRef.current[rowId];
            }

            if (debugMode) {
                console.log(`[AmazonVariations] ⏭️ Custom attribute not saved (incomplete):`, rowId, value);
            }
            return;
        }

        // Convert to SavedAttributeValue format and add to pending changes
        const savedValue: SavedAttributeValue = {
            Code: value.valueCode, // VALUE shop attribute code
            Values: value.matchings && value.matchings.length > 0
                ? value.matchings  // Use matchings if available (type 'select')
                : (value.valueCode === 'freetext' ? value.customValue : ''),
            Kind: value.matchings && value.matchings.length > 0 ? 'Matching' : 'FreeText',
            UseShopValues: value.useShopValues ?? true
        };

        // Add DateFormat if set (for date-type metafield attributes)
        if (value.dateFormat) {
            savedValue.DateFormat = value.dateFormat;
        }

        // Add NAME attribute info
        // CustomAttributeNameCode: 'freetext' (indicator) or shop attribute code
        // AttributeName: actual name (custom text or resolved from shop attribute)
        (savedValue as any).CustomAttributeNameCode = value.attributeCode;

        if (value.attributeCode === 'freetext' && value.customName) {
            // Freetext: use the custom name entered by user
            (savedValue as any).AttributeName = value.customName;
        } else if (value.attributeCode && value.attributeCode !== 'freetext') {
            // Shop attribute: resolve the attribute name from shopAttributes
            let attributeName = value.attributeCode;
            for (const [, group] of Object.entries(shopAttributes)) {
                if (typeof group === 'object' && group.optGroupClass) {
                    for (const [attrKey, attr] of Object.entries(group)) {
                        if (attrKey === value.attributeCode && typeof attr === 'object' && (attr as any).name) {
                            attributeName = (attr as any).name;
                            break;
                        }
                    }
                }
            }
            (savedValue as any).AttributeName = attributeName;
        }

        // Update attribute values
        setAttributeValues(prev => ({
            ...prev,
            [rowId]: savedValue
        }));

        // Add to pending changes for auto-save
        pendingChangesRef.current[rowId] = {
            value: savedValue,
            actionType: 'save'
        };

        if (debugMode) {
            console.log(`[AmazonVariations] 📝 Custom attribute changed:`, rowId, value);
        }
    }, [debugMode, marketplaceAttributes, dynamicCustomAttributeSlots, getNextCustomAttributeIndex, shopAttributes]);

    // Handle custom attribute removal
    const handleRemoveCustomAttribute = React.useCallback((rowId: string) => {
        setCustomAttributeValues(prev => {
            const newValues = {...prev};
            delete newValues[rowId];
            return newValues;
        });

        // Remove from attribute values
        setAttributeValues(prev => {
            const newValues = {...prev};
            delete newValues[rowId];
            return newValues;
        });

        // Remove from dynamic slots if it's a dynamic one
        setDynamicCustomAttributeSlots(prev => prev.filter(key => key !== rowId));

        // Add to pending changes as delete
        pendingChangesRef.current[rowId] = {
            value: {Code: '', Values: undefined},
            actionType: 'delete'
        };

        if (debugMode) {
            console.log(`[AmazonVariations] 🗑️ Custom attribute removed:`, rowId);
        }
    }, [debugMode]);

    // Update state when savedValues change
    React.useEffect(() => {
        setAttributeValues(initializeAttributeValues(savedValues));
    }, [savedValues, initializeAttributeValues]);

    // Notify parent of changes
    React.useEffect(() => {
        onValuesChange?.(attributeValues);
    }, [attributeValues, onValuesChange]);

    // Batch changes: collect all changes and save every 5 seconds
    const pendingChangesRef = React.useRef<Record<string, {
        value: SavedAttributeValue;
        actionType: 'save' | 'delete';
    }>>({});

    // Queue for pending save operations
    const saveQueueRef = React.useRef<Array<{
        attributeKey: string;
        value: SavedAttributeValue;
        actionType: 'save' | 'delete';
    }>>([]);

    // Track if we're currently processing the queue
    const isProcessingQueueRef = React.useRef<boolean>(false);

    // Track save success message
    const [showSaveSuccess, setShowSaveSuccess] = React.useState(false);

    // Track save error message (strictSave only)
    const [showSaveError, setShowSaveError] = React.useState(false);

    // Attribute keys whose save failed while draining the queue (collected per flush)
    const queueFailuresRef = React.useRef<string[]>([]);

    // True while a batch flush request (hierarchy mode) is in flight
    const batchFlushInProgressRef = React.useRef<boolean>(false);

    // Whether the most recent flush ended with failures (read by strict empty-pending waits)
    const lastFlushHadFailuresRef = React.useRef<boolean>(false);

    // Track if initial save has been done
    const initialSaveDoneRef = React.useRef(false);

    // Attributes with a parent/child hierarchy (Temu) are flushed as ONE batch request so
    // the server-side stale-child prune always sees parent and child together
    const hasHierarchy = React.useMemo(
        () => hasParentChildAttributes(marketplaceAttributes),
        [marketplaceAttributes]
    );

    /**
     * Convert React format to backend expected format
     * - Convert Values array to object with numeric keys ("1", "2", "3")
     * - Remove __id from each row
     * - Convert UseShopValues boolean to string "0" or "1"
     * - Add backend-specific fields (Kind, Required, DataType, AttributeName)
     * - Add Info field to Marketplace values
     */
    const convertToBackendFormat = React.useCallback((attributeKey: string, value: SavedAttributeValue): any => {
        const converted: any = {...value};
        const attribute = marketplaceAttributes[attributeKey];

        // Add backend-specific fields from marketplaceAttributes
        if (attribute) {
            // Determine Kind based on attribute type
            const dataType = attribute.dataType?.toLowerCase() || '';
            converted.Kind = dataType.includes('text') ? 'FreeText' : 'Matching';

            // Add other backend fields
            converted.Required = attribute.required || false;
            converted.DataType = attribute.dataType || 'text';

            // For custom attributes, preserve the user-provided AttributeName
            // (set by handleCustomAttributeChange from textbox or shop attribute name)
            // Only set from marketplace attribute for non-custom attributes
            if (!attribute.custom && !converted.AttributeName) {
                converted.AttributeName = attribute.value || attributeKey;
            } else if (!converted.AttributeName) {
                // Fallback for custom attributes without explicit name
                converted.AttributeName = attributeKey;
            }
        }

        // Handle Values based on Code type
        if (converted.Code === 'attribute_value' || converted.Code === 'freetext') {
            // Remove UseShopValues for freetext and attribute_value (no checkbox displayed)
            delete converted.UseShopValues;
            // For attribute_value and freetext, extract the simple string value
            if (converted.Values && typeof converted.Values === 'object' && !Array.isArray(converted.Values)) {
                // If Values is object like {AttributeValue: "as3"} or {FreeText: "0"}, extract the value
                converted.Values = converted.Values.AttributeValue || converted.Values.FreeText || '';
            } else if (converted.AttributeValue) {
                // Or use AttributeValue field if it exists
                converted.Values = converted.AttributeValue;
            } else if (converted.FreeTextValue) {
                // Or use FreeTextValue field if it exists
                converted.Values = converted.FreeTextValue;
            }
            // If Values is already a string, keep it as is
        } else if (Array.isArray(converted.Values)) {
            // Convert Values array to object with numeric keys (for matching tables)
            // Convert UseShopValues boolean to string "0" or "1" (only for matching tables)
            if (typeof converted.UseShopValues === 'boolean') {
                converted.UseShopValues = converted.UseShopValues ? "1" : "0";
            }

            const valuesObject: any = {};
            converted.Values.forEach((row: any, index: number) => {
                // Create a copy without __id
                const {__id, ...rowWithoutId} = row;

                // Handle custom text entries: replace "__custom__" key with actual value
                if (rowWithoutId.Marketplace && rowWithoutId.Marketplace.Key === '__custom__') {
                    const customValue = rowWithoutId.Marketplace.Value || '';
                    if (customValue) {
                        rowWithoutId.Marketplace.Key = customValue;
                    }
                }

                // Add Info field to Marketplace if it doesn't exist
                if (rowWithoutId.Marketplace && !rowWithoutId.Marketplace.Info) {
                    const marketplaceKey = rowWithoutId.Marketplace.Key || '';
                    const marketplaceValue = rowWithoutId.Marketplace.Value || '';

                    // Generate Info text (e.g., "Test - (manuell zugeordnet)")
                    if (marketplaceKey && marketplaceValue) {
                        rowWithoutId.Marketplace.Info = `${marketplaceValue} - (manuell zugeordnet)`;
                    }
                }

                valuesObject[String(index + 1)] = rowWithoutId;
            });
            converted.Values = valuesObject;
        }

        return converted;
    }, [marketplaceAttributes]);

    // Batch save function: saves all attributes in a single AJAX request.
    // Returns true on success, false when every attempt failed.
    const saveAllAttributesBatch = React.useCallback(async (
        attributesToSave: Record<string, SavedAttributeValue>,
        silent: boolean = false,
        maxRetries: number = 1
    ): Promise<boolean> => {
        if (!apiEndpoint || !variationGroup) {
            if (debugMode) {
                console.warn('[AmazonVariations] 💾 Cannot save - missing apiEndpoint or variationGroup');
            }
            return false;
        }

        const retryDelay = 1000; // 1 second between attempts

        for (let attempt = 1; attempt <= maxRetries; attempt++) {
            try {
                if (debugMode) {
                    console.log(`[AmazonVariations] 💾 Batch saving attributes (attempt ${attempt}/${maxRetries}):`, Object.keys(attributesToSave));
                }

                // Convert all attributes to backend format
                const convertedAttributes: Record<string, any> = {};
                Object.entries(attributesToSave).forEach(([key, value]) => {
                    convertedAttributes[key] = convertToBackendFormat(key, value);
                });

                // Create FormData to send as POST fields
                const params = new URLSearchParams();
                params.append('ml[action]', 'saveAttributeMatchingBatch');
                params.append('ml[variationGroup]', variationGroup);
                params.append('ml[attributesData]', JSON.stringify(convertedAttributes));
                if (customIdentifier) {
                    params.append('ml[customIdentifier]', customIdentifier);
                }
                if (variationTheme) {
                    params.append('ml[variationTheme]', variationTheme);
                }
                // Add platform-specific form fields
                if (neededFormFields) {
                    Object.entries(neededFormFields).forEach(([key, value]) => {
                        params.append(key, value);
                    });
                }

                const response = await fetch(apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: params.toString()
                });

                const result = await response.json();

                // A form-render fallthrough response (PHP controller without the batch
                // endpoint) also answers {"success":true,...} but carries a `plugin`
                // payload that real react endpoints never send — treat it as a failure,
                // the save was NOT persisted (BUG-018 finding on the Attributes
                // Matching tab).
                if (!result.success || result.plugin !== undefined) {
                    throw new Error(result.message || 'Failed to batch save attributes');
                }

                if (debugMode) {
                    console.log('[AmazonVariations] ✅ Batch save successful');
                }

                // Show success message unless the caller owns the messaging
                if (!silent) {
                    setShowSaveSuccess(true);
                    setTimeout(() => {
                        setShowSaveSuccess(false);
                    }, 5000); // 5 seconds
                }
                return true;
            } catch (error) {
                if (attempt === maxRetries) {
                    console.error('[AmazonVariations] ❌ Batch save failed:', error);
                } else {
                    console.warn(`[AmazonVariations] ⚠️ Batch save attempt ${attempt}/${maxRetries} failed, retrying in ${retryDelay}ms...`);
                    await new Promise(resolve => setTimeout(resolve, retryDelay));
                }
            }
        }
        return false;
    }, [apiEndpoint, variationGroup, customIdentifier, variationTheme, neededFormFields, debugMode, convertToBackendFormat]);

    // Internal save function that does the actual AJAX request with retry logic.
    // Returns true on success, false when every attempt failed.
    const saveAttributeMatchingInternal = React.useCallback(async (
        attributeKey: string,
        value: SavedAttributeValue,
        actionType: 'save' | 'delete' = 'save'
    ): Promise<boolean> => {
        if (!apiEndpoint || !variationGroup) {
            if (debugMode) {
                console.warn('[AmazonVariations] 💾 Cannot save - missing apiEndpoint or variationGroup');
            }
            return false;
        }

        // Retry logic: 3 attempts with 1 second delay between attempts
        const maxRetries = 3;
        const retryDelay = 1000; // 1 second

        for (let attempt = 1; attempt <= maxRetries; attempt++) {
            try {
                if (debugMode) {
                    console.log(`[AmazonVariations] 💾 ${actionType === 'delete' ? 'Deleting' : 'Saving'} attribute matching (attempt ${attempt}/${maxRetries}):`, {
                        attributeKey,
                        value,
                        variationGroup,
                        actionType
                    });
                }

                // Create FormData to send as POST fields instead of JSON
                const params = new URLSearchParams();
                params.append('ml[action]', 'saveAttributeMatching');
                params.append('ml[attributeKey]', attributeKey);
                params.append('ml[variationGroup]', variationGroup);
                params.append('ml[actionType]', actionType); // 'save' or 'delete'
                if (customIdentifier) {
                    params.append('ml[customIdentifier]', customIdentifier);
                }
                // Send variationTheme if available (e.g., "SIZE/COLOR")
                if (variationTheme) {
                    params.append('ml[variationTheme]', variationTheme);
                }
                // Add platform-specific form fields (e.g., Magento form_key)
                if (neededFormFields) {
                    Object.entries(neededFormFields).forEach(([key, value]) => {
                        params.append(key, value);
                    });
                }
                // Only send attributeData for 'save' action
                if (actionType === 'save') {
                    // Convert to backend format before sending
                    const convertedValue = convertToBackendFormat(attributeKey, value);
                    params.append('ml[attributeData]', JSON.stringify(convertedValue));
                }

                const response = await fetch(apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: params.toString() // Send as FormData for $_POST access
                });

                const result = await response.json();

                // Guard against form-render fallthrough responses (see batch save above)
                if (!result.success || result.plugin !== undefined) {
                    throw new Error(result.message || 'Failed to save attribute matching');
                }

                // Success - return immediately
                if (debugMode) {
                    if (attempt > 1) {
                        console.log(`[AmazonVariations] ✅ Attribute matching ${actionType === 'delete' ? 'deleted' : 'saved'} successfully on attempt ${attempt}`);
                    } else {
                        console.log(`[AmazonVariations] ✅ Attribute matching ${actionType === 'delete' ? 'deleted' : 'saved'} successfully`);
                    }
                }
                return true; // Exit successfully
            } catch (error) {
                const isLastAttempt = attempt === maxRetries;

                if (isLastAttempt) {
                    // Final attempt failed - log error and continue
                    console.error(`[AmazonVariations] ❌ All ${maxRetries} attempts failed to save attribute matching for ${attributeKey}:`, error);
                    // Continue processing queue even if one item fails
                } else {
                    // Not the last attempt - log warning and retry after delay
                    console.warn(`[AmazonVariations] ⚠️ Attempt ${attempt}/${maxRetries} failed for ${attributeKey}, retrying in ${retryDelay}ms...`);
                    await new Promise(resolve => setTimeout(resolve, retryDelay));
                }
            }
        }
        return false;
    }, [apiEndpoint, variationGroup, customIdentifier, variationTheme, neededFormFields, debugMode, convertToBackendFormat]);

    // Process the save queue one at a time (serialize saves)
    const processSaveQueue = React.useCallback(async () => {
        // If already processing or queue is empty, return
        if (isProcessingQueueRef.current || saveQueueRef.current.length === 0) {
            return;
        }

        // Mark as processing to prevent concurrent execution
        isProcessingQueueRef.current = true;

        try {
            // Process all items in queue, one by one
            while (saveQueueRef.current.length > 0) {
                const item = saveQueueRef.current.shift();
                if (!item) continue;

                if (debugMode) {
                    console.log(`[AmazonVariations] 💾 Processing queue item (${saveQueueRef.current.length} remaining):`, item.attributeKey);
                }

                // Call the actual save function (awaits completion before next item)
                const succeeded = await saveAttributeMatchingInternal(item.attributeKey, item.value, item.actionType);
                if (!succeeded) {
                    queueFailuresRef.current.push(item.attributeKey);
                }
            }
        } finally {
            // Mark as not processing
            isProcessingQueueRef.current = false;
        }
    }, [saveAttributeMatchingInternal, debugMode]);

    // Public save function that adds to queue
    const saveAttributeMatching = React.useCallback((
        attributeKey: string,
        value: SavedAttributeValue,
        actionType: 'save' | 'delete' = 'save'
    ) => {
        // Add to queue
        saveQueueRef.current.push({attributeKey, value, actionType});

        if (debugMode) {
            console.log(`[AmazonVariations] 📋 Added to queue (${saveQueueRef.current.length} items):`, attributeKey);
        }

        // Start processing queue
        processSaveQueue();
    }, [processSaveQueue, debugMode]);

    // Wait until the serialized save queue and any batch flush are done (bounded)
    const waitForSavesToSettle = React.useCallback(async () => {
        const maxWaitTime = 30000; // 30 seconds max
        const startTime = Date.now();

        while (isProcessingQueueRef.current || saveQueueRef.current.length > 0 || batchFlushInProgressRef.current) {
            if (Date.now() - startTime > maxWaitTime) {
                console.error('[AmazonVariations] Timeout waiting for save queue to finish');
                break;
            }
            await new Promise(resolve => setTimeout(resolve, 100));
        }
    }, []);

    // Process all pending changes and send to server.
    // Returns 'failed' only in strictSave mode when at least one change did not persist.
    const processPendingChanges = React.useCallback(async (): Promise<'ok' | 'failed'> => {
        const pendingKeys = Object.keys(pendingChangesRef.current);

        if (pendingKeys.length === 0) {
            if (!strictSave) {
                return 'ok'; // Nothing to save (legacy behavior: return immediately)
            }
            // Strict mode: a timer flush may still be in flight — wait for it so callers
            // (form submit) don't navigate away while save requests are pending
            await waitForSavesToSettle();
            return lastFlushHadFailuresRef.current ? 'failed' : 'ok';
        }

        if (debugMode) {
            console.log(`[AmazonVariations] 💾 Processing ${pendingKeys.length} pending changes`);
        }

        // Take ownership of the current pending set
        const changes = pendingChangesRef.current;
        pendingChangesRef.current = {};

        const failedKeys: string[] = [];
        const deletes = Object.entries(changes).filter(([, change]) => change.actionType === 'delete');
        const saves = Object.entries(changes).filter(([, change]) => change.actionType === 'save');

        if (hasHierarchy && saves.length > 0) {
            // Parent/child hierarchy (Temu): send every pending save in ONE batch request so
            // the server merges parent and child atomically before pruning stale children.
            // Sequential per-attribute saves let a failed parent save silently prune the
            // child inside the child's own request.
            batchFlushInProgressRef.current = true;
            try {
                const batch: Record<string, SavedAttributeValue> = {};
                saves.forEach(([key, change]) => {
                    batch[key] = change.value;
                });
                const succeeded = await saveAllAttributesBatch(batch, true, 3);
                if (!succeeded) {
                    saves.forEach(([key, change]) => {
                        failedKeys.push(key);
                        // Keep the change for the next flush unless the user edited it again
                        if (!pendingChangesRef.current[key]) {
                            pendingChangesRef.current[key] = change;
                        }
                    });
                }
            } finally {
                batchFlushInProgressRef.current = false;
            }
        } else {
            saves.forEach(([key, change]) => {
                saveAttributeMatching(key, change.value, 'save');
            });
        }

        // Deletes always go through the single-save endpoint — the batch endpoint
        // merges and cannot remove a key
        deletes.forEach(([key, change]) => {
            saveAttributeMatching(key, change.value, 'delete');
        });

        await waitForSavesToSettle();
        failedKeys.push(...queueFailuresRef.current.splice(0));

        lastFlushHadFailuresRef.current = failedKeys.length > 0;

        if (strictSave && failedKeys.length > 0) {
            console.error('[AmazonVariations] ❌ Save flush finished with failures:', failedKeys);
            setShowSaveError(true);
            setTimeout(() => {
                setShowSaveError(false);
            }, 8000);
            return 'failed';
        }

        // Show success message
        setShowSaveSuccess(true);

        // Hide success message after 5 seconds
        setTimeout(() => {
            setShowSaveSuccess(false);
        }, 5000); // 5 seconds
        return 'ok';
    }, [saveAttributeMatching, saveAllAttributesBatch, waitForSavesToSettle, hasHierarchy, strictSave, debugMode]);

    // Timer to process pending changes every 10 seconds
    React.useEffect(() => {
        const intervalId = setInterval(() => {
            processPendingChanges();
        }, 10000); // Every 10 seconds

        return () => {
            clearInterval(intervalId);
            // Process any remaining changes on unmount
            if (Object.keys(pendingChangesRef.current).length > 0) {
                processPendingChanges();
            }
        };
    }, [processPendingChanges]);

    // Track the last user-changed attribute (for scroll/highlight)
    const [lastChangedAttribute, setLastChangedAttribute] = React.useState<string | null>(null);

    // Handle attribute change - add to pending changes instead of immediate save
    const handleAttributeChange = React.useCallback((
        attributeKey: string,
        value: SavedAttributeValue
    ) => {
        // Track that THIS attribute was changed by user (for scroll/highlight)
        setLastChangedAttribute(attributeKey);

        // Update local state immediately for responsive UI
        setAttributeValues(prev => ({
            ...prev,
            [attributeKey]: value
        }));

        // Add to pending changes (will be processed every 5 seconds)
        pendingChangesRef.current[attributeKey] = {
            value,
            actionType: 'save'
        };

        if (debugMode) {
            console.log(`[AmazonVariations] 📝 User changed attribute:`, attributeKey,
                `(${Object.keys(pendingChangesRef.current).length} pending)`);
        }

        // Reset after short delay (allow affected fields to check it)
        setTimeout(() => {
            setLastChangedAttribute(null);
        }, 100);
    }, [debugMode]);

    // Handle adding a new optional attribute (deduplicated via functional updater)
    const handleAddOptionalAttribute = React.useCallback((attributeKey: string) => {
        setActiveOptionalAttributes(prev => {
            if (prev.includes(attributeKey)) return prev;
            return [...prev, attributeKey];
        });

        // Initialize with default values based on attribute type
        const attribute = marketplaceAttributes[attributeKey];
        const dataType = attribute?.dataType?.toLowerCase() || '';
        const isTextType = dataType.includes('text');

        setAttributeValues(prev => ({
            ...prev,
            [attributeKey]: {
                Code: '',
                UseShopValues: isTextType ? true : undefined,
                Values: isTextType ? [] : undefined
            }
        }));
    }, [marketplaceAttributes]);

    // Handle adding multiple optional attributes at once (for error message links with sibling attributes)
    const handleAddOptionalAttributes = React.useCallback((attributeKeys: string[]) => {
        setActiveOptionalAttributes(prev => {
            const existingSet = new Set(prev);
            const newKeys = attributeKeys.filter(key =>
                !existingSet.has(key) && marketplaceAttributes[key] && !marketplaceAttributes[key].required
            );
            if (newKeys.length === 0) return prev;
            return [...prev, ...newKeys];
        });

        setAttributeValues(prev => {
            const updates: SavedValues = {};
            attributeKeys.forEach(key => {
                if (!prev[key] && marketplaceAttributes[key] && !marketplaceAttributes[key].required) {
                    const attribute = marketplaceAttributes[key];
                    const dataType = attribute?.dataType?.toLowerCase() || '';
                    const isTextType = dataType.includes('text');
                    updates[key] = {
                        Code: '',
                        UseShopValues: isTextType ? true : undefined,
                        Values: isTextType ? [] : undefined
                    };
                }
            });
            if (Object.keys(updates).length === 0) return prev;
            return { ...prev, ...updates };
        });
    }, [marketplaceAttributes]);

    // Handle removing an optional attribute
    const handleRemoveOptionalAttribute = React.useCallback((attributeKey: string) => {
        setActiveOptionalAttributes(prev => prev.filter(key => key !== attributeKey));
        // Remove from saved values
        setAttributeValues(prev => {
            const newValues = {...prev};
            delete newValues[attributeKey];
            return newValues;
        });

        // Add removal to pending changes
        if (apiEndpoint && variationGroup) {
            pendingChangesRef.current[attributeKey] = {
                value: {Code: '', Values: undefined},
                actionType: 'delete'
            };

            if (debugMode) {
                console.log(`[AmazonVariations] 🗑️ Added removal to pending changes:`, attributeKey);
            }
        }
    }, [apiEndpoint, variationGroup, debugMode]);

    // Parent-child visibility: compute which attributes are currently visible
    const visibleAttributeKeys = React.useMemo(() => {
        return new Set(getVisibleAttributeKeys(marketplaceAttributes, attributeValues));
    }, [marketplaceAttributes, attributeValues]);

    // NOTE: children hidden by a parent value change are intentionally NOT cleared here.
    // A transient parent state (switching the parent's shop attribute resets Values,
    // toggling a vid off/on) used to hard-delete the child values locally AND queue
    // backend deletes — permanent data loss when the parent was toggled back. Hidden
    // children now simply keep their values client-side (restored if the parent value
    // returns); the server prunes stale children from the persisted blob on every save
    // (pruneStaleChildAttributes / ML_Temu_Helper_Model_ParentChildVisibility), so the
    // DB never keeps a child the current parent selection does not trigger.

    // Validation logic
    const validateAttributes = React.useCallback(() => {
        const errors: ValidationError[] = [];

        Object.entries(marketplaceAttributes).forEach(([key, attribute]) => {
            if (!visibleAttributeKeys.has(key)) {
                return;
            }

            const savedValue = attributeValues[key];
            if (attribute.required && !savedValue?.Code) {
                errors.push({
                    key,
                    name: attribute.value || key,
                    message: i18n.requiredField || 'Required field must be assigned'
                });
            }
        });

        setValidationErrors(errors);
        onValidationError?.(errors);
        return errors;
    }, [marketplaceAttributes, attributeValues, i18n, onValidationError, visibleAttributeKeys]);

    // Run validation when values change
    React.useEffect(() => {
        validateAttributes();
    }, [validateAttributes]);

    // Function to scroll to first validation error
    const scrollToFirstError = React.useCallback(() => {
        if (validationErrors.length > 0) {
            // Get the first error
            const firstError = validationErrors[0];

            if (debugMode) {
                console.log('[AmazonVariations] 🔍 Scrolling to first validation error:', firstError.key);
            }

            // Find the attribute row element by data attribute
            const errorElement = document.querySelector(`[data-attribute-key="${firstError.key}"]`);

            if (errorElement) {
                // Scroll to the element with smooth behavior
                errorElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                // Optional: Add temporary highlight effect
                errorElement.classList.add('highlight-error');
                setTimeout(() => {
                    errorElement.classList.remove('highlight-error');
                }, 2000);
            }
        }
    }, [validationErrors, debugMode]);

    // Listen for external scroll requests (from form submission ONLY)
    // IMPORTANT: Do NOT auto-scroll when validation errors change during normal editing
    // Only scroll when explicitly requested via custom event (i.e., when submit is clicked)
    React.useEffect(() => {
        const handleScrollRequest = () => {
            if (debugMode) {
                console.log('[AmazonVariations] 🔔 External scroll request received from submit button');
            }
            scrollToFirstError();
        };

        document.addEventListener('amazon-variations-scroll-to-error', handleScrollRequest);

        return () => {
            document.removeEventListener('amazon-variations-scroll-to-error', handleScrollRequest);
        };
    }, [scrollToFirstError, debugMode]);

    // Stable registry key for this instance (apiNamespace when set, auto id otherwise)
    const saveInstanceKeyRef = React.useRef<string | null>(null);
    if (saveInstanceKeyRef.current === null) {
        saveInstanceKeyRef.current = apiNamespace || `__ml_instance_${++mlSaveInstanceCounter}`;
    }

    // Expose save function globally for external triggers (e.g., form submit from jQuery).
    // Every instance registers itself in a shared registry; the unscoped globals flush ALL
    // registered instances. On a single-instance page (Amazon/eBay) this is identical to
    // the previous per-instance global; on a multi-instance page (Temu) the page's Save
    // button no longer flushes only the last-mounted instance.
    React.useEffect(() => {
        const instanceKey = saveInstanceKeyRef.current as string;

        // Flush THIS instance's pending changes; 'failed' only in strictSave mode
        const instanceSave: InstanceSaveFn = async () => {
            try {
                if (debugMode) {
                    console.log(`[Variations] External save triggered (${instanceKey})`);
                }
                const result = await processPendingChanges();
                if (debugMode) {
                    console.log(`[Variations] External save completed (${instanceKey}):`, result);
                }
                return result;
            } catch (error) {
                console.error('[Variations] External save failed:', error);
                // Legacy behavior: a thrown error never blocks the caller unless strictSave
                return strictSave ? 'failed' : 'ok';
            }
        };

        const registry = getSaveRegistry();
        registry[instanceKey] = instanceSave;

        // Flush EVERY registered instance; invoke the callback only when no strict
        // instance reported a failure (a strict failure means data would be lost by
        // submitting/navigating — the failing instance shows the error toast).
        const flushAll = async (callback?: () => void) => {
            const fns = Object.values(getSaveRegistry());
            const results = await Promise.all(fns.map(fn => fn().catch(() => 'ok' as const)));
            const blocked = results.some(result => result === 'failed');
            if (!blocked && callback && typeof callback === 'function') {
                callback();
            }
        };

        // Expose with multiple names for backwards compatibility and marketplace-specific use
        (window as any).magnalisterSaveAmazonVariations = flushAll;
        (window as any).magnalisterSaveEbayVariations = flushAll;
        (window as any).magnalisterSaveVariations = flushAll; // Generic name for all marketplaces

        // Instance-scoped name for targeted flushes/debugging (same pattern as v2 BUG-013)
        if (apiNamespace) {
            (window as any)['magnalisterSaveVariations_' + apiNamespace] = async (callback?: () => void) => {
                const result = await instanceSave();
                if (result !== 'failed' && callback && typeof callback === 'function') {
                    callback();
                }
            };
        }

        // Cleanup on unmount: remove only THIS instance; drop the shared globals only
        // when no instance is left (a surviving instance keeps them working — flushAll
        // reads the registry live)
        return () => {
            const reg = getSaveRegistry();
            delete reg[instanceKey];
            if (apiNamespace) {
                delete (window as any)['magnalisterSaveVariations_' + apiNamespace];
            }
            if (Object.keys(reg).length === 0) {
                delete (window as any).magnalisterSaveAmazonVariations;
                delete (window as any).magnalisterSaveEbayVariations;
                delete (window as any).magnalisterSaveVariations;
            }
        };
    }, [processPendingChanges, strictSave, apiNamespace, debugMode]);

    // Expose function to add optional attributes (for conditional rule links)
    React.useEffect(() => {
        // Instance-scoped helper names when apiNamespace is set → no shared-global race between
        // multiple AmazonVariations instances on the same page (e.g. Temu variation + category).
        const addFnName  = apiNamespace ? 'magnalisterAddOptionalAttribute_'  + apiNamespace : 'magnalisterAddOptionalAttribute';
        const addFnsName = apiNamespace ? 'magnalisterAddOptionalAttributes_' + apiNamespace : 'magnalisterAddOptionalAttributes';

        // Create a globally accessible function to add optional attributes
        (window as any)[addFnName] = (attributeKey: string, callback?: () => void) => {
            try {
                if (debugMode) {
                    console.log('[AmazonVariations] 🔔 External request to add optional attribute:', attributeKey);
                }

                // Check if attribute exists in marketplace attributes
                if (!marketplaceAttributes[attributeKey]) {
                    console.warn('[AmazonVariations] ⚠️ Attribute not found:', attributeKey);
                    if (callback && typeof callback === 'function') {
                        callback();
                    }
                    return;
                }

                // Add the optional attribute
                handleAddOptionalAttribute(attributeKey);

                if (debugMode) {
                    console.log('[AmazonVariations] ✅ Optional attribute added:', attributeKey);
                }

                // Call the callback if provided
                if (callback && typeof callback === 'function') {
                    // Use setTimeout to ensure DOM has updated
                    setTimeout(() => {
                        callback();
                    }, 100);
                }
            } catch (error) {
                console.error('[AmazonVariations] ❌ Failed to add optional attribute:', error);
                if (callback && typeof callback === 'function') {
                    callback();
                }
            }
        };

        // Create a globally accessible batch function to add multiple optional attributes at once
        // Used by error message links where clicking one sub-attribute should add all sibling sub-attributes
        (window as any)[addFnsName] = (attributeKeys: string[], callback?: () => void) => {
            try {
                if (debugMode) {
                    console.log('[AmazonVariations] External request to add optional attributes (batch):', attributeKeys);
                }

                handleAddOptionalAttributes(attributeKeys);

                if (debugMode) {
                    console.log('[AmazonVariations] Batch optional attributes added:', attributeKeys);
                }

                if (callback && typeof callback === 'function') {
                    // Longer timeout for batch since more DOM elements need to render
                    setTimeout(() => {
                        callback();
                    }, 200);
                }
            } catch (error) {
                console.error('[AmazonVariations] Failed to add optional attributes (batch):', error);
                if (callback && typeof callback === 'function') {
                    callback();
                }
            }
        };

        // Cleanup on unmount
        return () => {
            delete (window as any)[addFnName];
            delete (window as any)[addFnsName];
        };
    }, [handleAddOptionalAttribute, handleAddOptionalAttributes, marketplaceAttributes, debugMode, apiNamespace]);

    // Initial save: batch save all attributes with Code !== '' on first render
    React.useEffect(() => {
        // Skip if already done or no API endpoint
        if (initialSaveDoneRef.current || !apiEndpoint || !variationGroup) {
            return;
        }

        // Mark as done immediately to prevent duplicate execution
        initialSaveDoneRef.current = true;

        // Collect all attributes that have Code assigned
        const attributesToSave: Record<string, SavedAttributeValue> = {};
        Object.entries(attributeValues).forEach(([key, value]) => {
            if (value && value.Code && value.Code !== '') {
                attributesToSave[key] = value;
            }
        });

        // If there are attributes to save, do batch save
        if (Object.keys(attributesToSave).length > 0) {
            if (debugMode) {
                console.log('[AmazonVariations] 🚀 Initial batch save:', Object.keys(attributesToSave));
            }

            // Save in batch without showing success message (isInitialSave = true)
            saveAllAttributesBatch(attributesToSave, true);
        } else {
            if (debugMode) {
                console.log('[AmazonVariations] ⚪ No attributes to save on initial load');
            }
        }
    }, [apiEndpoint, variationGroup, attributeValues, saveAllAttributesBatch, debugMode]);

    // Split attributes by requirement, availability, and custom flag
    const {requiredAttributes, displayedOptionalAttributes, availableOptionalAttributes, customAttributes} = React.useMemo(() => {
        // Filter out custom attributes first (eBay-specific) - from backend
        const backendCustom = enableCustomAttributes
            ? Object.entries(marketplaceAttributes).filter(([, attr]) => attr.custom === true)
            : [];

        // Add dynamically created custom attribute slots
        const dynamicCustom: [string, any][] = enableCustomAttributes
            ? dynamicCustomAttributeSlots.map(key => [
                key,
                {
                    value: 'Custom Attribute',
                    custom: true,
                    required: false,
                    dataType: 'text'
                }
            ])
            : [];

        // Combine backend + dynamic custom attributes
        const custom = [...backendCustom, ...dynamicCustom];

        // Required attributes (excluding custom) — includes visible child attributes
        // that were triggered by their parent's value selection, even if not mandatory
        const requiredUnsorted = Object.entries(marketplaceAttributes)
            .filter(([key, attr]) => attr.custom !== true && visibleAttributeKeys.has(key)
                && (attr.required === true || (attr as any).parentRefPid !== undefined));

        // Sort so child attributes appear directly after their parent
        const refPidToKey: Record<number, string> = {};
        for (const [key, attr] of requiredUnsorted) {
            if ((attr as any).refPid !== undefined) {
                refPidToKey[(attr as any).refPid] = key;
            }
        }
        const required: typeof requiredUnsorted = [];
        const added = new Set<string>();
        const addWithChildren = (entries: typeof requiredUnsorted, parentKey: string) => {
            if (added.has(parentKey)) return;
            const entry = entries.find(([k]) => k === parentKey);
            if (!entry) return;
            added.add(parentKey);
            required.push(entry);
            // Find and add children of this parent
            const parentAttr = entry[1] as any;
            if (parentAttr.childAttributes && typeof parentAttr.childAttributes === 'object' && !Array.isArray(parentAttr.childAttributes)) {
                for (const childRefs of Object.values(parentAttr.childAttributes) as any[]) {
                    if (Array.isArray(childRefs)) {
                        for (const ref of childRefs) {
                            const childKey = refPidToKey[ref.childRefPid];
                            if (childKey && visibleAttributeKeys.has(childKey)) {
                                addWithChildren(entries, childKey);
                            }
                        }
                    }
                }
            }
        };
        // First add root attributes (no parent), each followed by its children
        for (const [key, attr] of requiredUnsorted) {
            if ((attr as any).parentRefPid === undefined) {
                addWithChildren(requiredUnsorted, key);
            }
        }
        // Add any remaining (shouldn't happen, but safety net)
        for (const [key] of requiredUnsorted) {
            if (!added.has(key)) {
                required.push(requiredUnsorted.find(([k]) => k === key)!);
            }
        }

        // Optional attributes (excluding custom and child attributes — children are shown
        // automatically in the required section when their parent triggers them)
        const allOptional = Object.entries(marketplaceAttributes)
            .filter(([key, attr]) => attr.required !== true && attr.custom !== true
                && visibleAttributeKeys.has(key) && (attr as any).parentRefPid === undefined);

        // Show optional attributes in the order they were added (based on activeOptionalAttributes array).
        // Exclude child attributes (parentRefPid): they are always rendered in the required section
        // grouped under their parent, so showing them here too would duplicate them.
        const displayed = activeOptionalAttributes
            .map(key => {
                const attribute = marketplaceAttributes[key];
                return attribute ? [key, attribute] as [string, typeof attribute] : null;
            })
            .filter((entry): entry is [string, any] => entry !== null
                && (entry[1] as any).parentRefPid === undefined);

        // Available attributes are those not currently displayed
        const activeSet = new Set(activeOptionalAttributes);
        const available = allOptional
            .filter(([key]) => !activeSet.has(key))
            .map(([key, attribute]) => ({key, attribute}));

        return {
            requiredAttributes: required,
            displayedOptionalAttributes: displayed,
            availableOptionalAttributes: available,
            customAttributes: custom
        };
    }, [marketplaceAttributes, activeOptionalAttributes, enableCustomAttributes, dynamicCustomAttributeSlots, visibleAttributeKeys]);

    // Don't render if no variation group
    if (!variationGroup || variationGroup === 'none' || variationGroup === 'new') {
        if (debugMode) {
            console.log('[AmazonVariations] Not rendering: variationGroup =', variationGroup);
        }
        return null;
    }

    // Render only tbody elements - used by both wrapped and unwrapped modes
    const renderTableBodies = () => (
        <>
            {/* Required Attributes Section */}
            {requiredAttributes.length > 0 && (
                <tbody className="required-attributes-section">
                <tr className="headline">
                    <td colSpan={1} className="section-header marketplace-header">
                        <h4>{i18n.requiredAttributesTitle || `${marketplaceName} Required Attributes`}</h4>
                    </td>
                    {!hideHelpColumn && (
                        <td colSpan={1} className="section-header"></td>
                    )}
                    <td colSpan={1} className="section-header matching-header">
                        <h4>{i18n.attributesMatchingTitle || 'Attributes Matching'}</h4>
                    </td>
                    <td colSpan={1} className="section-header"></td>
                </tr>
                {requiredAttributes.map(([key, attr]) => (
                    <AttributeRow
                        key={key}
                        attributeKey={key}
                        attribute={attr}
                        isRequired={attr.required === true}
                        currentValue={attributeValues[key]}
                        allAttributeValues={attributeValues}
                        conditionalRules={conditionalRules}
                        allMarketplaceAttributes={marketplaceAttributes}
                        variationGroup={variationGroup}
                        marketplaceName={marketplaceName}
                        shopAttributes={shopAttributes}
                        i18n={i18n}
                        databaseTables={databaseTables}
                        disabled={disabled}
                        debugMode={debugMode}
                        hideHelpColumn={hideHelpColumn}
                        error={validationErrors.find(err => err.key === key)?.message}
                        onAttributeChange={handleAttributeChange}
                        onFetchShopAttributeValues={fetchShopAttributeValues}
                    />
                ))}
                <tr className="spacer">
                    <td colSpan={hideHelpColumn ? 3 : 4}></td>
                </tr>
                </tbody>
            )}

            {/* Optional Attributes Section */}
            {displayedOptionalAttributes.length > 0 && (
                <tbody className="optional-attributes-section">
                <tr className="headline">
                    <td colSpan={1} className="section-header marketplace-header">
                        <h4>{i18n.optionalAttributesTitle || `${marketplaceName} Optional Attributes`}</h4>
                    </td>
                    {!hideHelpColumn && (
                        <td colSpan={1} className="section-header"></td>
                    )}
                    <td colSpan={1} className="section-header matching-header">
                        <h4>{i18n.optionalAttributeMatching || 'Optional Attribute Matching'}</h4>
                    </td>
                    <td colSpan={1} className="section-header"></td>
                </tr>
                {displayedOptionalAttributes.map(([key, attr]) => (
                    <AttributeRow
                        key={key}
                        attributeKey={key}
                        attribute={attr}
                        isRequired={false}
                        currentValue={attributeValues[key]}
                        allAttributeValues={attributeValues}
                        conditionalRules={conditionalRules}
                        allMarketplaceAttributes={marketplaceAttributes}
                        variationGroup={variationGroup}
                        marketplaceName={marketplaceName}
                        shopAttributes={shopAttributes}
                        i18n={i18n}
                        databaseTables={databaseTables}
                        disabled={disabled}
                        debugMode={debugMode}
                        hideHelpColumn={hideHelpColumn}
                        error={validationErrors.find(err => err.key === key)?.message}
                        onAttributeChange={handleAttributeChange}
                        onRemoveOptionalAttribute={handleRemoveOptionalAttribute}
                        onFetchShopAttributeValues={fetchShopAttributeValues}
                    />
                ))}
                </tbody>
            )}

            {/* Optional Attribute Selector */}
            {availableOptionalAttributes.length > 0 && (
                <tbody className="optional-attribute-selector-section">
                <tr>
                    <td colSpan={hideHelpColumn ? 3 : 4} style={{padding: '15px'}}>
                        <OptionalAttributeSelector
                            availableOptionalAttributes={availableOptionalAttributes}
                            i18n={i18n}
                            onAttributeSelect={handleAddOptionalAttribute}
                            disabled={disabled}
                            debugMode={debugMode}
                        />
                    </td>
                </tr>
                </tbody>
            )}

            {/* Custom Attributes Section (eBay-specific) */}
            {enableCustomAttributes && customAttributes.length > 0 && (
                <tbody className="custom-attributes-section">
                <tr className="headline">
                    <td colSpan={1} className="section-header marketplace-header">
                        <h4>{i18n.customAttributesTitle || 'Custom Attributes'}</h4>
                    </td>
                    {!hideHelpColumn && (
                        <td colSpan={1} className="section-header"></td>
                    )}
                    <td colSpan={1} className="section-header matching-header">
                        <h4>{i18n.customAttributeMatching || 'Custom Attribute Matching'}</h4>
                    </td>
                    <td colSpan={1} className="section-header"></td>
                </tr>
                {customAttributes.map(([key, attr]) => {
                    // Show remove button only when:
                    // 1. Attribute is not required
                    // 2. Row has at least one selection (attributeCode or valueCode)
                    const rowValue = customAttributeValues[key];
                    const hasSelection = rowValue?.attributeCode || rowValue?.valueCode;
                    const showRemoveButton = !attr.required && hasSelection;

                    return (
                        <CustomAttributeRow
                            key={key}
                            rowId={key}
                            value={rowValue}
                            shopAttributes={shopAttributes}
                            i18n={i18n}
                            disabled={disabled}
                            hideHelpColumn={hideHelpColumn}
                            onChange={handleCustomAttributeChange}
                            onRemove={showRemoveButton ? handleRemoveCustomAttribute : undefined}
                            onFetchShopAttributeValues={fetchShopAttributeValues}
                        />
                    );
                })}
                </tbody>
            )}
        </>
    );

    return (
        <UserChangeContext.Provider value={{lastChangedAttribute}}>
            {/* Success Message - Fixed position works in both wrapped and unwrapped modes */}
            {showSaveSuccess && (
                <div
                    style={{
                        position: 'fixed',
                        top: '20px',
                        right: '20px',
                        backgroundColor: '#59E28D',
                        color: 'white',
                        padding: '12px 40px 12px 20px',
                        borderRadius: '4px',
                        boxShadow: '0 2px 6px rgba(0,0,0,0.15)',
                        zIndex: 9999,
                        fontSize: '14px',
                        animation: 'slideInRight 0.3s ease-out',
                        display: 'flex',
                        alignItems: 'center',
                        gap: '8px'
                    }}
                >
                    <span style={{fontSize: '16px'}}>{'\u2713'}</span>
                    <span>{i18n.saveSuccess || 'Attribute matching saved successfully'}</span>
                    <button
                        onClick={() => setShowSaveSuccess(false)}
                        style={{
                            position: 'absolute',
                            top: '8px',
                            right: '8px',
                            background: 'transparent',
                            border: 'none',
                            color: 'white',
                            fontSize: '18px',
                            cursor: 'pointer',
                            padding: '0',
                            width: '20px',
                            height: '20px',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            opacity: 0.8,
                            transition: 'opacity 0.2s'
                        }}
                        onMouseEnter={(e) => e.currentTarget.style.opacity = '1'}
                        onMouseLeave={(e) => e.currentTarget.style.opacity = '0.8'}
                        title="Close"
                    >
                        ×
                    </button>
                </div>
            )}

            {/* Save Error Message (strictSave only) */}
            {showSaveError && (
                <div
                    style={{
                        position: 'fixed',
                        top: '20px',
                        right: '20px',
                        backgroundColor: '#dc3545',
                        color: 'white',
                        padding: '12px 40px 12px 20px',
                        borderRadius: '4px',
                        boxShadow: '0 2px 6px rgba(0,0,0,0.15)',
                        zIndex: 9999,
                        fontSize: '14px',
                        animation: 'slideInRight 0.3s ease-out',
                        display: 'flex',
                        alignItems: 'center',
                        gap: '8px'
                    }}
                >
                    <span style={{fontSize: '16px'}}>{'✗'}</span>
                    <span>{i18n.saveFailed || 'Saving the attribute matching failed — your changes have NOT been saved. Please try again.'}</span>
                    <button
                        onClick={() => setShowSaveError(false)}
                        style={{
                            position: 'absolute',
                            top: '8px',
                            right: '8px',
                            background: 'transparent',
                            border: 'none',
                            color: 'white',
                            fontSize: '18px',
                            cursor: 'pointer',
                            padding: '0',
                            width: '20px',
                            height: '20px',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            opacity: 0.8,
                            transition: 'opacity 0.2s'
                        }}
                        onMouseEnter={(e) => e.currentTarget.style.opacity = '1'}
                        onMouseLeave={(e) => e.currentTarget.style.opacity = '0.8'}
                        title="Close"
                    >
                        ×
                    </button>
                </div>
            )}

            {wrapInTable ? (
                // Wrapped mode: Full component with div, table, and info text
                <div className={`amazon-variations-container ${className || ''}`}>

                    {/* Attributes Table */}
                    <table
                        className="attributesTable ml-js-attribute-matching"
                        style={{
                            width: '100%',
                            borderCollapse: 'collapse'
                        }}
                    >
                        {renderTableBodies()}
                    </table>

                    {/* Info Text */}
                    <div
                        className="mandatory-fields-info"
                        dangerouslySetInnerHTML={createSafeHtml(
                            i18n.mandatoryFieldsInfo || `Fields with <span style="color: #e31a1c; font-size: 16px;">•</span> are mandatory fields from <strong>${marketplaceName}</strong>.`
                        )}
                    />
                </div>
            ) : (
                // Unwrapped mode: Only tbody elements (no div, no table, no info text)
                renderTableBodies()
            )}
        </UserChangeContext.Provider>
    );
};

// Export the context for use in AttributeRow
export {UserChangeContext};
export default AmazonVariations;