import React, {useCallback, useEffect, useMemo, useState} from 'react';
import Select from 'react-select';
import {
    AmazonVariationsProps,
    MarketplaceAttribute,
    SavedValues,
    SelectOption,
    SelectOptions,
    ShopAttribute,
    ValidationError
} from './types';

/**
 * Amazon Variations React Component - Simplified Version
 *
 * A working, production-ready version of the Amazon attribute variations component
 */
const AmazonVariationsSimple: React.FC<AmazonVariationsProps> = ({
  variationGroup,
  customIdentifier,
  marketplaceName = 'Amazon',
  shopAttributes = {},
  marketplaceAttributes = {},
  savedValues = {},
  i18n = {},
  onValuesChange,
  onValidationError,
  className,
  disabled = false
}) => {
  const [attributeValues, setAttributeValues] = useState<SavedValues>(savedValues);
  const [validationErrors, setValidationErrors] = useState<ValidationError[]>([]);

  // Initialize component state
  useEffect(() => {
    setAttributeValues(savedValues);
  }, [savedValues]);

  // Notify parent of value changes
  useEffect(() => {
    onValuesChange?.(attributeValues);
  }, [attributeValues, onValuesChange]);

  // Get shop attribute options
  const getShopAttributeOptions = useCallback((dataType?: string): SelectOptions => {
    const options: SelectOptions = [
      { value: '', label: i18n.dontUse || "Don't use" }
    ];

    Object.entries(shopAttributes).forEach(([groupName, group]) => {
      if (typeof group === 'object' && group.optGroupClass) {
        const groupOptions: SelectOption[] = [];

        Object.entries(group).forEach(([attrKey, attr]) => {
          if (attrKey !== 'optGroupClass' && typeof attr === 'object') {
            const shopAttr = attr as ShopAttribute;
            groupOptions.push({
              value: attrKey,
              label: shopAttr.name,
              type: shopAttr.type
            });
          }
        });

        if (groupOptions.length > 0) {
          options.push({
            label: groupName,
            options: groupOptions
          });
        }
      }
    });

    // Add special options
    options.push({
      label: i18n.additionalOptions || 'Additional Options',
      options: [
        { value: 'freetext', label: i18n.freetext || 'Enter custom value' },
        { value: 'attribute_value', label: i18n.useAttributeValue || 'Use Amazon attribute value' }
      ]
    });

    return options;
  }, [shopAttributes, i18n]);

  // Handle shop attribute selection change
  const handleShopAttributeChange = useCallback((attributeKey: string, selectedOption: SelectOption | null) => {
    const newValue = selectedOption ? selectedOption.value : '';

    setAttributeValues(prev => ({
      ...prev,
      [attributeKey]: {
        ...prev[attributeKey],
        Code: newValue,
        Values: undefined
      }
    }));
  }, []);

  // Handle freetext input change
  const handleFreetextChange = useCallback((attributeKey: string, value: string) => {
    setAttributeValues(prev => ({
      ...prev,
      [attributeKey]: {
        ...prev[attributeKey],
        Values: { FreeText: value }
      }
    }));
  }, []);

  // Validate required fields
  const validateRequiredFields = useCallback((): ValidationError[] => {
    const errors: ValidationError[] = [];

    Object.entries(marketplaceAttributes).forEach(([key, attribute]) => {
      if (attribute.required && !attributeValues[key]?.Code) {
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

  // Validate on mount and when values change
  useEffect(() => {
    validateRequiredFields();
  }, [validateRequiredFields]);

  // Get flat options from nested structure
  const getFlatOptions = useCallback((options: SelectOptions): SelectOption[] => {
    return options.flatMap(opt =>
      'options' in opt ? opt.options : [opt]
    );
  }, []);

  // Render attribute row
  const renderAttributeRow = useCallback((attributeKey: string, attribute: MarketplaceAttribute, isRequired = false) => {
    const fieldId = `${marketplaceName.toLowerCase()}_prepare_variations_field_variationgroups_${variationGroup}_${attributeKey}_code`;
    const currentValue = attributeValues[attributeKey];
    const selectedShopAttr = currentValue?.Code;
    const options = getShopAttributeOptions(attribute.dataType);
    const flatOptions = getFlatOptions(options);

    return (
      <tr key={attributeKey} className={`js-field ${!isRequired ? 'optionalAttribute' : ''}`}>
        <th>
          <label htmlFor={fieldId}>
            {attribute.value || attributeKey}
            {isRequired && (
              <span style={{ color: '#e31a1c', fontSize: '18px', marginLeft: '5px' }}>•</span>
            )}
          </label>
        </th>
        <td className="mlhelp ml-js-noBlockUi"></td>
        <td className="input">
          <div style={{ marginBottom: '10px' }}>
            <label htmlFor={fieldId}>{i18n.webShopAttribute || 'Web-Shop Attribute'}</label>
            <Select
              id={fieldId}
              options={options}
              value={flatOptions.find(opt => opt.value === selectedShopAttr) || null}
              onChange={(selectedOption) => handleShopAttributeChange(attributeKey, selectedOption)}
              placeholder={i18n.pleaseSelect || 'Please select...'}
              isSearchable
              isDisabled={disabled}
              styles={{ container: (base) => ({ ...base, width: '100%' }) }}
            />
          </div>

          {/* Render input based on selection */}
          {selectedShopAttr === 'freetext' && (
            <div>
              <input
                type="text"
                style={{ width: '100%', padding: '8px' }}
                placeholder={i18n.enterFreetext || 'Enter custom value'}
                value={(currentValue?.Values as any)?.FreeText || ''}
                onChange={(e) => handleFreetextChange(attributeKey, e.target.value)}
                disabled={disabled}
              />
            </div>
          )}

          {selectedShopAttr === 'attribute_value' && (
            <div>
              {attribute.values ? (
                <Select
                  options={Object.entries(attribute.values).map(([key, value]) => ({
                    value: key,
                    label: value
                  }))}
                  onChange={(selectedOption) => {
                    setAttributeValues(prev => ({
                      ...prev,
                      [attributeKey]: {
                        ...prev[attributeKey],
                        Values: { AttributeValue: selectedOption?.value || '' }
                      }
                    }));
                  }}
                  placeholder={i18n.selectAmazonValue || 'Select Amazon value...'}
                  isSearchable
                  isDisabled={disabled}
                />
              ) : (
                <input
                  type="text"
                  style={{ width: '100%', padding: '8px' }}
                  placeholder={i18n.enterAmazonValue || 'Enter Amazon value'}
                  disabled={disabled}
                />
              )}
            </div>
          )}
        </td>
        <td className="info">{attribute.desc || ''}</td>
      </tr>
    );
  }, [
    marketplaceName,
    variationGroup,
    attributeValues,
    getShopAttributeOptions,
    getFlatOptions,
    i18n,
    disabled,
    handleShopAttributeChange,
    handleFreetextChange
  ]);

  // Separate required and optional attributes
  const requiredAttributes = useMemo(() =>
    Object.entries(marketplaceAttributes).filter(([key, attr]) => attr.required === true),
    [marketplaceAttributes]
  );

  const optionalAttributes = useMemo(() =>
    Object.entries(marketplaceAttributes).filter(([key, attr]) => attr.required !== true),
    [marketplaceAttributes]
  );

  // Don't render if no variation group
  if (!variationGroup || variationGroup === 'none' || variationGroup === 'new') {
    return null;
  }

  return (
    <div className={`amazon-variations-container ${className || ''}`}>
      {/* Validation Errors */}
      {validationErrors.length > 0 && (
        <div className="noticeBox ml-error-box" style={{
          padding: '15px',
          margin: '15px 0',
          backgroundColor: '#f8d7da',
          border: '1px solid #f5c6cb',
          borderRadius: '4px',
          color: '#721c24'
        }}>
          <strong>{i18n.fixErrors || 'Please fix the following errors:'}</strong>
          <ul style={{ margin: '10px 0 0 20px' }}>
            {validationErrors.map((error, index) => (
              <li key={index}>
                {error.name}: {error.message}
              </li>
            ))}
          </ul>
        </div>
      )}

      {/* Amazon Attributes Table */}
      <table className="attributesTable ml-js-attribute-matching" style={{ width: '100%', borderCollapse: 'collapse' }}>
        {/* Required Attributes Section */}
        <tbody>
          <tr className="headline">
            <td colSpan={2} style={{ backgroundColor: '#f8f9fa', padding: '10px', textAlign: 'center' }}>
              <h4>{i18n.requiredAttributesTitle || `${marketplaceName} Required Attributes`}</h4>
            </td>
            <td colSpan={2} style={{ backgroundColor: '#f8f9fa', padding: '10px', textAlign: 'center' }}>
              <h4>{i18n.attributesMatchingTitle || 'Attributes Matching'}</h4>
            </td>
          </tr>

          {requiredAttributes.map(([key, attribute]) =>
            renderAttributeRow(key, attribute, true)
          )}
        </tbody>

        {/* Optional Attributes Section */}
        {optionalAttributes.length > 0 && (
          <tbody>
            <tr className="headline">
              <td colSpan={2} style={{ backgroundColor: '#f8f9fa', padding: '10px', textAlign: 'center' }}>
                <h4>{i18n.optionalAttributesTitle || `${marketplaceName} Optional Attributes`}</h4>
              </td>
              <td colSpan={2} style={{ backgroundColor: '#f8f9fa', padding: '10px', textAlign: 'center' }}>
                <h4>{i18n.optionalAttributeMatching || 'Optional Attribute Matching'}</h4>
              </td>
            </tr>

            {optionalAttributes.map(([key, attribute]) =>
              renderAttributeRow(key, attribute, false)
            )}
          </tbody>
        )}
      </table>

      <p style={{ marginTop: '20px', fontSize: '14px', color: '#666' }}>
        {i18n.mandatoryFieldsInfo || `Fields with • are mandatory fields from ${marketplaceName}.`}
      </p>
    </div>
  );
};

export default React.memo(AmazonVariationsSimple);