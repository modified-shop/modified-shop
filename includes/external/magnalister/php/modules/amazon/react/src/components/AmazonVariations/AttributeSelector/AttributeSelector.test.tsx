import React from 'react';
import {render} from '@testing-library/react';
import AttributeSelector from './index';
import {I18nStrings, ShopAttribute, ShopAttributes} from '../../../types';

/**
 * Regression tests for OTRS #607858.
 *
 * Bug: when a marketplace item-specific is selection-only (dataType "select",
 * e.g. eBay "Ursprungsland"/Country of Origin), the shop-attribute dropdown
 * greyed out every shop attribute whose type was not exactly "select".
 * PrestaShop product features are typed "selectAndText"/"text" (never "select"),
 * so they all became unselectable.
 *
 * Fix: a "select" marketplace attribute now also accepts shop attributes that
 * expose a value list — "select", "selectAndText" and "multiSelect" (the only
 * value-list types a shop adapter ever emits; "multiSelectAndText" is a
 * marketplace dataType, never a shop-attribute type).
 * Pure "text" shop attributes (no value list) stay disabled, and the original
 * Amazon behaviour for "select" shop attributes is unchanged.
 *
 * With < 10 options the component renders a native <select>, so we can assert
 * each <option disabled> directly.
 */

const i18n: I18nStrings = {webShopAttribute: 'Web-Shop Attribute', pleaseSelect: 'Please select...'} as I18nStrings;

// One variation attr (select) + product features of every relevant type.
const shopAttributes: ShopAttributes = {
  '': 'Please select...',
  'Variations': {
    optGroupClass: 'variation',
    'a_1': {name: 'Size', type: 'select'},
  },
  'Properties': {
    optGroupClass: 'property',
    'f_sat': {name: 'CountryFeature', type: 'selectAndText'},   // PrestaShop non-custom feature
    'tags': {name: 'Tags', type: 'multiSelect'},                // PrestaShop tags / Shopware 5 properties (value list)
    // Shopware 6 emits 'multiselect' all-lowercase for sw-multi-select custom fields
    // (Shopware6/Model/ConfigForm/Shop.php → addCustomFieldListAttributeMatching). The gate
    // must match it case-insensitively. Cast: the runtime value is outside the typed union.
    'c_sw6ms': {name: 'SW6MultiCustomField', type: 'multiselect'} as ShopAttribute,
    'f_text': {name: 'CustomTextFeature', type: 'text'},        // PrestaShop custom (free-text) feature
  },
};

function renderWith(dataType: string) {
  const {container} = render(
    <AttributeSelector
      attributeKey="Ursprungsland"
      variationGroup="g"
      marketplaceName="eBay"
      shopAttributes={shopAttributes}
      dataType={dataType}
      i18n={i18n}
      onChange={() => undefined}
    />
  );
  const opts: Record<string, boolean> = {};
  container.querySelectorAll('option').forEach((o) => {
    opts[(o as HTMLOptionElement).value] = (o as HTMLOptionElement).disabled;
  });
  return opts;
}

describe('AttributeSelector disable logic — OTRS #607858', () => {
  test('select-type marketplace attribute (e.g. Ursprungsland) enables selectAndText / multiSelect features', () => {
    const disabled = renderWith('select');
    // The actual bug: these must NOT be disabled after the fix
    expect(disabled['f_sat']).toBe(false);     // selectAndText → selectable (the customer's case)
    expect(disabled['tags']).toBe(false);      // multiSelect (tags / SW5 properties) → selectable
    expect(disabled['c_sw6ms']).toBe(false);   // SW6 lowercase 'multiselect' → selectable (case-insensitive gate)
    // Unchanged behaviour:
    expect(disabled['a_1']).toBe(false);     // pure select → selectable (as before)
    expect(disabled['f_text']).toBe(true);   // pure text has no value list → stays disabled
  });

  test('non-select marketplace attribute (selectAndText) never disables shop attributes', () => {
    const disabled = renderWith('selectAndText');
    expect(disabled['f_sat']).toBe(false);
    expect(disabled['tags']).toBe(false);
    expect(disabled['f_text']).toBe(false);
    expect(disabled['a_1']).toBe(false);
  });

  test('Amazon regression: select-type attribute keeps select shop attrs enabled and text shop attrs disabled', () => {
    const disabled = renderWith('select');
    expect(disabled['a_1']).toBe(false);     // select shop attr usable for value matching (Amazon intent preserved)
    expect(disabled['f_text']).toBe(true);   // text shop attr still cannot be value-matched
  });
});