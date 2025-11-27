import React from 'react';
import {I18nStrings, MarketplaceAttribute, MatchingValue, ShopAttribute} from '@/types';
import MatchingRow from '@/components/AmazonVariations/ValueMatching/MatchingRow';
import SparklesIcon from '@/assets/Sparkles.svg';
import TrashIcon from '@/assets/Trash.svg';
import InfoIcon from '@/assets/info_tooltip.png';
import {INFO_BOX_STYLES} from '@/components/AmazonVariations/styles/infoBoxStyles';
import {applyStyleWithImportant} from '@/utils/styleUtils';

interface ValueMatchingTableProps {
    attributeKey: string;
    amazonAttribute: MarketplaceAttribute;
    shopAttribute: ShopAttribute;
    shopAttributeCode: string; // The actual shop attribute code to fetch values for
    variationGroup: string;
    currentMatchings: MatchingValue[];
    disabled?: boolean;
    debugMode?: boolean;
    useShopValues?: boolean; // Whether to use shop values directly without matching
    i18n: I18nStrings;
    onMatchingsChange: (matchings: MatchingValue[]) => void;
    onUseShopValuesChange?: (value: boolean) => void; // Callback when checkbox changes
    // Optional: provide a function to fetch shop attribute values
    onFetchShopAttributeValues?: (attributeCode: string) => Promise<{ [key: string]: string }>;
}

/**
 * ValueMatchingTable Component
 *
 * Displays a table for matching shop attribute values with Amazon attribute values.
 * Shows when a shop attribute with type="select" is selected.
 *
 * Features:
 * - Two-column layout: Shop values and Amazon values
 * - Dynamic rows based on shop attribute values
 * - Plus button to add new matchings
 * - Minus button to remove existing matchings
 * - Handles both existing and new value mappings
 */
