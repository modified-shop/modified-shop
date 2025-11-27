import React from 'react';
import Select from 'react-select';
import {I18nStrings, ShopAttribute, ShopAttributes} from '../../../types';
import {SELECT_CONFIGS} from '../config/selectConfig';

interface AttributeSelectorProps {
  attributeKey: string;
  variationGroup: string;
  marketplaceName: string;
  shopAttributes: ShopAttributes;
  selectedCode?: string;
  dataType?: string;
  hasAmazonValues?: boolean; // Whether Amazon attribute has predefined values
  i18n: I18nStrings;
  disabled?: boolean;
  debugMode?: boolean;
  hasError?: boolean; // Whether this field has a validation error
  onChange: (value: string) => void;
}


/**
 * AttributeSelector Component
 *
 * Renders a searchable select dropdown for choosing shop attributes
 * Uses react-select when there are many options, falls back to native select for fewer options
 */
const AttributeSelector: React.FC<AttributeSelectorProps> = ({
  attributeKey,
  variationGroup,
  marketplaceName,
  shopAttributes,
  selectedCode,
  dataType,
  hasAmazonValues = true,
  i18n,
  disabled = false,
  debugMode = false,
  hasError = false,
  onChange
}) => {
  const fieldId = `${marketplaceName.toLowerCase()}_prepare_variations_field_variationgroups_${variationGroup}_${attributeKey}_code`;

  /**
   * Red Border for Not-Matched Mandatory Attributes
   *
   * When hasError is true, a static red border is applied via CSS.
   * No animation needed - just a clear visual indicator.
   */

  // Prepare options for react-select
  const selectOptions = React.useMemo(() => {
    const simpleOptions: Array<{ value: string; label: string; isDisabled?: boolean }> = [];
    const groupedOptions: Array<{ label: string; options: Array<{ value: string; label: string; isDisabled?: boolean }> }> = [];

    // Determine if freetext should be disabled
    // Freetext is disabled when Amazon attribute is type "select"
    // When Amazon expects a select value, users cannot enter free text
    const isFreetextDisabled = dataType === 'select';

    // Determine if "Use Amazon attribute value" should be disabled
    // This option is disabled when Amazon attribute is type "text"
    // When Amazon expects text input, there are no predefined Amazon values to select
    const isAttributeValueDisabled = dataType === 'text';

    // Process shop attributes - separate simple options from grouped options
    Object.entries(shopAttributes).forEach(([groupName, group]) => {
      if (typeof group === 'object') {
        // Check if this is a grouped option (has optGroupClass)
        if (group.optGroupClass) {
          const groupOptions: Array<{ value: string; label: string; isDisabled?: boolean }> = [];

          Object.entries(group).forEach(([key, attr]) => {
            if (key !== 'optGroupClass' && typeof attr === 'object') {
              const attribute = attr as ShopAttribute;

              // Applying disable logic
              let isDisabled = false;

              // Disable freetext when Amazon type is "select"
              if (key === 'freetext') {
                isDisabled = isFreetextDisabled;
              }
              // Disable attribute_value when Amazon type is "text"
              else if (key === 'attribute_value') {
                isDisabled = isAttributeValueDisabled;
              }
              // When Amazon type is "select", only allow shop attributes with type "select"
              else if (dataType === 'select' && attribute.type && attribute.type !== 'select') {
                isDisabled = true;
              }

              groupOptions.push({
                value: key,
                label: debugMode ? `${attribute.name} [${key}]` : attribute.name,
                isDisabled: isDisabled
              });
            }
          });

          if (groupOptions.length > 0) {
            groupedOptions.push({
              label: groupName,
              options: groupOptions
            });
          }
        } else {
          // This is a simple option (no optGroupClass)
          // groupName is the key, group is the attribute object
          const optionValue = groupName;
          const attribute = group as unknown as ShopAttribute;
          const optionLabel = attribute.name || groupName;

          simpleOptions.push({
            value: optionValue,
            label: debugMode && optionValue ? `${optionLabel} [${optionValue}]` : optionLabel
          });
        }
      } else if (typeof group === 'string') {
        // Handle case where group is a simple string value
        simpleOptions.push({
          value: groupName,
          label: debugMode && groupName ? `${group} [${groupName}]` : group
        });
      }
    });

    return {
      simpleOptions: simpleOptions,
      flatOptions: [...simpleOptions, ...groupedOptions.flatMap(g => g.options)],
      groupedOptions: groupedOptions
    };
  }, [shopAttributes, i18n, debugMode, dataType, hasAmazonValues]);

  const totalOptions = selectOptions.flatOptions.length;
  const useSearchableSelect = totalOptions >= SELECT_CONFIGS.ATTRIBUTE_SELECTOR.threshold;

  // Find selected option for react-select
  const selectedOption = React.useMemo(() => {
    return selectOptions.flatOptions.find(option => option.value === selectedCode) || null;
  }, [selectOptions.flatOptions, selectedCode]);

  // Handle change for react-select
  const handleSelectChange = (option: any) => {
    onChange(option?.value || '');
  };


  if (useSearchableSelect) {
    // Combine simple options with grouped options for React-Select
    const reactSelectOptions = [
      ...selectOptions.simpleOptions,
      ...selectOptions.groupedOptions
    ];

    return (
      <div className="attribute-selector-container">
        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
          <label className="attribute-selector-label" style={{ margin: 0, whiteSpace: 'nowrap' }}>
            {i18n.webShopAttribute || 'Web-Shop Attribute'}
          </label>
          <Select
            inputId={fieldId}
            options={reactSelectOptions}
            value={selectedOption}
            onChange={handleSelectChange}
            isDisabled={disabled}
            isSearchable={true}
            isClearable={false}
            placeholder={i18n.pleaseSelect || 'Please select...'}
            noOptionsMessage={() => 'No options found'}
            styles={{
              ...SELECT_CONFIGS.ATTRIBUTE_SELECTOR.styles,
              container: (provided: any) => ({
                ...provided,
                minWidth: 'fit-content',
                width: 'auto'
              }),
              control: (provided: any, state: any) => ({
                ...SELECT_CONFIGS.ATTRIBUTE_SELECTOR.styles.control(provided, state),
                minWidth: '200px',
                width: 'auto',
                borderColor: hasError ? '#e31a1c' : (provided.borderColor || '#ccc')
              })
            }}
            className={`attribute-selector-react-select ${hasError ? 'has-error' : ''}`}
            classNamePrefix="ml-amazon-attr-selector"
          />
        </div>
      </div>
    );
  }

  // Fallback to native select for smaller option lists
  return (
    <div className="attribute-selector-container">
      <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
        <label htmlFor={fieldId} style={{ margin: 0, whiteSpace: 'nowrap' }}>
          {i18n.webShopAttribute || 'Web-Shop Attribute'}
        </label>
        <select
          id={fieldId}
          value={selectedCode || ''}
          onChange={(e) => onChange(e.target.value)}
          disabled={disabled}
          className={`attribute-selector ${hasError ? 'has-error' : ''}`}
          style={{
            minWidth: '200px',
            width: 'auto',
            borderColor: hasError ? '#e31a1c' : undefined
          }}
        >
          {/* Simple options (including "Please select...") */}
          {selectOptions.simpleOptions.map((option, index) => (
            <option key={`simple-${index}`} value={option.value} disabled={option.isDisabled || false}>
              {option.label}
            </option>
          ))}

          {/* Grouped options from backend */}
          {selectOptions.groupedOptions.map((group: any, groupIndex: number) => (
            <optgroup key={`group-${groupIndex}`} label={group.label}>
              {group.options.map((option: any, optionIndex: number) => (
                <option
                  key={`option-${groupIndex}-${optionIndex}`}
                  value={option.value}
                  disabled={option.isDisabled || false}
                >
                  {option.label}
                </option>
              ))}
            </optgroup>
          ))}
        </select>
      </div>
    </div>
  );
};

export default AttributeSelector;