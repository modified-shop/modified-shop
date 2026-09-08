import React from 'react';
import Select, {SingleValue, StylesConfig} from 'react-select';
import {I18nStrings, MatchingValue, ShopAttribute, ShopAttributes} from '../../types';
import ValueMatchingTable from '../AmazonVariations/ValueMatching/ValueMatchingTable';
import {isValueListType} from '../../constants/attributeTypes';

/**
 * Custom attribute value configuration
 */
export interface CustomAttributeValue {
  /** Shop attribute code or 'freetext' for custom name */
  attributeCode: string;
  /** Custom name when attributeCode is 'freetext' */
  customName?: string;
  /** Shop value code or custom value */
  valueCode: string;
  /** Custom value when using freetext */
  customValue?: string;
  /** Value matchings when shop attribute has type 'select' */
  matchings?: MatchingValue[];
  /** Use shop values directly (default: true) */
  useShopValues?: boolean;
  /** Date format for date-type metafield attributes (e.g. 'Y-m-d', 'd.m.Y') */
  dateFormat?: string;
}

/**
 * Props for CustomAttributeRow component
 */
export interface CustomAttributeRowProps {
  /** Unique identifier for this row */
  rowId: string;
  /** Current value configuration */
  value?: CustomAttributeValue;
  /** Available shop attributes */
  shopAttributes: ShopAttributes;
  /** Internationalization strings */
  i18n: I18nStrings;
  /** Whether the row is disabled */
  disabled?: boolean;
  /** Hide help column (for v2 compatibility) */
  hideHelpColumn?: boolean;
  /** Callback when value changes */
  onChange: (rowId: string, value: CustomAttributeValue) => void;
  /** Callback to remove this row */
  onRemove?: (rowId: string) => void;
  /** Callback to fetch shop attribute values */
  onFetchShopAttributeValues?: (attributeCode: string) => Promise<{ [key: string]: string }>;
}

/**
 * Select option interface
 */
interface SelectOption {
  value: string;
  label: string;
  isDisabled?: boolean;
}

/**
 * Select option group interface
 */
interface SelectOptionGroup {
  label: string;
  options: SelectOption[];
}

type SelectOptions = SelectOption | SelectOptionGroup;

/**
 * CustomAttributeRow Component
 *
 * Renders a row for eBay custom attribute matching with:
 * - Left column: Attribute NAME dropdown (shop attributes + freetext option)
 * - Right column: Attribute VALUE dropdown (shop values or freetext input)
 *
 * This allows users to map shop attributes to custom eBay attributes
 * with flexible naming (either use shop attribute name or enter custom name).
 */
