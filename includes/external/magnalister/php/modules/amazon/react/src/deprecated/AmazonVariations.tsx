import React, {useCallback, useEffect, useMemo, useState} from 'react';
import Select from 'react-select';
import classNames from 'classnames';
import debounce from 'lodash.debounce';
import {
    AmazonVariationsProps,
    MarketplaceAttribute,
    MatchingValue,
    SavedValues,
    SelectOption,
    SelectOptions,
    ShopAttribute,
    ValidationError
} from './types';

/**
 * Amazon Variations React Component
 *
 * A sophisticated attribute matching interface for Amazon marketplace integration.
 * Converted from PHP to modern React with TypeScript support.
 */
const AmazonVariations: React.FC<AmazonVariationsProps> = ({
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
  // Debug logging
  console.log('AmazonVariations component rendered with props:', {
    variationGroup,
    customIdentifier,
    marketplaceName,
    shopAttributes,
    marketplaceAttributes,
    savedValues,
    i18n
  });

  // State management
  const [attributeValues, setAttributeValues] = useState<SavedValues>(savedValues);
  const [optionalAttributeVisibility, setOptionalAttributeVisibility] = useState<Record<string, boolean>>({});
  const [validationErrors, setValidationErrors] = useState<ValidationError[]>([]);
  const [isLoading, setIsLoading] = useState(false);

  // Initialize component state
  useEffect(() => {
    setAttributeValues(savedValues);

    // Initialize visibility for optional attributes that have saved values
    const visibility: Record<string, boolean> = {};
    Object.entries(marketplaceAttributes).forEach(([key, attribute]) => {
      if (!attribute.required && savedValues[key]) {
        visibility[key] = true;
      }
    });
    setOptionalAttributeVisibility(visibility);
  }, [savedValues, marketplaceAttributes]);

  // Debounced callback for external value change notifications
  const debouncedOnValuesChange = useMemo(
    () => onValuesChange ? debounce(onValuesChange, 300) : undefined,
    [onValuesChange]
  );

  // Notify parent of value changes
  useEffect(() => {
    debouncedOnValuesChange?.(attributeValues);
  }, [attributeValues, debouncedOnValuesChange]);

  // Helper function to generate field IDs
  const generateFieldId = useCallback((attributeKey: string): string => {
    return `${marketplaceName.toLowerCase()}_prepare_variations_field_variationgroups_${variationGroup}_${attributeKey}_code`;
  }, [marketplaceName, variationGroup]);

  // Helper function to check if attribute is disabled based on data type compatibility
  const isAttributeDisabled = useCallback((shopAttrType: string, amazonDataType: string): boolean => {
    if (!amazonDataType) return false;

    const dataType = amazonDataType.toLowerCase();

    if (['select', 'multiselect'].includes(dataType)) {
      return shopAttrType === 'text';
    }

    if (dataType === 'text') {
      return shopAttrType === 'attribute_value';
    }

    return false;
  }, []);

  // Convert shop attributes to select options
  const getShopAttributeOptions = useCallback((dataType?: string): SelectOptions => {
    console.log('getShopAttributeOptions called with dataType:', dataType);
    console.log('shopAttributes:', shopAttributes);

    const options: SelectOptions = [
      { value: '', label: i18n.dontUse || "Don't use", isDisabled: false }
    ];

    Object.entries(shopAttributes).forEach(([groupName, group]) => {
      if (typeof group === 'object' && group.optGroupClass) {
        const groupOptions: SelectOption[] = [];

        Object.entries(group).forEach(([attrKey, attr]) => {
          if (attrKey !== 'optGroupClass' && typeof attr === 'object') {
            const shopAttr = attr as ShopAttribute;
            const disabled = isAttributeDisabled(shopAttr.type, dataType || '');
            groupOptions.push({
              value: attrKey,
              label: shopAttr.name,
              isDisabled: disabled,
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
    // @ts-ignore
      // @ts-ignore
      options.push({
      label: i18n.additionalOptions || 'Additional Options',
      options: [
        { value: 'freetext', label: i18n.freetext || 'Enter custom value' },
        {
          value: 'attribute_value',
          label: i18n.useAttributeValue || 'Use Amazon attribute value',
          isDisabled: Boolean(dataType && !['text', 'selectandtext'].includes(dataType.toLowerCase()))
        }
      ]
    });

    console.log('getShopAttributeOptions returning:', options);
    return options;
  }, [shopAttributes, i18n, isAttributeDisabled]);

  // Handle shop attribute selection change
  const handleShopAttributeChange = useCallback((attributeKey: string, selectedOption: SelectOption | null) => {
    console.log('handleShopAttributeChange called:', attributeKey, selectedOption);
    const newValue = selectedOption ? selectedOption.value : '';

    setAttributeValues(prev => ({
      ...prev,
      [attributeKey]: {
        ...prev[attributeKey],
        Code: newValue,
        // Clear dependent values when changing attribute type
        Values: undefined,
        FreeTextValue: undefined,
        AttributeValue: undefined
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

  // Handle attribute value selection change
  const handleAttributeValueChange = useCallback((attributeKey: string, selectedOption: SelectOption | null) => {
    const value = selectedOption ? selectedOption.value : '';

    setAttributeValues(prev => ({
      ...prev,
      [attributeKey]: {
        ...prev[attributeKey],
        Values: { AttributeValue: value }
      }
    }));
  }, []);

  // Handle matching row changes
  const handleMatchingRowChange = useCallback((
    attributeKey: string,
    rowIndex: number,
    field: { type: string; key: string },
    value: string
  ) => {
    setAttributeValues(prev => {
      const currentValues = prev[attributeKey]?.Values as MatchingValue[] || [];
      const newValues = [...currentValues];

      if (!newValues[rowIndex]) {
        newValues[rowIndex] = { Shop: {}, Marketplace: {} };
      }

      if (field.type === 'Shop') {
        newValues[rowIndex].Shop[field.key as keyof typeof newValues[number]['Shop']] = value;
      } else if (field.type === 'Marketplace') {
        newValues[rowIndex].Marketplace[field.key as keyof typeof newValues[number]['Marketplace']] = value;
      }

      return {
        ...prev,
        [attributeKey]: {
          ...prev[attributeKey],
          Values: newValues
        }
      };
    });
  }, []);

  // Add new matching row
  const addMatchingRow = useCallback((attributeKey: string) => {
    setAttributeValues(prev => {
      const currentValues = prev[attributeKey]?.Values as MatchingValue[] || [];
      return {
        ...prev,
        [attributeKey]: {
          ...prev[attributeKey],
          Values: [...currentValues, { Shop: {}, Marketplace: {} }]
        }
      };
    });
  }, []);

  // Remove matching row
  const removeMatchingRow = useCallback((attributeKey: string, rowIndex: number) => {
    setAttributeValues(prev => {
      const currentValues = prev[attributeKey]?.Values as MatchingValue[] || [];
      const newValues = currentValues.filter((_, index) => index !== rowIndex);

      return {
        ...prev,
        [attributeKey]: {
          ...prev[attributeKey],
          Values: newValues
        }
      };
    });
  }, []);

  // Toggle optional attribute visibility
  const toggleOptionalAttribute = useCallback((attributeKey: string) => {
    setOptionalAttributeVisibility(prev => ({
      ...prev,
      [attributeKey]: !prev[attributeKey]
    }));
  }, []);

  // Get Amazon attribute values as select options
  const getAmazonAttributeOptions = useCallback((attribute: MarketplaceAttribute): SelectOption[] => {
    if (!attribute?.values) return [];

    return Object.entries(attribute.values).map(([key, value]) => ({
      value: key,
      label: value
    }));
  }, []);

  // Find shop attribute by key
  const findShopAttribute = useCallback((key: string): ShopAttribute | null => {
    for (const [groupName, group] of Object.entries(shopAttributes)) {
      if (typeof group === 'object' && group[key]) {
        return group[key] as ShopAttribute;
      }
    }
    return null;
  }, [shopAttributes]);

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

  // Get flat options from nested structure
  const getFlatOptions = useCallback((options: SelectOptions): SelectOption[] => {
    return options.flatMap(opt =>
      'options' in opt ? opt.options : [opt]
    );
  }, []);

  // Render attribute matching content based on selection type
  const renderAttributeMatchingContent = useCallback((
    attributeKey: string,
    attribute: MarketplaceAttribute,
    selectedShopAttr: string | undefined,
    shopAttr: ShopAttribute | null
  ): React.ReactNode => {
    if (!selectedShopAttr) return null;

    if (selectedShopAttr === 'freetext') {
      return (
        <div className="magnalisterAttributeAjaxForm">
          <input
            type="text"
            name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][Values][FreeText]`}
            style={{ width: '100%' }}
            placeholder={i18n.enterFreetext}
            value={(attributeValues[attributeKey]?.Values as any)?.FreeText || ''}
            onChange={(e) => handleFreetextChange(attributeKey, e.target.value)}
            disabled={disabled}
          />
        </div>
      );
    }

    if (selectedShopAttr === 'attribute_value') {
      if (attribute?.values && Object.keys(attribute.values).length > 0) {
        const options = getAmazonAttributeOptions(attribute);
        const currentValue = (attributeValues[attributeKey]?.Values as any)?.AttributeValue;
        return (
          <div className="magnalisterAttributeAjaxForm">
            <Select
              name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][Values][AttributeValue]`}
              options={options}
              value={options.find(opt => opt.value === currentValue) || null}
              onChange={(selectedOption) => handleAttributeValueChange(attributeKey, selectedOption)}
              placeholder={i18n.selectAmazonValue || 'Select Amazon value...'}
              isSearchable
              isDisabled={disabled}
              menuPortalTarget={document.body}
              menuPosition={'fixed'}
              classNamePrefix="react-select"
              styles={{
                container: (base) => ({ ...base, width: '100%' }),
                menuPortal: (base) => ({ ...base, zIndex: 9999 }),
                menu: (base) => ({ ...base, zIndex: 9999 })
              }}
            />
          </div>
        );
      } else {
        return (
          <div className="magnalisterAttributeAjaxForm">
            <input
              type="text"
              name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][Values][AttributeValue]`}
              style={{ width: '100%' }}
              placeholder={i18n.enterAmazonValue || 'Enter Amazon value'}
              value={(attributeValues[attributeKey]?.Values as any)?.AttributeValue || ''}
              onChange={(e) => handleAttributeValueChange(attributeKey, { value: e.target.value, label: e.target.value })}
              disabled={disabled}
            />
          </div>
        );
      }
    }

    // Shop attribute matching
    if (shopAttr && (shopAttr.type === 'select' || shopAttr.type === 'multiSelect')) {
      return renderShopAttributeMatching(attributeKey, attribute, shopAttr);
    }

    // For text and selectandtext types
    if (shopAttr && (shopAttr.type === 'text' || shopAttr.type === 'selectandtext')) {
      return (
        <div className="magnalisterAttributeAjaxForm">
          <input
            type="text"
            name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][Values][FreeText]`}
            style={{ width: '100%' }}
            placeholder={i18n.enterFreetext}
            value={(attributeValues[attributeKey]?.Values as any)?.FreeText || ''}
            onChange={(e) => handleFreetextChange(attributeKey, e.target.value)}
            disabled={disabled}
          />
        </div>
      );
    }

    return null;
  }, [
    variationGroup,
    i18n,
    attributeValues,
    disabled,
    handleFreetextChange,
    getAmazonAttributeOptions,
    handleAttributeValueChange
  ]);

  // Render shop attribute matching interface
  const renderShopAttributeMatching = useCallback((
    attributeKey: string,
    amazonAttribute: MarketplaceAttribute,
    shopAttr: ShopAttribute
  ): React.ReactNode => {
    const currentValues = attributeValues[attributeKey]?.Values as MatchingValue[] || [];
    const matchingRows = Array.isArray(currentValues) ? currentValues : [];

    return (
      <div className="magnalisterAttributeAjaxForm">
        <span className="nowrap ml-translate-toolbar-wrapper ml-field-flex-align-center">
          <input
            type="checkbox"
            id={`ml-field-variationgroups-${variationGroup}-${attributeKey}-UseShopValues`}
            name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][UseShopValues]`}
            defaultChecked
            disabled={disabled}
          />
          <label htmlFor={`ml-field-variationgroups-${variationGroup}-${attributeKey}-UseShopValues`}>
            {i18n.useShopValues || 'Use shop values'}
          </label>
        </span>

        <table style={{ width: '100%', marginTop: '15px' }} className="attribute-matching-table">
          <thead>
            <tr>
              <th style={{ width: '35%', borderRight: '1px solid #dadada' }}>
                {i18n.shopValue || 'Shop Value'}
              </th>
              <th style={{ width: '35%', borderRight: '1px solid #dadada' }}>
                {i18n.marketplaceValue || 'Marketplace Value'}
              </th>
              <th></th>
            </tr>
          </thead>
          <tbody className="matching-rows">
            {matchingRows.length === 0 ? (
              renderMatchingRow(attributeKey, amazonAttribute, shopAttr, 0, { Shop: {}, Marketplace: {} })
            ) : (
              matchingRows.map((rowData, index) =>
                renderMatchingRow(attributeKey, amazonAttribute, shopAttr, index, rowData, true)
              )
            )}
          </tbody>
        </table>

        <button
          type="button"
          className="mlbtn add-matching-row"
          onClick={() => addMatchingRow(attributeKey)}
          disabled={disabled}
        >
          +
        </button>
      </div>
    );
  }, [
    attributeValues,
    variationGroup,
    i18n,
    disabled,
    addMatchingRow
  ]);

  // Render individual matching row
  const renderMatchingRow = useCallback((
    attributeKey: string,
    amazonAttribute: MarketplaceAttribute,
    shopAttr: ShopAttribute,
    rowIndex: number,
    rowData: MatchingValue,
    canRemove = false
  ): React.ReactNode => {
    const shopOptions: SelectOption[] = shopAttr?.values ?
      Object.entries(shopAttr.values).map(([key, value]) => ({
        value: key,
        label: value
      })) : [];

    const amazonOptions: SelectOption[] = amazonAttribute?.values ?
      Object.entries(amazonAttribute.values).map(([key, value]) => ({
        value: key,
        label: value
      })) : [];

    // Add default options
    const shopSelectOptions: SelectOption[] = [
      { value: 'noselection', label: i18n.pleaseSelect || 'Please select' },
      ...shopOptions
    ];

    const amazonSelectOptions: SelectOption[] = [
      { value: 'noselection', label: i18n.pleaseSelect || 'Please select' },
      { value: 'auto', label: i18n.autoMatching || 'Auto-Matching' },
      { value: 'manual', label: i18n.manualMatching || 'Manual entry' },
      ...amazonOptions
    ];

    return (
      <tr key={rowIndex}>
        <td style={{ width: '35%' }}>
          <Select
            name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][Values][${rowIndex}][Shop][Key]`}
            options={shopSelectOptions}
            value={shopSelectOptions.find(opt => opt.value === rowData?.Shop?.Key) || shopSelectOptions[0]}
            onChange={(selectedOption) =>
              handleMatchingRowChange(attributeKey, rowIndex, { type: 'Shop', key: 'Key' }, selectedOption?.value || '')
            }
            className="shop-value-select"
            isSearchable
            isDisabled={disabled}
            menuPortalTarget={document.body}
            menuPosition={'fixed'}
            classNamePrefix="react-select"
            styles={{
              container: (base) => ({ ...base, width: '100%' }),
              menuPortal: (base) => ({ ...base, zIndex: 9999 })
            }}
          />
        </td>
        <td style={{ width: '35%' }}>
          <Select
            name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][Values][${rowIndex}][Marketplace][Key]`}
            options={amazonSelectOptions}
            value={amazonSelectOptions.find(opt => opt.value === rowData?.Marketplace?.Key) || amazonSelectOptions[0]}
            onChange={(selectedOption) =>
              handleMatchingRowChange(attributeKey, rowIndex, { type: 'Marketplace', key: 'Key' }, selectedOption?.value || '')
            }
            className="amazon-matching-select"
            isSearchable
            isDisabled={disabled}
            menuPortalTarget={document.body}
            menuPosition={'fixed'}
            classNamePrefix="react-select"
            styles={{
              container: (base) => ({ ...base, width: '100%' }),
              menuPortal: (base) => ({ ...base, zIndex: 9999 })
            }}
          />
        </td>
        <td style={{ border: 'none' }}>
          <button
            type="button"
            className="mlbtn action remove-matching-row"
            onClick={() => removeMatchingRow(attributeKey, rowIndex)}
            disabled={disabled || (!canRemove && rowIndex === 0)}
            style={{
              opacity: disabled || (!canRemove && rowIndex === 0) ? '0.5' : '1'
            }}
          >
            -
          </button>
        </td>
      </tr>
    );
  }, [
    variationGroup,
    i18n,
    disabled,
    handleMatchingRowChange,
    removeMatchingRow
  ]);

  // Render attribute row
  const renderAttributeRow = useCallback((attributeKey: string, attribute: MarketplaceAttribute, isRequired = false): React.ReactNode => {
    const fieldId = generateFieldId(attributeKey);
    const currentValue = attributeValues[attributeKey];
    const selectedShopAttr = currentValue?.Code;
    const shopAttr = findShopAttribute(selectedShopAttr || '');
    const flatOptions = getFlatOptions(getShopAttributeOptions(attribute.dataType));

    return (
      <tr
        key={attributeKey}
        className={classNames('js-field', {
          'optionalAttribute': !isRequired
        })}
        data-attribute-key={attributeKey}
        data-required={isRequired ? '1' : '0'}
        style={{
          display: isRequired || optionalAttributeVisibility[attributeKey] ? 'table-row' : 'none'
        }}
      >
        <th>
          <label htmlFor={fieldId}>
            <p style={{ display: 'inline-table' }}>
              {attribute.value || attributeKey}
              {isRequired && (
                <span style={{ color: '#e31a1c', fontSize: '18px', marginLeft: '5px' }}>•</span>
              )}
            </p>
          </label>
        </th>
        <td className="mlhelp ml-js-noBlockUi"></td>
        <td className="input">
          <div style={{ overflow: 'hidden', backgroundColor: '#fff' }}>
            <div style={{ float: 'left' }}>
              <div className="ml-form-subfields-main-container">
                <div className="ml-subfield-field-container">
                  <span>
                    <label htmlFor={fieldId}>
                      {i18n.webShopAttribute || 'Web-Shop Attribute'}
                    </label>
                  </span>
                  <span style={{ paddingRight: '1em' }}></span>
                  <span style={{ width: '83%' }}>
                    <Select
                      id={fieldId}
                      name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][Code]`}
                      options={getShopAttributeOptions(attribute.dataType)}
                      value={flatOptions.find(opt => opt.value === selectedShopAttr) || null}
                      onChange={(selectedOption) => handleShopAttributeChange(attributeKey, selectedOption)}
                      className="shop-attribute-select"
                      placeholder={i18n.pleaseSelect || 'Please select...'}
                      isSearchable
                      isDisabled={disabled}
                      menuPortalTarget={document.body}
                      menuPosition={'fixed'}
                      classNamePrefix="react-select"
                      styles={{
                        container: (base) => ({ ...base, width: '100%' }),
                        menuPortal: (base) => ({ ...base, zIndex: 9999 })
                      }}
                    />
                  </span>
                </div>
              </div>
            </div>

            {!isRequired && (
              <div style={{ display: 'flex', width: '360px', flexDirection: 'row' }}>
                <div className="ml-form-subfields-main-container">
                  <div className="ml-subfield-field-container">
                    <span className="attribute-extra-controls">
                      <button
                        type="button"
                        className="mlbtn action add-matching"
                        onClick={() => toggleOptionalAttribute(attributeKey)}
                        disabled={disabled}
                      >
                        +
                      </button>
                    </span>
                  </div>
                </div>
              </div>
            )}
          </div>

          <div className="attribute-matched-content">
            {renderAttributeMatchingContent(attributeKey, attribute, selectedShopAttr, shopAttr)}
          </div>
        </td>
        <td className="info">{attribute.desc || ''}</td>
      </tr>
    );
  }, [
    generateFieldId,
    attributeValues,
    findShopAttribute,
    getFlatOptions,
    getShopAttributeOptions,
    optionalAttributeVisibility,
    variationGroup,
    i18n,
    disabled,
    handleShopAttributeChange,
    toggleOptionalAttribute,
    renderAttributeMatchingContent
  ]);

  // Render optional attributes selector
  const renderOptionalAttributesSelector = useCallback((): React.ReactNode => {
    const availableOptionalAttributes = Object.entries(marketplaceAttributes)
      .filter(([key, attribute]) => !attribute.required && !optionalAttributeVisibility[key])
      .map(([key, attribute]) => ({
        value: key,
        label: attribute.value || key
      }));

    if (availableOptionalAttributes.length === 0) return null;

    return (
      <tr className="js-field optionalAttribute dont_use_sub">
        <th>
          <Select
            options={[
              { value: '', label: i18n.dontUse || "Don't use" },
              ...availableOptionalAttributes
            ]}
            onChange={(selectedOption) => {
              if (selectedOption && selectedOption.value) {
                toggleOptionalAttribute(selectedOption.value);
              }
            }}
            placeholder={i18n.dontUse || "Don't use"}
            className="ml-searchable-select"
            isSearchable
            isDisabled={disabled}
            menuPortalTarget={document.body}
            menuPosition={'fixed'}
            classNamePrefix="react-select"
            styles={{
              container: (base) => ({ ...base, width: '100%' }),
              menuPortal: (base) => ({ ...base, zIndex: 9999 })
            }}
          />
        </th>
        <td className="mlhelp ml-js-noBlockUi"></td>
        <td className="input"></td>
        <td className="info">
          {i18n.optionalAttributeInfo || 'Select an optional Amazon attribute to match with your shop attributes'}
        </td>
      </tr>
    );
  }, [marketplaceAttributes, optionalAttributeVisibility, i18n, disabled, toggleOptionalAttribute]);

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

  // Expose validation function
  useEffect(() => {
    validateRequiredFields();
  }, [attributeValues, validateRequiredFields]);

  return (
    <div className={classNames('amazon-variations-container', className)}>
      {/* Loading indicator */}
      {isLoading && (
        <div className="loading-indicator" style={{ textAlign: 'center', padding: '20px' }}>
          <span>Loading...</span>
        </div>
      )}

      {/* Validation Errors */}
      {validationErrors.length > 0 && (
        <div className="noticeBox ml-error-box">
          <strong>{i18n.fixErrors || 'Please fix the following errors:'}</strong>
          <ul>
            {validationErrors.map((error, index) => (
              <li key={index}>
                {error.name}: {error.message}
              </li>
            ))}
          </ul>
        </div>
      )}

      {/* Amazon Attributes Table */}
      <table className="attributesTable ml-js-attribute-matching" id="amazonAttributesTable">
        {/* Required Attributes Section */}
        <tbody id="amazon_prepare_variations_fieldset_required">
          <tr className="headline">
            <td colSpan={2}>
              <h4>{i18n.requiredAttributesTitle || `${marketplaceName} Required Attributes`}</h4>
            </td>
            <td colSpan={2}>
              <h4>{i18n.attributesMatchingTitle || 'Attributes Matching'}</h4>
            </td>
          </tr>

          {requiredAttributes.map(([key, attribute]) =>
            renderAttributeRow(key, attribute, true)
          )}

          <tr className="spacer"><td colSpan={4}></td></tr>
        </tbody>

        {/* Optional Attributes Section */}
        {optionalAttributes.length > 0 && (
          <tbody id="amazon_prepare_variations_fieldset_optional">
            <tr className="headline optional-headline">
              <td colSpan={2}>
                <h4>{i18n.optionalAttributesTitle || `${marketplaceName} Optional Attributes`}</h4>
              </td>
              <td colSpan={2}>
                <h4>{i18n.optionalAttributeMatching || 'Optional Attribute and Value Matching'}</h4>
              </td>
            </tr>

            {optionalAttributes.map(([key, attribute]) =>
              renderAttributeRow(key, attribute, false)
            )}

            {renderOptionalAttributesSelector()}

            <tr className="spacer"><td colSpan={4}></td></tr>
          </tbody>
        )}
      </table>

      <p>
        {i18n.mandatoryFieldsInfo || `Fields with • are mandatory fields from ${marketplaceName}.`}
      </p>
    </div>
  );
};

export default React.memo(AmazonVariations);