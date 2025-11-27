import React, {useCallback, useEffect, useMemo, useState} from 'react';
import Select from 'react-select';

const AmazonVariations = ({
  variationGroup,
  customIdentifier,
  marketplaceName = 'Amazon',
  shopAttributes = {},
  marketplaceAttributes = {},
  savedValues = {},
  i18n = {}
}) => {
  const [attributeValues, setAttributeValues] = useState(savedValues);
  const [optionalAttributeVisibility, setOptionalAttributeVisibility] = useState({});
  const [validationErrors, setValidationErrors] = useState([]);

  // Initialize component state
  useEffect(() => {
    setAttributeValues(savedValues);

    // Initialize visibility for optional attributes that have saved values
    const visibility = {};
    Object.entries(marketplaceAttributes).forEach(([key, attribute]) => {
      if (!attribute.required && savedValues[key]) {
        visibility[key] = true;
      }
    });
    setOptionalAttributeVisibility(visibility);
  }, [savedValues, marketplaceAttributes]);

  // Helper function to generate field IDs
  const generateFieldId = useCallback((attributeKey) => {
    return `${marketplaceName.toLowerCase()}_prepare_variations_field_variationgroups_${variationGroup}_${attributeKey}_code`;
  }, [marketplaceName, variationGroup]);

  // Helper function to check if attribute is disabled based on data type compatibility
  const isAttributeDisabled = useCallback((shopAttrType, amazonDataType) => {
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
  const getShopAttributeOptions = useCallback((dataType) => {
    const options = [{ value: '', label: i18n.dontUse || 'Don\'t use', isDisabled: false }];

    Object.entries(shopAttributes).forEach(([groupName, group]) => {
      if (typeof group === 'object' && group.optGroupClass) {
        const groupOptions = [];

        Object.entries(group).forEach(([attrKey, attr]) => {
          if (attrKey !== 'optGroupClass' && typeof attr === 'object') {
            const disabled = isAttributeDisabled(attr.type, dataType);
            groupOptions.push({
              value: attrKey,
              label: attr.name,
              isDisabled: disabled,
              type: attr.type
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
        {
          value: 'attribute_value',
          label: i18n.useAttributeValue || 'Use Amazon attribute value',
          isDisabled: dataType && !['text', 'selectandtext'].includes(dataType.toLowerCase())
        }
      ]
    });

    return options;
  }, [shopAttributes, i18n, isAttributeDisabled]);

  // Handle shop attribute selection change
  const handleShopAttributeChange = useCallback((attributeKey, selectedOption) => {
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
  const handleFreetextChange = useCallback((attributeKey, value) => {
    setAttributeValues(prev => ({
      ...prev,
      [attributeKey]: {
        ...prev[attributeKey],
        Values: { FreeText: value }
      }
    }));
  }, []);

  // Handle attribute value selection change
  const handleAttributeValueChange = useCallback((attributeKey, selectedOption) => {
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
  const handleMatchingRowChange = useCallback((attributeKey, rowIndex, field, value) => {
    setAttributeValues(prev => {
      const currentValues = prev[attributeKey]?.Values || [];
      const newValues = [...currentValues];

      if (!newValues[rowIndex]) {
        newValues[rowIndex] = { Shop: {}, Marketplace: {} };
      }

      newValues[rowIndex][field.type][field.key] = value;

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
  const addMatchingRow = useCallback((attributeKey) => {
    setAttributeValues(prev => {
      const currentValues = prev[attributeKey]?.Values || [];
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
  const removeMatchingRow = useCallback((attributeKey, rowIndex) => {
    setAttributeValues(prev => {
      const currentValues = prev[attributeKey]?.Values || [];
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
  const toggleOptionalAttribute = useCallback((attributeKey) => {
    setOptionalAttributeVisibility(prev => ({
      ...prev,
      [attributeKey]: !prev[attributeKey]
    }));
  }, []);

  // Get Amazon attribute values as select options
  const getAmazonAttributeOptions = useCallback((attribute) => {
    if (!attribute?.values) return [];

    return Object.entries(attribute.values).map(([key, value]) => ({
      value: key,
      label: value
    }));
  }, []);

  // Find shop attribute by key
  const findShopAttribute = useCallback((key) => {
    for (const [groupName, group] of Object.entries(shopAttributes)) {
      if (typeof group === 'object' && group[key]) {
        return group[key];
      }
    }
    return null;
  }, [shopAttributes]);

  // Validate required fields
  const validateRequiredFields = useCallback(() => {
    const errors = [];

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
    return errors;
  }, [marketplaceAttributes, attributeValues, i18n]);

  // Render attribute row
  const renderAttributeRow = (attributeKey, attribute, isRequired = false) => {
    const fieldId = generateFieldId(attributeKey);
    const currentValue = attributeValues[attributeKey];
    const selectedShopAttr = currentValue?.Code;
    const shopAttr = findShopAttribute(selectedShopAttr);

    return (
      <tr
        key={attributeKey}
        className={`js-field ${!isRequired ? 'optionalAttribute' : ''}`}
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
                      value={getShopAttributeOptions(attribute.dataType)
                        .flatMap(opt => opt.options || [opt])
                        .find(opt => opt.value === selectedShopAttr) || null}
                      onChange={(selectedOption) => handleShopAttributeChange(attributeKey, selectedOption)}
                      className="shop-attribute-select"
                      placeholder={i18n.pleaseSelect || 'Please select...'}
                      isSearchable
                      styles={{
                        container: (base) => ({ ...base, width: '100%' })
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
  };

  // Render attribute matching content based on selection type
  const renderAttributeMatchingContent = (attributeKey, attribute, selectedShopAttr, shopAttr) => {
    if (!selectedShopAttr) return null;

    if (selectedShopAttr === 'freetext') {
      return (
        <div className="magnalisterAttributeAjaxForm">
          <input
            type="text"
            name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][Values][FreeText]`}
            style={{ width: '100%' }}
            placeholder={i18n.enterFreetext || 'Enter custom value'}
            value={attributeValues[attributeKey]?.Values?.FreeText || ''}
            onChange={(e) => handleFreetextChange(attributeKey, e.target.value)}
          />
        </div>
      );
    }

    if (selectedShopAttr === 'attribute_value') {
      if (attribute?.values && Object.keys(attribute.values).length > 0) {
        return (
          <div className="magnalisterAttributeAjaxForm">
            <Select
              name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][Values][AttributeValue]`}
              options={getAmazonAttributeOptions(attribute)}
              value={getAmazonAttributeOptions(attribute)
                .find(opt => opt.value === attributeValues[attributeKey]?.Values?.AttributeValue) || null}
              onChange={(selectedOption) => handleAttributeValueChange(attributeKey, selectedOption)}
              placeholder={i18n.selectAmazonValue || 'Select Amazon value...'}
              isSearchable
              styles={{
                container: (base) => ({ ...base, width: '100%' })
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
              value={attributeValues[attributeKey]?.Values?.AttributeValue || ''}
              onChange={(e) => handleAttributeValueChange(attributeKey, { value: e.target.value })}
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
            placeholder={i18n.enterFreetext || 'Enter custom value'}
            value={attributeValues[attributeKey]?.Values?.FreeText || ''}
            onChange={(e) => handleFreetextChange(attributeKey, e.target.value)}
          />
        </div>
      );
    }

    return null;
  };

  // Render shop attribute matching interface
  const renderShopAttributeMatching = (attributeKey, amazonAttribute, shopAttr) => {
    const currentValues = attributeValues[attributeKey]?.Values || [];
    const matchingRows = Array.isArray(currentValues) ? currentValues : [];

    return (
      <div className="magnalisterAttributeAjaxForm">
        <span className="nowrap ml-translate-toolbar-wrapper ml-field-flex-align-center">
          <input
            type="checkbox"
            id={`ml-field-variationgroups-${variationGroup}-${attributeKey}-UseShopValues`}
            name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][UseShopValues]`}
            defaultChecked
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
              renderMatchingRow(attributeKey, amazonAttribute, shopAttr, 0, {})
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
        >
          +
        </button>
      </div>
    );
  };

  // Render individual matching row
  const renderMatchingRow = (attributeKey, amazonAttribute, shopAttr, rowIndex, rowData, canRemove = false) => {
    const shopOptions = shopAttr?.values ?
      Object.entries(shopAttr.values).map(([key, value]) => ({
        value: key,
        label: value
      })) : [];

    const amazonOptions = amazonAttribute?.values ?
      Object.entries(amazonAttribute.values).map(([key, value]) => ({
        value: key,
        label: value
      })) : [];

    // Add default options
    const shopSelectOptions = [
      { value: 'noselection', label: i18n.pleaseSelect || 'Please select' },
      ...shopOptions
    ];

    const amazonSelectOptions = [
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
            styles={{
              container: (base) => ({ ...base, width: '100%' })
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
            styles={{
              container: (base) => ({ ...base, width: '100%' })
            }}
          />
        </td>
        <td style={{ border: 'none' }}>
          <button
            type="button"
            className="mlbtn action remove-matching-row"
            onClick={() => removeMatchingRow(attributeKey, rowIndex)}
            disabled={!canRemove && rowIndex === 0}
            style={{
              opacity: !canRemove && rowIndex === 0 ? '0.5' : '1'
            }}
          >
            -
          </button>
        </td>
      </tr>
    );
  };

  // Render optional attributes selector
  const renderOptionalAttributesSelector = () => {
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
          />
        </th>
        <td className="mlhelp ml-js-noBlockUi"></td>
        <td className="input"></td>
        <td className="info">
          {i18n.optionalAttributeInfo || 'Select an optional Amazon attribute to match with your shop attributes'}
        </td>
      </tr>
    );
  };

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
    <div className="amazon-variations-container">
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
            <td colSpan="2">
              <h4>{i18n.requiredAttributesTitle || `${marketplaceName} Required Attributes`}</h4>
            </td>
            <td colSpan="2">
              <h4>{i18n.attributesMatchingTitle || 'Attributes Matching'}</h4>
            </td>
          </tr>

          {requiredAttributes.map(([key, attribute]) =>
            renderAttributeRow(key, attribute, true)
          )}

          <tr className="spacer"><td colSpan="4"></td></tr>
        </tbody>

        {/* Optional Attributes Section */}
        {optionalAttributes.length > 0 && (
          <tbody id="amazon_prepare_variations_fieldset_optional">
            <tr className="headline optional-headline">
              <td colSpan="2">
                <h4>{i18n.optionalAttributesTitle || `${marketplaceName} Optional Attributes`}</h4>
              </td>
              <td colSpan="2">
                <h4>{i18n.optionalAttributeMatching || 'Optional Attribute and Value Matching'}</h4>
              </td>
            </tr>

            {optionalAttributes.map(([key, attribute]) =>
              renderAttributeRow(key, attribute, false)
            )}

            {renderOptionalAttributesSelector()}

            <tr className="spacer"><td colSpan="4"></td></tr>
          </tbody>
        )}
      </table>

      <p>
        {i18n.mandatoryFieldsInfo || `Fields with • are mandatory fields from ${marketplaceName}.`}
      </p>
    </div>
  );
};

export default AmazonVariations;