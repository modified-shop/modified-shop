import React from 'react';
import Select from 'react-select';
import {I18nStrings, MarketplaceAttributes} from '../../../types';
import {SELECT_CONFIGS} from '../config/selectConfig';

interface OptionalAttributeSelectorProps {
  availableOptionalAttributes: Array<{
    key: string;
    attribute: MarketplaceAttributes[string];
  }>;
  i18n: I18nStrings;
  onAttributeSelect: (attributeKey: string) => void;
  disabled?: boolean;
  debugMode?: boolean;
  className?: string;
}

/**
 * OptionalAttributeSelector Component
 *
 * Allows users to select additional optional attributes to add to the matching interface
 * Only shows optional attributes that haven't been added yet
 */
const OptionalAttributeSelector: React.FC<OptionalAttributeSelectorProps> = ({
  availableOptionalAttributes,
  i18n,
  onAttributeSelect,
  disabled = false,
  debugMode = false,
  className = ''
}) => {
  // Helper function to strip HTML tags from description
  const stripHtmlTags = (html: string | undefined): string | undefined => {
    if (!html) return undefined;
    // Create a temporary element to parse HTML
    const temp = document.createElement('div');
    temp.innerHTML = html;
    // Get text content without HTML tags
    const text = temp.textContent || temp.innerText || '';
    return text.trim() || undefined;
  };

  // Prepare options for the selector
  const selectOptions = React.useMemo(() => {
    return availableOptionalAttributes.map(({ key, attribute }) => ({
      value: key,
      label: debugMode ? `${attribute.value || key} [${key}]` : (attribute.value || key),
      description: stripHtmlTags(attribute.desc)
    }));
  }, [availableOptionalAttributes, debugMode]);

  // Handle attribute selection
  const handleAttributeSelect = (option: any) => {
    if (option?.value) {
      onAttributeSelect(option.value);
    }
  };

  // Don't render if no available attributes
  if (selectOptions.length === 0) {
    return null;
  }

  const useSearchableSelect = selectOptions.length >= SELECT_CONFIGS.ATTRIBUTE_SELECTOR.threshold;

  // Custom option component to show description
  const CustomOption = (props: any) => {
    const { data, innerRef, innerProps } = props;
    return (
      <div
        ref={innerRef}
        {...innerProps}
        style={{
          padding: '8px 12px',
          cursor: 'pointer',
          backgroundColor: props.isFocused ? '#f8f9fa' : 'white',
          color: '#495057'
        }}
      >
        <div style={{ fontWeight: 500 }}>{data.label}</div>
        {data.description && (
          <div style={{ fontSize: '12px', color: '#6c757d', marginTop: '2px' }}>
            {data.description}
          </div>
        )}
      </div>
    );
  };

  if (useSearchableSelect) {
    return (
      <div className={`optional-attribute-selector-container ${className}`}>
        <label className="optional-attribute-selector-label">
          {i18n.addOptionalAttribute || 'Add Optional Attribute'}
        </label>
        <Select
          options={selectOptions}
          value={null} // Always null so it acts as a pure selector
          onChange={handleAttributeSelect}
          isDisabled={disabled}
          isSearchable={true}
          isClearable={false}
          placeholder={i18n.selectOptionalAttribute || 'Select an optional attribute to add...'}
          noOptionsMessage={() => i18n.noMoreOptionalAttributes || 'No more optional attributes available'}
          styles={SELECT_CONFIGS.ATTRIBUTE_SELECTOR.styles}
          components={{ Option: CustomOption }}
          className="optional-attribute-react-select"
          classNamePrefix="ml-amazon-optional-attr"
        />
      </div>
    );
  }

  // Fallback to native select for smaller lists
  return (
    <div className={`optional-attribute-selector-container ${className}`}>
      <label className="optional-attribute-selector-label">
        {i18n.addOptionalAttribute || 'Add Optional Attribute'}
      </label>
      <select
        value=""
        onChange={(e) => {
          if (e.target.value) {
            onAttributeSelect(e.target.value);
            e.target.value = ''; // Reset selection
          }
        }}
        disabled={disabled}
        className="optional-attribute-selector"
      >
        <option value="">
          {i18n.selectOptionalAttribute || 'Select an optional attribute to add...'}
        </option>
        {selectOptions.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
    </div>
  );
};

export default OptionalAttributeSelector;