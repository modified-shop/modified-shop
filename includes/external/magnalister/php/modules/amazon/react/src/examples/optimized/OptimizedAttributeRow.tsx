import React, {memo, useCallback, useMemo, useState} from 'react';
import VirtualizedSelect from './VirtualizedSelect';
import {AttributeRowProps, MatchingValue, SelectOption, SelectOptions} from '../../types';

/**
 * Highly optimized attribute row component with memoization and lazy rendering
 */
const OptimizedAttributeRow: React.FC<AttributeRowProps> = ({
  attributeKey,
  attribute,
  isRequired,
  currentValue,
  variationGroup,
  shopAttributes,
  i18n,
  onAttributeChange,
  onToggleOptional
}) => {
  const [isExpanded, setIsExpanded] = useState(!!currentValue?.Code);

  // Generate field ID (memoized)
  const fieldId = useMemo(() =>
    `amazon_prepare_variations_field_variationgroups_${variationGroup}_${attributeKey}_code`,
    [variationGroup, attributeKey]
  );

  // Check if attribute is disabled based on data type compatibility (memoized)
  const isAttributeDisabled = useCallback((shopAttrType: string, amazonDataType?: string): boolean => {
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

  // Get shop attribute options (memoized)
  const shopAttributeOptions = useMemo((): SelectOptions => {
    const options: SelectOptions = [
      { value: '', label: i18n.dontUse || "Don't use", isDisabled: false }
    ];

    Object.entries(shopAttributes).forEach(([groupName, group]) => {
      if (typeof group === 'object' && group.optGroupClass) {
        const groupOptions: SelectOption[] = [];

        Object.entries(group).forEach(([attrKey, attr]) => {
          if (attrKey !== 'optGroupClass' && typeof attr === 'object') {
            const disabled = isAttributeDisabled(attr.type, attribute.dataType);
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
        { value: 'freetext', label: i18n.freetext  },
        {
          value: 'attribute_value',
          label: i18n.useAttributeValue || 'Use Amazon attribute value',
          isDisabled: attribute.dataType && !['text', 'selectandtext'].includes(attribute.dataType.toLowerCase())
        }
      ]
    });

    return options;
  }, [shopAttributes, i18n, isAttributeDisabled, attribute.dataType]);

  // Find shop attribute by key (memoized)
  const selectedShopAttribute = useMemo(() => {
    if (!currentValue?.Code) return null;

    for (const [groupName, group] of Object.entries(shopAttributes)) {
      if (typeof group === 'object' && group[currentValue.Code]) {
        return group[currentValue.Code];
      }
    }
    return null;
  }, [currentValue?.Code, shopAttributes]);

  // Handle shop attribute selection change
  const handleShopAttributeChange = useCallback((selectedOption: SelectOption | null) => {
    const newValue = selectedOption ? selectedOption.value : '';

    onAttributeChange(attributeKey, {
      ...currentValue,
      Code: newValue,
      Values: undefined,
      FreeTextValue: undefined,
      AttributeValue: undefined
    });

    setIsExpanded(!!newValue);
  }, [attributeKey, currentValue, onAttributeChange]);

  // Get current selected option (memoized)
  const currentSelectedOption = useMemo(() => {
    if (!currentValue?.Code) return null;

    const flatOptions = shopAttributeOptions.flatMap(opt =>
      'options' in opt ? opt.options : [opt]
    );

    return flatOptions.find(opt => opt.value === currentValue.Code) || null;
  }, [currentValue?.Code, shopAttributeOptions]);

  // Render attribute matching content (memoized by content type)
  const matchingContent = useMemo(() => {
    if (!currentValue?.Code || !isExpanded) return null;

    const selectedCode = currentValue.Code;

    // Freetext input
    if (selectedCode === 'freetext') {
      return (
        <div className="magnalisterAttributeAjaxForm">
          <input
            type="text"
            name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][Values][FreeText]`}
            style={{ width: '100%' }}
            placeholder={i18n.enterFreetext }
            value={(currentValue.Values as any)?.FreeText || ''}
            onChange={(e) => onAttributeChange(attributeKey, {
              ...currentValue,
              Values: { FreeText: e.target.value }
            })}
          />
        </div>
      );
    }

    // Amazon attribute value
    if (selectedCode === 'attribute_value') {
      if (attribute.values && Object.keys(attribute.values).length > 0) {
        const options = Object.entries(attribute.values).map(([key, value]) => ({
          value: key,
          label: value
        }));

        return (
          <div className="magnalisterAttributeAjaxForm">
            <VirtualizedSelect
              options={options}
              value={options.find(opt => opt.value === (currentValue.Values as any)?.AttributeValue) || null}
              onChange={(selectedOption) => onAttributeChange(attributeKey, {
                ...currentValue,
                Values: { AttributeValue: selectedOption?.value || '' }
              })}
              placeholder={i18n.selectAmazonValue || 'Select Amazon value...'}
              threshold={50}
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
              value={(currentValue.Values as any)?.AttributeValue || ''}
              onChange={(e) => onAttributeChange(attributeKey, {
                ...currentValue,
                Values: { AttributeValue: e.target.value }
              })}
            />
          </div>
        );
      }
    }

    // Shop attribute matching
    if (selectedShopAttribute && (selectedShopAttribute.type === 'select' || selectedShopAttribute.type === 'multiSelect')) {
      const matchingValues = (currentValue.Values as MatchingValue[]) || [];

      return (
        <div className="magnalisterAttributeAjaxForm">
          <div className="ml-field-flex-align-center" style={{ marginBottom: '10px' }}>
            <input
              type="checkbox"
              id={`ml-field-variationgroups-${variationGroup}-${attributeKey}-UseShopValues`}
              name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][UseShopValues]`}
              defaultChecked
            />
            <label htmlFor={`ml-field-variationgroups-${variationGroup}-${attributeKey}-UseShopValues`}>
              {i18n.useShopValues || 'Use shop values'}
            </label>
          </div>

          <table style={{ width: '100%' }} className="attribute-matching-table">
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
            <tbody>
              {matchingValues.length === 0 ? (
                <tr>
                  <td colSpan={3} style={{ textAlign: 'center', padding: '10px', color: '#666' }}>
                    No matching rules configured
                  </td>
                </tr>
              ) : (
                matchingValues.map((matching, index) => (
                  <tr key={index}>
                    <td>
                      <VirtualizedSelect
                        options={[
                          { value: 'noselection', label: i18n.pleaseSelect || 'Please select' },
                          ...Object.entries(selectedShopAttribute.values || {}).map(([key, value]) => ({
                            value: key,
                            label: value
                          }))
                        ]}
                        value={{ value: matching.Shop?.Key || 'noselection', label: matching.Shop?.Key || i18n.pleaseSelect || 'Please select' }}
                        onChange={(option) => {
                          const newMatching = [...matchingValues];
                          newMatching[index] = {
                            ...newMatching[index],
                            Shop: { Key: option?.value || '' }
                          };
                          onAttributeChange(attributeKey, {
                            ...currentValue,
                            Values: newMatching
                          });
                        }}
                        threshold={20}
                      />
                    </td>
                    <td>
                      <VirtualizedSelect
                        options={[
                          { value: 'noselection', label: i18n.pleaseSelect || 'Please select' },
                          { value: 'auto', label: i18n.autoMatching || 'Auto-Matching' },
                          { value: 'manual', label: i18n.manualMatching || 'Manual entry' },
                          ...Object.entries(attribute.values || {}).map(([key, value]) => ({
                            value: key,
                            label: value
                          }))
                        ]}
                        value={{ value: matching.Marketplace?.Key || 'noselection', label: matching.Marketplace?.Key || i18n.pleaseSelect || 'Please select' }}
                        onChange={(option) => {
                          const newMatching = [...matchingValues];
                          newMatching[index] = {
                            ...newMatching[index],
                            Marketplace: { Key: option?.value || '' }
                          };
                          onAttributeChange(attributeKey, {
                            ...currentValue,
                            Values: newMatching
                          });
                        }}
                        threshold={20}
                      />
                    </td>
                    <td>
                      <button
                        type="button"
                        className="mlbtn action remove-matching-row"
                        onClick={() => {
                          const newMatching = matchingValues.filter((_, i) => i !== index);
                          onAttributeChange(attributeKey, {
                            ...currentValue,
                            Values: newMatching
                          });
                        }}
                        style={{ opacity: index === 0 && matchingValues.length === 1 ? '0.5' : '1' }}
                        disabled={index === 0 && matchingValues.length === 1}
                      >
                        -
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>

          <button
            type="button"
            className="mlbtn add-matching-row"
            onClick={() => {
              const newMatching = [...matchingValues, { Shop: {}, Marketplace: {} }];
              onAttributeChange(attributeKey, {
                ...currentValue,
                Values: newMatching
              });
            }}
            style={{ marginTop: '10px' }}
          >
            +
          </button>
        </div>
      );
    }

    // Text attributes
    if (selectedShopAttribute && (selectedShopAttribute.type === 'text' || selectedShopAttribute.type === 'selectandtext')) {
      return (
        <div className="magnalisterAttributeAjaxForm">
          <input
            type="text"
            name={`ml[field][variationgroups][${variationGroup}][${attributeKey}][Values][FreeText]`}
            style={{ width: '100%' }}
            placeholder={i18n.enterFreetext}
            value={(currentValue.Values as any)?.FreeText || ''}
            onChange={(e) => onAttributeChange(attributeKey, {
              ...currentValue,
              Values: { FreeText: e.target.value }
            })}
          />
        </div>
      );
    }

    return null;
  }, [
    currentValue,
    isExpanded,
    variationGroup,
    attributeKey,
    attribute,
    selectedShopAttribute,
    i18n,
    onAttributeChange
  ]);

  return (
    <tr
      className={`js-field ${!isRequired ? 'optionalAttribute' : ''}`}
      data-attribute-key={attributeKey}
      data-required={isRequired ? '1' : '0'}
      style={{ display: 'table-row' }}
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
          <div style={{ float: 'left', width: '100%' }}>
            <div className="ml-form-subfields-main-container">
              <div className="ml-subfield-field-container">
                <span>
                  <label htmlFor={fieldId}>
                    {i18n.webShopAttribute || 'Web-Shop Attribute'}
                  </label>
                </span>
                <span style={{ paddingRight: '1em' }}></span>
                <span style={{ width: '83%' }}>
                  <VirtualizedSelect
                    id={fieldId}
                    options={shopAttributeOptions}
                    value={currentSelectedOption}
                    onChange={handleShopAttributeChange}
                    placeholder={i18n.pleaseSelect || 'Please select...'}
                    threshold={100}
                  />
                </span>
              </div>
            </div>

            {!isRequired && (
              <div style={{ marginTop: '10px' }}>
                <button
                  type="button"
                  className="mlbtn action add-matching"
                  onClick={() => onToggleOptional?.(attributeKey)}
                >
                  +
                </button>
              </div>
            )}
          </div>

          {isExpanded && (
            <div className="attribute-matched-content" style={{ marginTop: '10px' }}>
              {matchingContent}
            </div>
          )}
        </div>
      </td>
      <td className="info">{attribute.desc || ''}</td>
    </tr>
  );
};

// Deep comparison for memo
const areEqual = (prevProps: AttributeRowProps, nextProps: AttributeRowProps) => {
  return (
    prevProps.attributeKey === nextProps.attributeKey &&
    prevProps.isRequired === nextProps.isRequired &&
    JSON.stringify(prevProps.currentValue) === JSON.stringify(nextProps.currentValue) &&
    prevProps.attribute === nextProps.attribute &&
    prevProps.shopAttributes === nextProps.shopAttributes
  );
};

export default memo(OptimizedAttributeRow, areEqual);