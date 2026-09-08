/**
 * Shop-attribute type constants
 */

/**
 * Shop-attribute types that expose a value list, so their values can be matched
 * against a selection-only ("select") marketplace item specific.
 *
 * - `select`        — plain dropdown attributes (every shop)
 * - `selectAndText` — PrestaShop product features (dropdown + free text)
 * - `multiSelect`   — PrestaShop tags, Shopware 5 properties, Magento multiselect,
 *                     Shopware 6 `sw-multi-select` custom fields
 *
 * Pure `text` attributes (no value list) are intentionally excluded — they stay
 * disabled for "select" item specifics. See OTRS #607858.
 *
 * NOTE: the casing of `type` is NOT consistent across shop adapters — most emit
 * camelCase (`selectAndText` / `multiSelect`), but Shopware 6 emits the all-lowercase
 * `multiselect` for `sw-multi-select` custom fields
 * (`70_Shop/Shopware6/Model/ConfigForm/Shop.php` → `addCustomFieldListAttributeMatching`).
 * Comparison is therefore done case-insensitively via {@link isValueListType}, mirroring
 * `isMultiSelectType` in ValueMatching/. Keep the entries below lowercase.
 */
const VALUE_LIST_TYPES: ReadonlyArray<string> = ['select', 'selectandtext', 'multiselect'];

/**
 * True when a shop attribute's `type` exposes a value list that can be matched
 * against a "select" marketplace item specific. Case-insensitive (see note above).
 */
export const isValueListType = (type: string | undefined): boolean =>
  !!type && VALUE_LIST_TYPES.includes(type.toLowerCase());