const ValueMatchingTable: React.FC<ValueMatchingTableProps> = ({
                                                                   attributeKey,
                                                                   amazonAttribute,
                                                                   shopAttribute,
                                                                   shopAttributeCode,
                                                                   variationGroup,
                                                                   currentMatchings = [],
                                                                   disabled = false,
                                                                   debugMode = false,
                                                                   useShopValues = false,
                                                                   i18n,
                                                                   onMatchingsChange,
                                                                   onUseShopValuesChange,
                                                                   onFetchShopAttributeValues
                                                               }) => {

    // State for lazy-loaded shop attribute values
    const [loadedShopValues, setLoadedShopValues] = React.useState<{ [key: string]: string } | null>(null);
    const [isLoadingValues, setIsLoadingValues] = React.useState(false);
    const [loadError, setLoadError] = React.useState<string | null>(null);

    // State for auto-match statistics
    const [matchStats, setMatchStats] = React.useState<{
        exactMatches: number;
        noMatches: number;
    } | null>(null);

    // State for search filter
    const [searchFilter, setSearchFilter] = React.useState<string>('');

    // Refs for info box styling with !important
    const infoBoxContainerRef = React.useRef<HTMLDivElement>(null);
    const infoBoxHeaderRef = React.useRef<HTMLDivElement>(null);
    const infoBoxIconRef = React.useRef<HTMLImageElement>(null);
    const infoBoxTextRef = React.useRef<HTMLParagraphElement>(null);

    // Stable ID counter (persists across re-renders but resets on mount)
    const idCounterRef = React.useRef(0);

    // Helper function to generate stable IDs
    const generateStableId = React.useCallback((prefix: string) => {
        idCounterRef.current += 1;
        return `${prefix}-${idCounterRef.current}`;
    }, []);

    // Apply styles with !important to info box to override PrestaShop CSS
    // This effect runs after every render when the info box is visible
    React.useEffect(() => {
        // Only apply if refs are available (info box is rendered)
        if (infoBoxContainerRef.current) {
            applyStyleWithImportant(infoBoxContainerRef.current, INFO_BOX_STYLES.container);
            applyStyleWithImportant(infoBoxHeaderRef.current, INFO_BOX_STYLES.header);
            applyStyleWithImportant(infoBoxIconRef.current, INFO_BOX_STYLES.icon);
            applyStyleWithImportant(infoBoxTextRef.current, INFO_BOX_STYLES.text);
        }
    });

    // Effect to reset state when shop attribute code changes
    React.useEffect(() => {
        // Reset loaded values when shop attribute changes
        setLoadedShopValues(null);
        setIsLoadingValues(false);
        setLoadError(null);
        setSearchFilter(''); // Reset search filter
    }, [shopAttributeCode, attributeKey]);

    // Effect to load shop attribute values when needed
    // IMPORTANT: Only run when shopAttributeCode changes, NOT when loadedShopValues/isLoadingValues change
    // to prevent infinite re-fetch loops
    React.useEffect(() => {
        const needsLoading = !shopAttribute?.values && !loadedShopValues && !isLoadingValues && onFetchShopAttributeValues && shopAttributeCode;

        if (needsLoading) {
            setIsLoadingValues(true);
            setLoadError(null);

            onFetchShopAttributeValues(shopAttributeCode)
                .then((values) => {
                    setLoadedShopValues(values);
                    setIsLoadingValues(false);
                })
                .catch((error) => {
                    console.error(`[ValueMatchingTable] ❌ AJAX ERROR for ${shopAttributeCode}:`, error);
                    setLoadError(error.message || 'Failed to load shop attribute values');
                    setIsLoadingValues(false);
                });
        }
    }, [shopAttribute?.values, onFetchShopAttributeValues, shopAttributeCode]);

    // Get all shop values that could be matched
    const shopValues = React.useMemo(() => {
        // Use either pre-loaded values or lazy-loaded values
        const values = shopAttribute?.values || loadedShopValues;

        if (!values) {
            return [];
        }

        const shopValuesList = Object.entries(values).map(([key, value]) => ({
            key,
            value: value as string
        }));


        return shopValuesList;
    }, [shopAttribute?.values, loadedShopValues]);

    // Create matching rows based on saved matchings with stable IDs
    // IMPORTANT: IDs must be stable across re-renders to prevent unnecessary DOM recreation
    const matchingRows = React.useMemo(() => {
        // If we have saved matchings, show them with stable IDs
        if (currentMatchings.length > 0) {
            return currentMatchings.map((matching, index) => ({
                ...matching,
                // Use existing __id, or generate a stable one based on Shop.Key + Marketplace.Key
                // This ensures same matching always gets same ID across re-renders
                __id: matching.__id || `saved-${matching.Shop?.Key || 'empty'}-${matching.Marketplace?.Key || 'empty'}-${index}`
            }));
        }

        // If no saved matchings, show one empty row for new matching with stable ID
        // Using a fixed ID since we always have exactly one empty row
        return [{
            Shop: {
                Key: '',
                Value: ''
            },
            Marketplace: {
                Key: '',
                Value: ''
            },
            __id: `empty-initial`
        }];
    }, [currentMatchings]);

    // Filter rows based on search input (non-destructive filter)
    const filteredRows = React.useMemo(() => {
        if (!searchFilter.trim()) {
            return matchingRows; // No filter, show all rows
        }

        const lowerSearch = searchFilter.toLowerCase();
        return matchingRows.filter(row => {
            const shopValue = row.Shop?.Value?.toLowerCase() || '';
            const shopKey = row.Shop?.Key?.toLowerCase() || '';
            const amazonValue = row.Marketplace?.Value?.toLowerCase() || '';
            const amazonKey = row.Marketplace?.Key?.toLowerCase() || '';

            return shopValue.includes(lowerSearch) ||
                shopKey.includes(lowerSearch) ||
                amazonValue.includes(lowerSearch) ||
                amazonKey.includes(lowerSearch);
        });
    }, [matchingRows, searchFilter]);

    // Handle row data change
    const handleRowChange = React.useCallback((
        rowIndex: number,
        field: { type: string; key: string },
        value: string
    ) => {
        const updatedRows = [...matchingRows];
        const row = updatedRows[rowIndex];

        if (field.type === 'Shop') {
            if (!row.Shop) row.Shop = {Key: '', Value: ''};
            (row.Shop as any)[field.key] = value;

            // Auto-set the shop value label from loaded shop values
            if (field.key === 'Key' && value) {
                const shopValues = shopAttribute?.values || loadedShopValues || {};
                row.Shop.Value = shopValues[value] || value;
            }
        } else if (field.type === 'Marketplace') {
            if (!row.Marketplace) row.Marketplace = {Key: '', Value: ''};
            (row.Marketplace as any)[field.key] = value;

            // Auto-set the marketplace value label from amazonAttribute.values
            if (field.key === 'Key' && value && amazonAttribute?.values) {
                row.Marketplace.Value = amazonAttribute.values[value] as string || value;
            }
        }

        // Always notify parent of changes, regardless of completeness
        onMatchingsChange(updatedRows);
    }, [matchingRows, amazonAttribute, shopAttribute?.values, loadedShopValues, onMatchingsChange]);

    // Handle removing a row (completely remove the row)
    const handleRemoveRow = React.useCallback((rowIndex: number) => {
        const updatedRows = [...matchingRows];
        updatedRows.splice(rowIndex, 1);

        // If we removed all rows, add one empty row with stable ID
        if (updatedRows.length === 0) {
            updatedRows.push({
                Shop: {Key: '', Value: ''},
                Marketplace: {Key: '', Value: ''},
                __id: generateStableId('empty-after-remove')
            });
        }

        onMatchingsChange(updatedRows);
    }, [matchingRows, onMatchingsChange, generateStableId]);

    // Handle adding a new row
    const handleAddRow = React.useCallback(() => {
        const updatedRows = [...matchingRows, {
            Shop: {Key: '', Value: ''},
            Marketplace: {Key: '', Value: ''},
            __id: generateStableId('new-row')
        }];
        onMatchingsChange(updatedRows);
    }, [matchingRows, onMatchingsChange, generateStableId]);

    // Clear all matchings function
    const handleClearAllMatchings = React.useCallback(() => {
        // Reset to one empty row
        const emptyRow = {
            Shop: {Key: '', Value: ''},
            Marketplace: {Key: '', Value: ''},
            __id: generateStableId('empty-after-clear')
        };

        onMatchingsChange([emptyRow]);
        setMatchStats(null); // Clear statistics
    }, [onMatchingsChange, generateStableId]);

    // Check if there are any completed matchings (at least one row with both shop and amazon values)
    const hasMatchedValues = React.useMemo(() => {
        return matchingRows.some(row =>
            row.Shop?.Key && row.Marketplace?.Key
        );
    }, [matchingRows]);

    // Auto-match with exact case-insensitive matching only
    const handleAutoMatch = React.useCallback(() => {
        const shopValues = shopAttribute?.values || loadedShopValues || {};
        const amazonValues = amazonAttribute?.values || {};

        if (Object.keys(shopValues).length === 0) {
            return;
        }

        const newMatchings: MatchingValue[] = [];
        let exactMatches = 0;
        let noMatches = 0;

        const amazonDataType = amazonAttribute?.dataType?.toLowerCase() || '';

        // Check if this is a selectAndText or text attribute (both allow shop values)
        const allowsShopValues = amazonDataType === 'selectandtext' || amazonDataType === 'text';

        // Create a case-insensitive lookup map for Amazon values
        const amazonLookupMap = new Map<string, { key: string; label: string }>();
        if (amazonValues && Object.keys(amazonValues).length > 0) {
            Object.entries(amazonValues).forEach(([amazonKey, amazonLabel]) => {
                amazonLookupMap.set((amazonLabel as string).toLowerCase(), {
                    key: amazonKey,
                    label: amazonLabel as string
                });
            });
        }

        // Exact case-insensitive matching algorithm
        // IMPORTANT: Only add rows for MATCHED values, not for unmatched ones
        Object.entries(shopValues).forEach(([shopKey, shopLabel]) => {
            const shopLabelLower = shopLabel.toLowerCase();
            let matched = false;

            // First, search in Amazon values (exact match, case-insensitive)
            const amazonMatch = amazonLookupMap.get(shopLabelLower);
            if (amazonMatch) {
                exactMatches++;
                matched = true;
                newMatchings.push({
                    Shop: {Key: shopKey, Value: shopLabel},
                    // For text/selectAndText, use label for Key; otherwise use key
                    Marketplace: {
                        Key: allowsShopValues ? amazonMatch.label : amazonMatch.key,
                        Value: amazonMatch.label
                    },
                    __id: `auto-${shopKey}-${amazonMatch.key}`
                });
            }

            // For selectAndText and text attributes, if no match found in Amazon values,
            // match with shop value itself (use shop LABEL for both Key and Value)
            if (!matched && allowsShopValues) {
                exactMatches++;
                matched = true;
                newMatchings.push({
                    Shop: {Key: shopKey, Value: shopLabel},
                    Marketplace: {Key: shopLabel, Value: shopLabel},
                    __id: `auto-${shopKey}-${shopLabel}`
                });
            }

            // Count unmatched values but DON'T add them to the table
            if (!matched) {
                noMatches++;
            }
        });

        // If no matches found, add one empty row so user can manually add matchings
        if (newMatchings.length === 0) {
            newMatchings.push({
                Shop: {Key: '', Value: ''},
                Marketplace: {Key: '', Value: ''},
                __id: generateStableId('empty-after-automatch')
            });
        }

        // Update statistics
        setMatchStats({
            exactMatches,
            noMatches
        });

        onMatchingsChange(newMatchings);
    }, [shopAttribute?.values, loadedShopValues, amazonAttribute?.values, amazonAttribute?.dataType, onMatchingsChange, generateStableId]);


    // Handle loading state
    if (isLoadingValues) {
        return (
            <div className="value-matching-table-container">
                <div className="matching-table-header">
                    <h5>{i18n.valueMatchingTitle || 'Value Matching'}</h5>
                    <div style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: '10px',
                        padding: '15px',
                        backgroundColor: '#f8f9fa',
                        border: '1px solid #dee2e6',
                        borderRadius: '4px',
                        marginTop: '10px'
                    }}>
                        <div style={{
                            width: '20px',
                            height: '20px',
                            border: '3px solid #f3f3f3',
                            borderTop: '3px solid #3498db',
                            borderRadius: '50%',
                            animation: 'spin 1s linear infinite'
                        }}/>
                        <p style={{margin: 0}}>
                            {i18n.loadingShopValues || 'Loading shop attribute values...'}
                        </p>
                    </div>
                </div>
                <style>{`
          @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
          }
        `}</style>
            </div>
        );
    }

    // Handle error state
    if (loadError) {
        return (
            <div className="value-matching-table-container">
                <div className="matching-table-header">
                    <h5>{i18n.valueMatchingTitle || 'Value Matching'}</h5>
                    <p className="matching-description error">
                        {i18n.loadErrorMessage || `Error loading shop attribute values: ${loadError}`}
                    </p>
                </div>
            </div>
        );
    }

    // Don't render if no shop values and not loading
    if (shopValues.length === 0 && !onFetchShopAttributeValues) {
        return (
            <div className="value-matching-table-container">
                <div className="matching-table-header">
                    <h5>{i18n.valueMatchingTitle || 'Value Matching'}</h5>
                    <p className="matching-description info">
                        {i18n.noShopValuesMessage ||
                            `The selected shop attribute "${shopAttribute.name}" has no values to match. This attribute may need to be configured in your shop system first.`
                        }
                    </p>
                </div>
            </div>
        );
    }

    // Determine Amazon attribute data type
    const amazonDataType = amazonAttribute?.dataType?.toLowerCase() || '';

    // V2 OVERRIDE: Disable "Use Shop Values" checkbox feature
    // Always show matching table for select attributes, never show checkbox
    const showCheckbox = false;

    // Show matching table when checkbox is unchecked (or doesn't exist)
    const showMatchingTable = (!showCheckbox || !useShopValues);

    return (

        <div className="value-matching-table-container">
            {/* Use Shop Values Checkbox - Only show for selectAndText and multiSelectAndText types */}
            {showCheckbox && (
                <div style={{marginTop: '12px', marginBottom: '12px'}}>
                    <label style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: '8px',
                        cursor: disabled ? 'not-allowed' : 'pointer',
                        fontSize: '14px'
                    }}>
                        <input
                            type="checkbox"
                            checked={useShopValues}
                            onChange={(e) => onUseShopValuesChange?.(e.target.checked)}
                            disabled={disabled}
                            style={{
                                cursor: disabled ? 'not-allowed' : 'pointer',
                                width: '16px',
                                height: '16px'
                            }}
                        />
                        <span>{i18n.useShopValuesCheckbox || 'Use webshop provided value'}</span>
                    </label>
                </div>
            )}

            {/* Show info message when checkbox is checked */}
            {showCheckbox && useShopValues && (
                <div ref={infoBoxContainerRef} style={INFO_BOX_STYLES.container}>
                    <div ref={infoBoxHeaderRef} style={INFO_BOX_STYLES.header}>
                        <img
                            ref={infoBoxIconRef}
                            src={InfoIcon}
                            alt="Info"
                            style={INFO_BOX_STYLES.icon}
                        />
                        <span>{i18n.useShopValuesCheckbox || 'Use webshop provided value'}</span>
                    </div>
                    <p ref={infoBoxTextRef} style={INFO_BOX_STYLES.text}>
                        {i18n.useShopValuesDescription || 'Shop attribute values will be sent directly to Amazon without manual matching.'}
                    </p>
                </div>
            )}

            {/* Matching table - show when NOT text type AND (no checkbox OR checkbox unchecked) */}
            {showMatchingTable && (
                <>
                    <div style={{
                        display: 'flex',
                        alignItems: 'flex-start',
                        gap: '10px',
                        marginBottom: '15px',
                        flexWrap: 'wrap',
                        width: '100%'
                    }}>

                        {/* Title on the left */}
                        <h5 style={{margin: 0, fontSize: '16px', fontWeight: 'bold', flex: '0 0 auto'}}>
                            {i18n.valueMatchingTitle || 'Value Matching'}
                        </h5>

                        {/* Buttons on the right - always pushed to right with marginLeft: auto */}
                        <div style={{
                            display: 'flex',
                            gap: '8px',
                            marginLeft: 'auto',
                            flexWrap: 'nowrap'
                        }}>
                            {/* Auto-match button */}
                            <button
                                type="button"
                                className="mlbtn action auto-match-button"
                                onClick={handleAutoMatch}
                                disabled={disabled || Object.keys(shopAttribute?.values || loadedShopValues || {}).length === 0}
                                title={i18n.autoMatching || 'Auto-match similar values'}
                                style={{
                                    padding: '6px 10px',
                                    fontSize: '14px',
                                    lineHeight: '1',
                                    minWidth: 'auto',
                                    backgroundColor: '#dc3545',
                                    color: 'white',
                                    border: 'none',
                                    cursor: disabled ? 'not-allowed' : 'pointer',
                                    opacity: disabled ? '0.5' : '1',
                                    boxShadow: '0 1px 3px rgba(0,0,0,0.2)',
                                    height: '39px'
                                }}
                            >
                                <img
                                    src={SparklesIcon}
                                    alt="Auto-match"
                                    style={{
                                        width: '20px',
                                        height: '20px',
                                        display: 'block',
                                        filter: 'brightness(0) invert(1)' // Make the icon white
                                    }}
                                />
                            </button>

                            {/* Clear all matchings button - only show when there are matched values */}
                            {hasMatchedValues && (
                                <button
                                    type="button"
                                    className="mlbtn action clear-matchings-button"
                                    onClick={handleClearAllMatchings}
                                    disabled={disabled}
                                    title={i18n.clearAllMatchings || 'Clear all matchings'}
                                    style={{
                                        padding: '6px 10px',
                                        fontSize: '14px',
                                        lineHeight: '1',
                                        minWidth: 'auto',
                                        backgroundColor: '#dc3545',
                                        color: 'white',
                                        border: 'none',
                                        cursor: disabled ? 'not-allowed' : 'pointer',
                                        opacity: disabled ? '0.5' : '1',
                                        boxShadow: '0 1px 3px rgba(0,0,0,0.2)',
                                        height: '39px'
                                    }}
                                >
                                    <img
                                        src={TrashIcon}
                                        alt="Clear matchings"
                                        style={{
                                            width: '20px',
                                            height: '20px',
                                            display: 'block',
                                            filter: 'brightness(0) invert(1)' // Make the icon white
                                        }}
                                    />
                                </button>
                            )}
                        </div>
                    </div>

                    <div className="matching-table-header">
                        <p className="matching-description">
                            {i18n.valueMatchingDescription ||
                                'Match your shop attribute values with Amazon attribute values:'
                            }
                        </p>


                        {/* Search/Filter input - only show if there are matchings to filter */}
                        {!useShopValues && matchingRows.length > 5 && (
                            <div style={{marginTop: '12px', marginBottom: '12px'}}>
                                <input
                                    type="text"
                                    placeholder={i18n.searchMatchings || 'Search matchings...'}
                                    value={searchFilter}
                                    onChange={(e) => setSearchFilter(e.target.value)}
                                    style={{
                                        width: '100%',
                                        maxWidth: '400px',
                                        padding: '8px 12px',
                                        fontSize: '14px',
                                        border: '1px solid #ced4da',
                                        borderRadius: '4px'
                                    }}
                                />
                                {searchFilter && (
                                    <div style={{marginTop: '6px', fontSize: '13px', color: '#666'}}>
                                        {i18n.showingResults || 'Showing'} {filteredRows.length} {i18n.of || 'of'} {matchingRows.length} {i18n.matchings || 'matchings'}
                                    </div>
                                )}
                            </div>
                        )}
                    </div>

                    {/* Scrollable table container for large datasets*/}
                    <div style={{
                        maxHeight: matchingRows.length > 10 ? '500px' : 'none',
                        overflowY: matchingRows.length > 10 ? 'auto' : 'visible',
                        border: matchingRows.length > 10 ? '1px solid #dee2e6' : 'none',
                        borderRadius: '4px'
                    }}>
                        <table className="value-matching-table">
                            <thead style={{
                                position: matchingRows.length > 10 ? 'sticky' : 'static',
                                top: 0,
                                backgroundColor: '#fff',
                                zIndex: 1
                            }}>
                            <tr>
                                <th className="shop-value-header">
                                    {i18n.shopValueColumn || 'Shop Value'}
                                </th>
                                <th className="amazon-value-header">
                                    {i18n.amazonValueColumn || 'Amazon Value'}
                                </th>
                                <th className="action-header">
                                    {i18n.actionColumn || 'Action'}
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            {filteredRows.map((rowData) => {
                                // Find the original index in matchingRows for proper updates
                                const originalIndex = matchingRows.findIndex(r => r.__id === rowData.__id);

                                // Determine if this row can be removed (has Amazon value selected)
                                const canRemove = Boolean(rowData.Marketplace?.Key);
                                const isLastRow = originalIndex === matchingRows.length - 1;

                                return (
                                    <MatchingRow
                                        key={rowData.__id || `fallback-${originalIndex}`}
                                        attributeKey={attributeKey}
                                        amazonAttribute={amazonAttribute}
                                        shopAttribute={{
                                            ...shopAttribute,
                                            values: shopAttribute?.values || loadedShopValues || {}
                                        }}
                                        variationGroup={variationGroup}
                                        rowIndex={originalIndex}
                                        rowData={rowData}
                                        allMatchings={matchingRows}
                                        canRemove={canRemove}
                                        disabled={disabled}
                                        debugMode={debugMode}
                                        isLastRow={isLastRow}
                                        i18n={i18n}
                                        onRowChange={handleRowChange}
                                        onRemoveRow={handleRemoveRow}
                                        onAddRow={handleAddRow}
                                    />
                                );
                            })}
                            </tbody>
                        </table>
                    </div>

                    {/* Auto-match statistics */}
                    {matchStats && (
                        <div
                            className="auto-match-stats"
                            style={{
                                marginTop: '12px',
                                padding: '10px 12px',
                                backgroundColor: '#f8f9fa',
                                border: '1px solid #dee2e6',
                                borderRadius: '4px',
                                fontSize: '13px'
                            }}
                        >
                            <div style={{fontWeight: 'bold', marginBottom: '6px'}}>
                                {i18n.autoMatchResults || 'Auto-Match Results:'}
                            </div>
                            <div style={{display: 'flex', gap: '20px', flexWrap: 'wrap'}}>
                                <div style={{display: 'flex', alignItems: 'center', gap: '6px'}}>
                                    <span style={{color: '#59E28D', fontWeight: 'bold', fontSize: '16px'}}>✓</span>
                                    <span>
                  <strong>{matchStats.exactMatches}</strong> {i18n.exactMatches || 'Exact match(es)'}
                </span>
                                </div>
                                <div style={{display: 'flex', alignItems: 'center', gap: '6px'}}>
                                    <span style={{color: '#dc3545', fontWeight: 'bold', fontSize: '16px'}}>✗</span>
                                    <span>
                  <strong>{matchStats.noMatches}</strong> {i18n.noMatches || 'No match(es)'}
                </span>
                                </div>
                            </div>
                        </div>
                    )}
                </>
            )}
        </div>
    );
};

export default ValueMatchingTable;