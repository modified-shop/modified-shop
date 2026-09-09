import {isValueListType} from './attributeTypes';

/**
 * Unit tests for the shared value-list gate (OTRS #607858).
 *
 * `isValueListType` is the single source of truth used by all three gates
 * (AttributeSelector disable logic, AttributeRow + eBay CustomAttributeRow
 * `shouldShowMatchingTable`). It decides whether a shop attribute exposes a
 * value list that can be matched against a selection-only marketplace item
 * specific.
 *
 * The comparison MUST be case-insensitive: shop adapters disagree on casing —
 * most emit camelCase (`selectAndText` / `multiSelect`), but Shopware 6 emits
 * the all-lowercase `multiselect` for `sw-multi-select` custom fields
 * (70_Shop/Shopware6/Model/ConfigForm/Shop.php::addCustomFieldListAttributeMatching).
 */
describe('isValueListType — OTRS #607858 value-list gate', () => {
  describe('value-list types are accepted (matching table / selectable)', () => {
    test.each([
      'select',          // plain dropdown (every shop)
      'selectAndText',   // PrestaShop product features (the customer's case)
      'multiSelect',     // PrestaShop tags / Shopware 5 properties / Magento (camelCase)
      'multiselect',     // Shopware 6 sw-multi-select custom fields (lowercase) — the bug case
    ])('"%s" → true', (type) => {
      expect(isValueListType(type)).toBe(true);
    });
  });

  describe('non-value-list / empty types are rejected', () => {
    test.each([
      'text',            // free-text, no value list → stays disabled
      'date',
      '',                // empty shop attribute type
    ])('"%s" → false', (type) => {
      expect(isValueListType(type)).toBe(false);
    });

    test('undefined → false', () => {
      expect(isValueListType(undefined)).toBe(false);
    });
  });

  describe('case-insensitive — every casing variant of the value-list types matches', () => {
    test.each([
      'SELECT',
      'SelectAndText',
      'selectandtext',   // PrestaShop lowercase variant
      'MULTISELECT',
      'MultiSelect',
    ])('"%s" → true', (type) => {
      expect(isValueListType(type)).toBe(true);
    });
  });
});