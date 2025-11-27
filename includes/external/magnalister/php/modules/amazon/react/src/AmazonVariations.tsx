import React from 'react';
import {AmazonVariationsProps, SavedAttributeValue, SavedValues, ValidationError} from './types';
import AttributeRow from './components/AmazonVariations/AttributeRow';
import OptionalAttributeSelector from './components/AmazonVariations/OptionalAttributeSelector';
import {createSafeHtml} from './components/AmazonVariations/utils/htmlSanitizer';
import {createShopAttributeValuesFetcher} from './utils/shopAttributeApi';
import './components/AmazonVariations/styles.css';

// Context to track which attribute was last changed by user
const UserChangeContext = React.createContext<{ lastChangedAttribute: string | null }>({
    lastChangedAttribute: null
});

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
                                                               shopAttributes = {},
                                                               marketplaceAttributes = {},
                                                               savedValues = {},
                                                               conditionalRules = [],
                                                               neededFormFields = {},
                                                               i18n = {},
                                                               onValuesChange,
                                                               onValidationError,
                                                               className,
                                                               disabled = false,
                                                               onFetchShopAttributeValues,
                                                               apiEndpoint,
                                                               debugMode = false,
                                                               wrapInTable = true,
                                                               hideHelpColumn = false
                                                           }) => {
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
    const initializeAttributeValues = React.useCallback((savedVals: SavedValues): SavedValues => {
        const initialized: SavedValues = {...savedVals};

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
                const hasSavedData = key in savedVals;

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
        // Initialize with optional attributes that have saved values
        const savedOptionalKeys = Object.keys(savedValues).filter(key => {
            const attribute = marketplaceAttributes[key];
            return attribute && !attribute.required;
        });
        return savedOptionalKeys;
    });

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

    // Track if initial save has been done
    const initialSaveDoneRef = React.useRef(false);

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
            converted.AttributeName = attribute.value || attributeKey;
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

    // Batch save function: saves all attributes in a single AJAX request
    const saveAllAttributesBatch = React.useCallback(async (
        attributesToSave: Record<string, SavedAttributeValue>,
        isInitialSave: boolean = false
    ) => {
        if (!apiEndpoint || !variationGroup) {
            if (debugMode) {
                console.warn('[AmazonVariations] 💾 Cannot save - missing apiEndpoint or variationGroup');
            }
            return;
        }

        try {
            if (debugMode) {
                console.log('[AmazonVariations] 💾 Batch saving attributes:', Object.keys(attributesToSave));
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

            if (!result.success) {
                throw new Error(result.message || 'Failed to batch save attributes');
            }

            if (debugMode) {
                console.log('[AmazonVariations] ✅ Batch save successful');
            }

            // Show success message only if NOT initial save
            if (!isInitialSave) {
                setShowSaveSuccess(true);
                setTimeout(() => {
                    setShowSaveSuccess(false);
                }, 5000); // 5 seconds
            }
        } catch (error) {
            console.error('[AmazonVariations] ❌ Batch save failed:', error);
        }
    }, [apiEndpoint, variationGroup, customIdentifier, variationTheme, neededFormFields, debugMode, convertToBackendFormat]);

    // Internal save function that does the actual AJAX request with retry logic
    const saveAttributeMatchingInternal = React.useCallback(async (
        attributeKey: string,
        value: SavedAttributeValue,
        actionType: 'save' | 'delete' = 'save'
    ) => {
        if (!apiEndpoint || !variationGroup) {
            if (debugMode) {
                console.warn('[AmazonVariations] 💾 Cannot save - missing apiEndpoint or variationGroup');
            }
            return;
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

                if (!result.success) {
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
                return; // Exit successfully
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
                await saveAttributeMatchingInternal(item.attributeKey, item.value, item.actionType);
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

    // Process all pending changes and send to server
    const processPendingChanges = React.useCallback(async () => {
        const pendingKeys = Object.keys(pendingChangesRef.current);

        if (pendingKeys.length === 0) {
            return; // Nothing to save
        }

        if (debugMode) {
            console.log(`[AmazonVariations] 💾 Processing ${pendingKeys.length} pending changes`);
        }

        // Move all pending changes to save queue
        pendingKeys.forEach(attributeKey => {
            const change = pendingChangesRef.current[attributeKey];
            saveAttributeMatching(attributeKey, change.value, change.actionType);
        });

        // Clear pending changes
        pendingChangesRef.current = {};

        // Wait for queue to finish processing
        const maxWaitTime = 30000; // 30 seconds max
        const startTime = Date.now();

        while (isProcessingQueueRef.current || saveQueueRef.current.length > 0) {
            if (Date.now() - startTime > maxWaitTime) {
                console.error('[AmazonVariations] Timeout waiting for save queue to finish');
                break;
            }
            await new Promise(resolve => setTimeout(resolve, 100));
        }

        // Show success message
        setShowSaveSuccess(true);

        // Hide success message after 5 seconds
        setTimeout(() => {
            setShowSaveSuccess(false);
        }, 5000); // 5 seconds
    }, [saveAttributeMatching, debugMode]);

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

    // Handle adding a new optional attribute
    const handleAddOptionalAttribute = React.useCallback((attributeKey: string) => {
        setActiveOptionalAttributes(prev => [...prev, attributeKey]);

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

    // Validation logic
    const validateAttributes = React.useCallback(() => {
        const errors: ValidationError[] = [];

        Object.entries(marketplaceAttributes).forEach(([key, attribute]) => {
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
    }, [marketplaceAttributes, attributeValues, i18n, onValidationError]);

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

    // Expose save function globally for external triggers (e.g., form submit from jQuery)
    React.useEffect(() => {
        // Create a globally accessible save function with callback support
        (window as any).magnalisterSaveAmazonVariations = async (callback?: () => void) => {
            try {
                if (debugMode) {
                    console.log('[AmazonVariations] 🔔 External save triggered');
                }

                // Process all pending changes and wait for completion
                await processPendingChanges();

                if (debugMode) {
                    console.log('[AmazonVariations] ✅ External save completed');
                }

                // Call the callback if provided
                if (callback && typeof callback === 'function') {
                    callback();
                }
            } catch (error) {
                console.error('[AmazonVariations] ❌ External save failed:', error);
                // Still call callback even on error to avoid blocking form submission
                if (callback && typeof callback === 'function') {
                    callback();
                }
            }
        };

        // Cleanup on unmount
        return () => {
            if ((window as any).magnalisterSaveAmazonVariations) {
                delete (window as any).magnalisterSaveAmazonVariations;
            }
        };
    }, [processPendingChanges, debugMode]);

    // Expose function to add optional attributes (for conditional rule links)
    React.useEffect(() => {
        // Create a globally accessible function to add optional attributes
        (window as any).magnalisterAddOptionalAttribute = (attributeKey: string, callback?: () => void) => {
            try {
                if (debugMode) {
                    console.log('[AmazonVariations] 🔔 External request to add optional attribute:', attributeKey);
                }

                // Check if attribute is already active
                if (activeOptionalAttributes.includes(attributeKey)) {
                    if (debugMode) {
                        console.log('[AmazonVariations] ⚠️ Attribute already active:', attributeKey);
                    }
                    // Still call callback even if already active
                    if (callback && typeof callback === 'function') {
                        callback();
                    }
                    return;
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

        // Cleanup on unmount
        return () => {
            if ((window as any).magnalisterAddOptionalAttribute) {
                delete (window as any).magnalisterAddOptionalAttribute;
            }
        };
    }, [handleAddOptionalAttribute, activeOptionalAttributes, marketplaceAttributes, debugMode]);

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

    // Split attributes by requirement and availability
    const {requiredAttributes, displayedOptionalAttributes, availableOptionalAttributes} = React.useMemo(() => {
        const required = Object.entries(marketplaceAttributes)
            .filter(([, attr]) => attr.required === true);

        const allOptional = Object.entries(marketplaceAttributes)
            .filter(([, attr]) => attr.required !== true);

        // Show optional attributes in the order they were added (based on activeOptionalAttributes array)
        const displayed = activeOptionalAttributes
            .map(key => {
                const attribute = marketplaceAttributes[key];
                return attribute ? [key, attribute] as [string, typeof attribute] : null;
            })
            .filter((entry): entry is [string, any] => entry !== null);

        // Available attributes are those not currently displayed
        const activeSet = new Set(activeOptionalAttributes);
        const available = allOptional
            .filter(([key]) => !activeSet.has(key))
            .map(([key, attribute]) => ({key, attribute}));

        return {
            requiredAttributes: required,
            displayedOptionalAttributes: displayed,
            availableOptionalAttributes: available
        };
    }, [marketplaceAttributes, activeOptionalAttributes]);

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
                        isRequired={true}
                        currentValue={attributeValues[key]}
                        allAttributeValues={attributeValues}
                        conditionalRules={conditionalRules}
                        allMarketplaceAttributes={marketplaceAttributes}
                        variationGroup={variationGroup}
                        marketplaceName={marketplaceName}
                        shopAttributes={shopAttributes}
                        i18n={i18n}
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