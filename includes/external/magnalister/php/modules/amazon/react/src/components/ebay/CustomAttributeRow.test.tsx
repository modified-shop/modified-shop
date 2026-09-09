import React from 'react';
import {render} from '@testing-library/react';
import CustomAttributeRow, {CustomAttributeValue} from './CustomAttributeRow';
import {I18nStrings, ShopAttribute, ShopAttributes} from '../../types';

/**
 * Regression tests for the eBay custom-attribute gate (OTRS #607858).
 *
 * In the eBay custom-attributes section, picking a shop attribute as the VALUE
 * shows a value-matching table only when that attribute exposes a value list.
 * Before the fix this was `type === 'select'` only, so PrestaShop features
 * (`selectAndText`) and multi-select attributes never got a matching table.
 *
 * The gate now uses the shared case-insensitive `isValueListType`, so it must
 * also accept the Shopware 6 `sw-multi-select` custom field, which is emitted
 * as the all-lowercase `multiselect`.
 *
 * ValueMatchingTable always renders a `.value-matching-table-container`, so its
 * presence/absence is the observable signal for the gate.
 */

const i18n = {valueMatchingTitle: 'Value Matching'} as I18nStrings;

// Value-list attributes (matching table expected) + a pure-text one (no table).
const shopAttributes: ShopAttributes = {
  'Properties': {
    optGroupClass: 'property',
    'c_feature': {name: 'CountryFeature', type: 'selectAndText'},        // PrestaShop feature
    'c_props': {name: 'Properties', type: 'multiSelect'},                // camelCase multi-select
    // Shopware 6 sw-multi-select custom field → lowercase 'multiselect' (the bug case)
    'c_sw6ms': {name: 'SW6MultiCustomField', type: 'multiselect'} as ShopAttribute,
    'c_text': {name: 'PlainText', type: 'text'},                        // no value list
  },
};

function renderWithValue(valueCode: string) {
  const value: CustomAttributeValue = {attributeCode: 'freetext', customName: 'Ursprungsland', valueCode};
  const {container} = render(
    <CustomAttributeRow
      rowId="r1"
      value={value}
      shopAttributes={shopAttributes}
      i18n={i18n}
      onChange={() => undefined}
      onFetchShopAttributeValues={() => Promise.resolve({})}
    />
  );
  return container.querySelector('.value-matching-table-container');
}

describe('eBay CustomAttributeRow value-matching gate — OTRS #607858', () => {
  test('selectAndText VALUE attribute shows the matching table', () => {
    expect(renderWithValue('c_feature')).not.toBeNull();
  });

  test('multiSelect (camelCase) VALUE attribute shows the matching table', () => {
    expect(renderWithValue('c_props')).not.toBeNull();
  });

  test('Shopware 6 lowercase "multiselect" VALUE attribute shows the matching table', () => {
    expect(renderWithValue('c_sw6ms')).not.toBeNull();
  });

  test('pure text VALUE attribute does NOT show the matching table', () => {
    expect(renderWithValue('c_text')).toBeNull();
  });
});