const CustomAttributeRow: React.FC<CustomAttributeRowProps> = ({
  rowId,
  value,
  shopAttributes,
  i18n,
  disabled = false,
  hideHelpColumn = false,
  onChange,
  onRemove,
  onFetchShopAttributeValues
}) => {
  // State for freetext inputs
  const [customNameInput, setCustomNameInput] = React.useState(value?.customName || '');
  const [customValueInput, setCustomValueInput] = React.useState(value?.customValue || '');

  // Sync state when value prop changes (e.g., when saved data is loaded)
  React.useEffect(() => {
    if (value?.customName !== undefined) {
      setCustomNameInput(value.customName);
    }
  }, [value?.customName]);

  React.useEffect(() => {
    if (value?.customValue !== undefined) {
      setCustomValueInput(value.customValue);
    }
  }, [value?.customValue]);

  // State for shop attribute values (when VALUE shop attribute is type 'select')
  const [shopAttributeValues, setShopAttributeValues] = React.useState<{ [key: string]: string }>({});
  const [isLoadingValues, setIsLoadingValues] = React.useState(false);

  // Check if freetext is selected
  const isFreetextName = value?.attributeCode === 'freetext';
  const isFreetextValue = value?.valueCode === 'freetext';

  /**
   * Find the selected shop attribute for VALUE column
   * Returns the ShopAttribute if found, null otherwise
   */
  const selectedValueShopAttribute = React.useMemo((): ShopAttribute | null => {
    if (!value?.valueCode || value.valueCode === 'freetext') {
      return null;
    }

    for (const [, group] of Object.entries(shopAttributes)) {
      if (typeof group === 'object' && group.optGroupClass) {
        for (const [attrKey, attr] of Object.entries(group)) {
          if (attrKey === value.valueCode && typeof attr === 'object') {
            return attr as ShopAttribute;
          }
        }
      }
    }

    return null;
  }, [value?.valueCode, shopAttributes]);

  // Check if we should show the matching table (when VALUE shop attribute exposes a value
  // list: 'select' / 'selectAndText' / 'multiSelect') — see OTRS #607858
  const shouldShowMatchingTable = isValueListType(selectedValueShopAttribute?.type);

  /**
   * Fetch shop attribute values when the VALUE shop attribute changes and exposes a value
   * list ('select' / 'selectAndText' / 'multiSelect') — see OTRS #607858
   */
  React.useEffect(() => {
    if (!value?.valueCode || value.valueCode === 'freetext' || !shouldShowMatchingTable || !onFetchShopAttributeValues) {
      setShopAttributeValues({});
      return;
    }

    setIsLoadingValues(true);
    onFetchShopAttributeValues(value.valueCode)
      .then(values => {
        setShopAttributeValues(values || {});
      })
      .catch(error => {
        console.error('Failed to fetch shop attribute values:', error);
        setShopAttributeValues({});
      })
      .finally(() => {
        setIsLoadingValues(false);
      });
  }, [value?.valueCode, shouldShowMatchingTable, onFetchShopAttributeValues]);

  /**
   * Build shop attribute options for the NAME dropdown
   */
  const nameOptions = React.useMemo((): SelectOptions[] => {
    const options: SelectOptions[] = [];

    // Add freetext option at the top
    options.push({
      label: i18n.makeCustomEntry || 'Eigene Angaben machen',
      options: [{
        value: 'freetext',
        label: i18n.enterCustomMarketplaceName || 'Eigenen Namen eingeben...'
      }]
    });

    // Add shop attribute groups
    for (const [groupKey, group] of Object.entries(shopAttributes)) {
      if (typeof group === 'object' && group.optGroupClass) {
        const groupOptions: SelectOption[] = [];

        for (const [attrKey, attr] of Object.entries(group)) {
          if (attrKey === 'optGroupClass') continue;

          const shopAttr = attr as ShopAttribute;
          if (typeof shopAttr === 'object' && shopAttr.name) {
            groupOptions.push({
              value: attrKey,
              label: shopAttr.name
            });
          }
        }

        if (groupOptions.length > 0) {
          options.push({
            label: group.optGroupClass || groupKey,
            options: groupOptions
          });
        }
      }
    }

    return options;
  }, [shopAttributes, i18n]);

  // Note: For eBay custom attributes, both NAME and VALUE columns show shop attributes
  // No need to fetch attribute values - we always show the shop attributes list

  /**
   * Handle attribute name selection change
   */
  const handleNameChange = (option: SingleValue<SelectOption>) => {
    if (!option) return;

    onChange(rowId, {
      attributeCode: option.value,
      customName: option.value === 'freetext' ? customNameInput : undefined,
      valueCode: '',
      customValue: undefined
    });
  };

  /**
   * Handle attribute value selection change
   */
  const handleValueChange = (option: SingleValue<SelectOption>) => {
    if (!option) return;

    onChange(rowId, {
      ...value,
      attributeCode: value?.attributeCode || '',
      valueCode: option.value,
      customValue: option.value === 'freetext' ? customValueInput : undefined
    });
  };

  /**
   * Handle custom name input change
   */
  const handleCustomNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const newName = e.target.value;
    setCustomNameInput(newName);

    onChange(rowId, {
      ...value,
      attributeCode: 'freetext',
      customName: newName,
      valueCode: value?.valueCode || '',
      customValue: value?.customValue
    });
  };

  /**
   * Handle custom value input change
   */
  const handleCustomValueChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const newValue = e.target.value;
    setCustomValueInput(newValue);

    onChange(rowId, {
      ...value,
      attributeCode: value?.attributeCode || '',
      valueCode: 'freetext',
      customValue: newValue
    });
  };

  /**
   * Handle value matchings change (when VALUE shop attribute is type 'select')
   */
  const handleMatchingsChange = (matchings: MatchingValue[]) => {
    onChange(rowId, {
      ...value,
      attributeCode: value?.attributeCode || '',
      valueCode: value?.valueCode || '',
      matchings,
      useShopValues: value?.useShopValues ?? true
    });
  };

  /**
   * Handle "Use shop values" checkbox change
   */
  const handleUseShopValuesChange = (useShopValues: boolean) => {
    onChange(rowId, {
      ...value,
      attributeCode: value?.attributeCode || '',
      valueCode: value?.valueCode || '',
      useShopValues,
      // When checked, clear matchings; when unchecked, keep current matchings
      matchings: useShopValues ? [] : (value?.matchings || [])
    });
  };

  /**
   * Handle DateFormat change for date-type metafield attributes
   */
  const handleDateFormatChange = (format: string) => {
    onChange(rowId, {
      ...value,
      attributeCode: value?.attributeCode || '',
      valueCode: value?.valueCode || '',
      dateFormat: format
    });
  };

  /**
   * Create a "fake" marketplace attribute for ValueMatchingTable
   * Since eBay custom attributes don't have predefined values,
   * we use 'text' type which allows freetext entries
   */
  const fakeMarketplaceAttribute = React.useMemo(() => ({
    value: 'Custom Attribute',
    required: false,
    dataType: 'text' as const,
    freetext: true,
    values: undefined // No predefined values - allows freetext
  }), []);

  /**
   * Custom styles for react-select
   */
  const selectStyles: StylesConfig<SelectOption, false> = {
    control: (base) => ({
      ...base,
      minHeight: '32px',
      fontSize: '13px'
    }),
    option: (base) => ({
      ...base,
      fontSize: '13px',
      padding: '6px 12px'
    }),
    groupHeading: (base) => ({
      ...base,
      fontSize: '11px',
      fontWeight: 600,
      textTransform: 'uppercase',
      color: '#666'
    })
  };

  /**
   * Find current option by value
   */
  const findOption = (options: SelectOptions[], val: string): SelectOption | null => {
    for (const opt of options) {
      if ('options' in opt) {
        const found = opt.options.find(o => o.value === val);
        if (found) return found;
      } else if (opt.value === val) {
        return opt;
      }
    }
    return null;
  };

  const selectedNameOption = value?.attributeCode ? findOption(nameOptions, value.attributeCode) : null;
  // For eBay custom attributes, VALUE dropdown ALWAYS shows shop attributes (nameOptions)
  // Both NAME and VALUE select from the same shop attributes list
  const selectedValueOption = value?.valueCode
    ? findOption(nameOptions, value.valueCode)
    : null;

  return (
    <tr className="js-field custom-attribute-row" data-row-id={rowId}>
      {/* Column 1: Attribute NAME (matches AttributeRow structure) */}
      <th className="attribute-name-column custom-attribute-name-column">
        <div className="custom-attribute-name-container">
          <Select<SelectOption, false>
            classNamePrefix="ml-custom-attr-name"
            options={nameOptions as SelectOption[]}
            value={selectedNameOption}
            onChange={handleNameChange}
            isDisabled={disabled}
            placeholder={i18n.selectAttribute || 'Attribut wählen...'}
            styles={selectStyles}
            isClearable={false}
            isSearchable={true}
          />

          {/* Freetext input for custom name */}
          {isFreetextName && (
            <input
              type="text"
              className="custom-name-input"
              value={customNameInput}
              onChange={handleCustomNameChange}
              placeholder={i18n.enterAttributeName || 'Attributname eingeben...'}
              disabled={disabled}
              style={{
                marginTop: '8px',
                width: '100%',
                padding: '6px 8px',
                fontSize: '13px',
                border: '1px solid #ccc',
                borderRadius: '4px'
              }}
            />
          )}
        </div>
      </th>

      {/* Column 2: Help column (empty, hidden when hideHelpColumn=true) */}
      {!hideHelpColumn && (
        <td className="mlhelp ml-js-noBlockUi help-column"></td>
      )}

      {/* Column 3: Attribute VALUE + Remove button (matches AttributeRow structure) */}
      <td className="input attribute-input-column custom-attribute-value-column">
        <div className="shop-attribute-container custom-attribute-value-container">
          {/* For eBay custom attributes, VALUE column ALWAYS shows shop attributes list
              Both NAME and VALUE columns select from shop attributes:
              - NAME: Which shop attribute provides the attribute name
              - VALUE: Which shop attribute provides the attribute value */}
          <Select<SelectOption, false>
            classNamePrefix="ml-custom-attr-value"
            options={nameOptions as SelectOption[]}
            value={selectedValueOption}
            onChange={handleValueChange}
            isDisabled={disabled}
            placeholder={i18n.selectValue || 'Wert wählen...'}
            styles={selectStyles}
            isClearable={false}
            isSearchable={true}
          />

          {/* Remove button INSIDE the value column (same as optional attributes) */}
          {onRemove && (
            <button
              type="button"
              className="mlbtn action remove-matching-row"
              onClick={() => onRemove(rowId)}
              disabled={disabled}
              title={i18n.removeAttribute || 'Attribut entfernen'}
            >
              -
            </button>
          )}
        </div>

        {/* Freetext input for custom value (when freetext is selected in VALUE dropdown) */}
        {isFreetextValue && (
          <input
            type="text"
            className="custom-value-input"
            value={customValueInput}
            onChange={handleCustomValueChange}
            placeholder={i18n.enterAttributeValue || 'Wert eingeben...'}
            disabled={disabled}
            style={{
              marginTop: '8px',
              width: '100%',
              padding: '6px 8px',
              fontSize: '13px',
              border: '1px solid #ccc',
              borderRadius: '4px'
            }}
          />
        )}

        {/* Value Matching Table (when VALUE shop attribute is type 'select') */}
        {shouldShowMatchingTable && selectedValueShopAttribute && value?.valueCode && (
          <ValueMatchingTable
            attributeKey={rowId}
            amazonAttribute={fakeMarketplaceAttribute}
            shopAttribute={{
              ...selectedValueShopAttribute,
              values: shopAttributeValues
            }}
            shopAttributeCode={value.valueCode}
            variationGroup=""
            currentMatchings={value?.matchings || []}
            disabled={disabled || isLoadingValues}
            debugMode={false}
            useShopValues={value?.useShopValues ?? true}
            i18n={i18n}
            onMatchingsChange={handleMatchingsChange}
            onUseShopValuesChange={handleUseShopValuesChange}
            onFetchShopAttributeValues={onFetchShopAttributeValues}
          />
        )}

        {/* Date format selector for date-type metafield attributes */}
        {selectedValueShopAttribute &&
            (['date', 'date_time', 'datetime'].includes(selectedValueShopAttribute.shopMetaFieldType || '')) && (
            <div className="date-format-selector" style={{ marginTop: '10px' }}>
              <label style={{ marginRight: '8px' }}>
                {i18n.dateFormatLabel || 'Date format'}
              </label>
              <select
                  value={value?.dateFormat || 'Y-m-d'}
                  onChange={(e) => handleDateFormatChange(e.target.value)}
                  disabled={disabled}
              >
                <option value="Y-m-d">{i18n.dateFormatIso || 'YYYY-MM-DD (ISO)'}</option>
                <option value="d.m.Y">{i18n.dateFormatDe || 'DD.MM.YYYY'}</option>
                <option value="m/d/Y">{i18n.dateFormatUs || 'MM/DD/YYYY'}</option>
                <option value="d/m/Y">{i18n.dateFormatUk || 'DD/MM/YYYY'}</option>
              </select>
            </div>
        )}
      </td>

      {/* Column 4: Description/help column (empty for custom attributes) */}
      <td className="description-column"></td>
    </tr>
  );
};

export default CustomAttributeRow;
