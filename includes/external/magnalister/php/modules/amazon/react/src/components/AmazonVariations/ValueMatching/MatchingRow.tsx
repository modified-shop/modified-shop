import React from 'react';
import Select from 'react-select';
import {I18nStrings, MarketplaceAttribute, MatchingValue, ShopAttribute} from '@/types';
import {SELECT_CONFIGS} from '../config/selectConfig';

interface MatchingRowProps {
  attributeKey: string;
  amazonAttribute: MarketplaceAttribute;
  shopAttribute: ShopAttribute;
  variationGroup: string;
  rowIndex: number;
  rowData: MatchingValue;
  allMatchings: MatchingValue[]; // All current matchings to filter out used shop values
  canRemove: boolean;
  disabled?: boolean;
  debugMode?: boolean;
  isLastRow?: boolean;
  i18n: I18nStrings;
  onRowChange: (rowIndex: number, field: { type: string; key: string }, value: string) => void;
  onRemoveRow: (rowIndex: number) => void;
  onAddRow?: () => void;
}

/**
 * MatchingRow Component
 *
 * Renders a single row in the value matching table with:
 * - Shop value selector (left column)
 * - Amazon value selector (right column)
 * - Remove button (if removable)
 * - Custom text input for selectAndText attributes
 */
const MatchingRow: React.FC<MatchingRowProps> = ({
  attributeKey,
  amazonAttribute,
  shopAttribute,
  variationGroup,
  rowIndex,
  rowData,
  allMatchings,
  canRemove,
  disabled = false,
  debugMode = false,
  isLastRow = false,
  i18n,
  onRowChange,
  onRemoveRow,
  onAddRow
}) => {
  // State to track custom text entry
  const [customText, setCustomText] = React.useState<string>('');

  // Debounce timer ref
  const debounceTimerRef = React.useRef<NodeJS.Timeout | null>(null);

  // Cleanup debounce timer on unmount
  React.useEffect(() => {
    return () => {
      if (debounceTimerRef.current) {
        clearTimeout(debounceTimerRef.current);
      }
    };
  }, []);

  // Prepare shop attribute options
  const shopOptions = React.useMemo(() => {
    const options = [
      { value: '', label: i18n.pleaseSelect || 'Please select...' }
    ];

    if (shopAttribute?.values) {
      // Get already used shop values from other rows (excluding current row)
      const usedShopValues = new Set(
        allMatchings
          .filter((_, index) => index !== rowIndex) // Exclude current row
          .map(matching => matching.Shop?.Key)
          .filter(Boolean) // Remove empty values
      );

      Object.entries(shopAttribute.values).forEach(([key, value]) => {
        // Only add option if it's not already used or it's the current row's value
        if (!usedShopValues.has(key) || key === rowData?.Shop?.Key) {
          options.push({
            value: key,
            label: debugMode ? `${value} [${key}]` : value
          });
        }
      });
    }

    return options;
  }, [shopAttribute, i18n, debugMode, allMatchings, rowIndex, rowData?.Shop?.Key]);

  // Check if there are any available shop values for new rows
  const hasAvailableShopValues = React.useMemo(() => {
    if (!shopAttribute?.values) return false;

    // Get all used shop values (including current row)
    const allUsedShopValues = new Set(
      allMatchings
        .map(matching => matching.Shop?.Key)
        .filter(Boolean) // Remove empty values
    );

    // Check if there are any unused shop values
    return Object.keys(shopAttribute.values).some(key => !allUsedShopValues.has(key));
  }, [shopAttribute?.values, allMatchings]);

  // Prepare Amazon attribute options
  const amazonOptions = React.useMemo(() => {
    const options = [
      { value: '', label: i18n.pleaseSelect || 'Please select...' }
    ];

    const amazonDataType = amazonAttribute?.dataType?.toLowerCase() || '';

    // Track Amazon values to avoid duplicates
    const amazonValueKeys = new Set<string>();

    // For pure "text" type, only show shop values (no Amazon values)
    if (amazonDataType === 'text') {
      if (shopAttribute?.values) {
        Object.entries(shopAttribute.values).forEach(([key, value]) => {
          options.push({
            value: value,
            label: debugMode ? `${value} [${key}]` : value
          });
        });
      }

      // Add custom entry option at the end
      options.push({
        value: '__custom__',
        label: i18n.makeCustomEntry || 'Make custom entry'
      });
    } else {
      // For select, multiselect, selectAndText, etc. - show Amazon values first
      if (amazonAttribute?.values) {
        Object.entries(amazonAttribute.values).forEach(([key, value]) => {
          amazonValueKeys.add(key);
          options.push({
            value: key,
            label: debugMode ? `${value} [${key}]` : (value as string)
          });
        });
      }

      // For selectAndText attributes, also add shop values that aren't already in Amazon values
      if (amazonDataType === 'selectandtext' && shopAttribute?.values) {
        Object.entries(shopAttribute.values).forEach(([key, value]) => {
          // Only add if not already in Amazon values
          if (!amazonValueKeys.has(key)) {
            options.push({
              value: value,
              label: debugMode ? `${value} [${key}] (Shop)` : `${value}`
            });
          }
        });

        // Add custom entry option at the end
        options.push({
          value: '__custom__',
          label: i18n.makeCustomEntry || 'Make custom entry'
        });
      }
    }

    // Debug log to check dataType
    if (debugMode) {
      console.log('[MatchingRow] Amazon attribute dataType:', amazonDataType, 'for attribute:', attributeKey, 'options count:', options.length);
    }

    return options;
  }, [amazonAttribute, shopAttribute?.values, i18n, debugMode, attributeKey]);

  // Check if custom entry is selected
  // Custom entry is when:
  // 1. Key is explicitly '__custom__', OR
  // 2. Key has a value but it's not found in amazonOptions (after they're computed)
  const isCustomEntrySelected = React.useMemo(() => {
    const marketplaceKey = rowData?.Marketplace?.Key;

    // If explicitly marked as custom
    if (marketplaceKey === '__custom__') {
      return true;
    }

    // If no key, it's not custom
    if (!marketplaceKey) {
      return false;
    }

    // Check if the key exists in amazonOptions
    // If not found, it's a custom value
    const foundInOptions = amazonOptions.some(opt => opt.value === marketplaceKey);
    return !foundInOptions;
  }, [rowData?.Marketplace?.Key, amazonOptions]);

  // Initialize custom text from rowData if it's a custom entry
  React.useEffect(() => {
    if (isCustomEntrySelected) {
      // Only set custom text if it's not "__custom__" (the key itself)
      const value = rowData?.Marketplace?.Value || '';
      setCustomText(value === '__custom__' ? '' : value);
    } else {
      // Clear custom text when not in custom mode
      setCustomText('');
    }
  }, [isCustomEntrySelected, rowData?.Marketplace?.Value]);

  // Find selected options
  const selectedShopOption = shopOptions.find(opt => opt.value === rowData?.Shop?.Key) || shopOptions[0];

  // For Amazon option, if it's a custom entry, select the '__custom__' option
  const selectedAmazonOption = React.useMemo(() => {
    const marketplaceKey = rowData?.Marketplace?.Key;

    // If it's custom entry, find the '__custom__' option
    if (isCustomEntrySelected) {
      return amazonOptions.find(opt => opt.value === '__custom__') || amazonOptions[0];
    }

    // Otherwise, find by the actual key
    return amazonOptions.find(opt => opt.value === marketplaceKey) || amazonOptions[0];
  }, [amazonOptions, rowData?.Marketplace?.Key, isCustomEntrySelected]);

  // Handle shop value change
  const handleShopChange = (selectedOption: any) => {
    onRowChange(rowIndex, { type: 'Shop', key: 'Key' }, selectedOption?.value || '');
  };

  // Handle Amazon value change
  const handleAmazonChange = (selectedOption: any) => {
    const selectedValue = selectedOption?.value || '';

    // Update the marketplace key
    onRowChange(rowIndex, { type: 'Marketplace', key: 'Key' }, selectedValue);

    // If custom entry is selected, also update the value with current custom text
    if (selectedValue === '__custom__' && customText) {
      onRowChange(rowIndex, { type: 'Marketplace', key: 'Value' }, customText);
    }
  };

  // Handle custom text change with debounce
  const handleCustomTextChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const newText = e.target.value;
    setCustomText(newText);

    // Clear previous debounce timer
    if (debounceTimerRef.current) {
      clearTimeout(debounceTimerRef.current);
    }

    // Set new debounce timer (500ms delay)
    debounceTimerRef.current = setTimeout(() => {
      // Update both Key and Value for custom entries after debounce
      onRowChange(rowIndex, { type: 'Marketplace', key: 'Key' }, '__custom__');
      onRowChange(rowIndex, { type: 'Marketplace', key: 'Value' }, newText);
    }, 500);
  };

  // Determine if we should use searchable selects
  const useSearchableShopSelect = shopOptions.length >= SELECT_CONFIGS.ATTRIBUTE_SELECTOR.threshold;
  const useSearchableAmazonSelect = amazonOptions.length >= SELECT_CONFIGS.AMAZON_VALUE_SELECTOR.threshold;

  return (
    <tr className="matching-row">
      {/* Shop Value Column */}
      <td className="shop-value-column">
        {useSearchableShopSelect ? (
          <Select
            name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][Values][${rowIndex}][Shop][Key]`}
            options={shopOptions}
            value={selectedShopOption}
            onChange={handleShopChange}
            isDisabled={disabled}
            isSearchable={true}
            isClearable={false}
            placeholder={i18n.pleaseSelect || 'Please select...'}
            styles={SELECT_CONFIGS.AMAZON_VALUE_SELECTOR.styles}
            className="shop-value-select"
            classNamePrefix="ml-amazon-shop-val"
            menuPortalTarget={document.body}
            menuPosition="fixed"
            menuPlacement="auto"
          />
        ) : (
          <select
            name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][Values][${rowIndex}][Shop][Key]`}
            value={selectedShopOption?.value || ''}
            onChange={(e) => handleShopChange({ value: e.target.value })}
            disabled={disabled}
            className="shop-value-select"
          >
            {shopOptions.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
        )}
      </td>

      {/* Amazon Value Column */}
      <td className="amazon-value-column">
        <div style={{
          display: 'flex',
          flexWrap: 'wrap',
          gap: '8px',
          alignItems: 'flex-start'
        }}>
          <div style={{ flex: '1 1 200px', minWidth: '200px' }}>
            {useSearchableAmazonSelect ? (
              <Select
                name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][Values][${rowIndex}][Marketplace][Key]`}
                options={amazonOptions}
                value={selectedAmazonOption}
                onChange={handleAmazonChange}
                isDisabled={disabled}
                isSearchable={true}
                isClearable={false}
                placeholder={i18n.pleaseSelect || 'Please select...'}
                styles={SELECT_CONFIGS.AMAZON_VALUE_SELECTOR.styles}
                className="amazon-matching-select"
                classNamePrefix="ml-amazon-match-val"
                menuPortalTarget={document.body}
                menuPosition="fixed"
                menuPlacement="auto"
              />
            ) : (
              <select
                name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][Values][${rowIndex}][Marketplace][Key]`}
                value={selectedAmazonOption?.value || ''}
                onChange={(e) => handleAmazonChange({ value: e.target.value })}
                disabled={disabled}
                className="amazon-matching-select"
              >
                {amazonOptions.map((option) => (
                  <option key={option.value} value={option.value}>
                    {option.label}
                  </option>
                ))}
              </select>
            )}
          </div>

          {/* Custom text input for selectAndText attributes */}
          {isCustomEntrySelected && (
            <div style={{ flex: '1 1 200px', minWidth: '200px' }}>
              <input
                type="text"
                value={customText}
                onChange={handleCustomTextChange}
                disabled={disabled}
                placeholder={i18n.enterCustomAmazonValue || 'Enter custom Amazon value'}
                className="custom-text-input"
                style={{
                  width: '100%',
                  padding: '8px',
                  fontSize: '14px',
                  border: '1px solid #ced4da',
                  borderRadius: '4px'
                }}
              />
            </div>
          )}
        </div>
      </td>

      {/* Action Column */}
      <td className="action-column">
        <div style={{ display: 'flex', gap: '4px', alignItems: 'center' }}>
          <button
            type="button"
            className="mlbtn action remove-matching-row"
            onClick={() => onRemoveRow(rowIndex)}
            disabled={disabled || (!canRemove && rowIndex === 0)}
            title={i18n.removeMatchingRow || 'Remove matching row'}
          >
            -
          </button>
          {isLastRow && onAddRow && hasAvailableShopValues && (
            <button
              type="button"
              className="mlbtn action add-matching-row"
              onClick={onAddRow}
              disabled={disabled}
              title={'Add new matching row'}
            >
              +
            </button>
          )}
        </div>
      </td>
    </tr>
  );
};

export default MatchingRow;