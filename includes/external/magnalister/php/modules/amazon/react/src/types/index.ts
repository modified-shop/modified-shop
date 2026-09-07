// Type definitions for Amazon Variations React Component

export interface ShopAttributeValue {
  [key: string]: string;
}

export interface ShopAttribute {
  name: string;
  type: 'select' | 'text' | 'multiSelect' | 'selectAndText';
  values?: ShopAttributeValue;
  /** Original shop metafield type (e.g. 'date', 'date_time'). Used to show date format selector. */
  shopMetaFieldType?: string;
}

export interface ShopAttributeGroup {
  optGroupClass?: string;
  [key: string]: ShopAttribute | string | undefined;
}

export interface ShopAttributes {
  [groupName: string]: ShopAttributeGroup;
}

export interface MarketplaceAttributeValues {
  [key: string]: string;
}

export interface ChildAttributeRef {
  childRefPid: number;
  groupId: string;
}

export interface MarketplaceAttribute {
  value: string;
  required: boolean;
  dataType: 'select' | 'text' | 'multiselect' | 'selectandtext';
  desc?: string;
  values?: MarketplaceAttributeValues;
  // Additional properties found in actual usage
  requiredField?: boolean;
  freetext?: boolean;
  useAttributeValue?: boolean;
  // eBay custom attribute flag - when true, this is a user-defined attribute
  // that allows selecting shop attribute for both NAME and VALUE
  custom?: boolean;
  // Temu parent-child attribute fields (data-driven, only present when API provides them)
  refPid?: number;
  childAttributes?: Record<string, ChildAttributeRef[]> | [];
  parentRefPid?: number;
  triggerVid?: number;
  groupId?: string;
}

// Conditional rules types
export interface ConditionalRuleCondition {
  field: string; // e.g., "shirt_form_type__value"
  operator: 'equals' | 'in' | 'notEquals' | 'notIn';
  value: string | string[]; // Single value or array of values
}

export interface ConditionalRule {
  sourceFields: string | string[]; // Source field name(s) - can be single string or array
  targetField: string; // Target field name
  conditions: ConditionalRuleCondition[]; // Array of conditions (AND logic)
  allowedValues: string[]; // Allowed values for target field when conditions match
  schemaRuleIndex?: number; // Optional index from schema
}

export interface MarketplaceAttributes {
  [key: string]: MarketplaceAttribute;
}

export interface MatchingValue {
  Shop: {
    Key?: string;
    Value?: string;
  };
  Marketplace: {
    Key?: string | string[]; // Can be array for multiselect attributes
    Value?: string | string[]; // Can be array for multiselect attributes
  };
  __id?: string; // Internal stable ID for React key prop
}

export interface AttributeValues {
  FreeText?: string;
  AttributeValue?: string | string[]; // string[] for multiselect attributes
}

export interface DatabaseValue {
  Table?: string;
  Column?: string;
  Alias?: string;
}

export interface SavedAttributeValue {
  Code?: string;
  // Can be a string (backend format), an object (AttributeValues/DatabaseValue),
  // a MatchingValue[] (React format after an edit), or a PHP-serialized object-map
  // {"1": MatchingValue, "2": MatchingValue} delivered on initial page load.
  // Multiselect attribute_value also arrives as a plain string[] of vids at
  // runtime; the array branch in getSelectedVids handles it.
  Values?: string | AttributeValues | DatabaseValue | MatchingValue[] | Record<string, MatchingValue>;
  FreeTextValue?: string;
  AttributeValue?: string;
  UseShopValues?: boolean; // When true, use shop values directly without matching
  Kind?: 'Matching' | 'Freetext' | string;
  /** PHP date format string (e.g. 'd.m.Y') for date-type metafield attributes */
  DateFormat?: string;
}

export interface SavedValues {
  [attributeKey: string]: SavedAttributeValue;
}

export interface I18nStrings {
  dontUse?: string;
  webShopAttribute?: string;
  pleaseSelect?: string;
  enterFreetext?: string;
  useAttributeValue?: string;
  selectAmazonValue?: string;
  enterAmazonValue?: string;
  useShopValues?: string;
  shopValue?: string;
  marketplaceValue?: string;
  autoMatching?: string;
  autoMatchingTooltip?: string;
  manualMatching?: string;
  requiredAttributesTitle?: string;
  attributesMatchingTitle?: string;
  optionalAttributesTitle?: string;
  optionalAttributeMatching?: string;
  optionalAttributeInfo?: string;
  mandatoryFieldsInfo?: string;
  requiredField?: string;
  fixErrors?: string;
  additionalOptions?: string;
  freetext?: string;
  // Form-specific strings
  submitButton?: string;
  resetButton?: string;
  loading?: string;
  autoSaveIndicator?: string;
  saveSuccess?: string;
  saveFailed?: string;
  validationFailed?: string;
  // Additional required strings (no duplicates)
  required?: string;
  value?: string;
  // Optional attribute management
  addOptionalAttribute?: string;
  selectOptionalAttribute?: string;
  removeOptionalAttribute?: string;
  noMoreOptionalAttributes?: string;
  // Value matching table
  valueMatchingTitle?: string;
  valueMatchingDescription?: string;
  shopValueColumn?: string;
  amazonValueColumn?: string;
  marketplaceValueColumn?: string;
  actionColumn?: string;
  matchingInfo?: string;
  removeMatchingRow?: string;
  noShopValuesMessage?: string;
  loadingShopValues?: string;
  loadErrorMessage?: string;
  // Auto-match statistics
  autoMatchResults?: string;
  exactMatches?: string;
  nearMatches?: string;
  noMatches?: string;
  // Search/Filter
  searchMatchings?: string;
  showingResults?: string;
  of?: string;
  matchings?: string;
  // Clear matchings
  clearAllMatchings?: string;
  // Custom text entry for selectAndText matching
  makeCustomEntry?: string;
  enterCustomAmazonValue?: string;
  // Use shop values checkbox
  useShopValuesCheckbox?: string;
  useShopValuesDescription?: string;
  // Conditional rules help text
  conditionalRulesAffectedBy?: string; // "This field options are filtered based on"
  conditionalRulesAffects?: string; // "Changing this field will filter options in"
  // Database value input
  databaseTableLabel?: string;
  databaseColumnLabel?: string;
  databaseAliasLabel?: string;
  databaseTablePlaceholder?: string;
  databaseColumnPlaceholder?: string;
  databaseAliasPlaceholder?: string;
  // eBay custom attributes
  selectAttribute?: string;
  selectValue?: string;
  enterAttributeName?: string;
  enterAttributeValue?: string;
  enterCustomMarketplaceName?: string;
  enterCustomMarketplaceValue?: string;
  removeAttribute?: string;
  // eBay custom attributes section headers
  customAttributesTitle?: string;
  customAttributeMatching?: string;
  // Multiselect attributes
  multiselectHint?: string; // Hint shown for multiselect attributes (can select multiple values)
  // Date format selector
  dateFormatLabel?: string;
  dateFormatIso?: string;
  dateFormatDe?: string;
  dateFormatUs?: string;
  dateFormatUk?: string;
}

export interface ValidationError {
  key: string;
  name: string;
  message: string;
}

export interface SelectOption {
  value: string;
  label: string;
  isDisabled?: boolean;
  type?: string;
}

export interface SelectOptionGroup {
  label: string;
  options: SelectOption[];
}

export type SelectOptions = (SelectOption | SelectOptionGroup)[];

/**
 * Database tables and columns data for database_value matching dropdowns
 */
export interface DatabaseTablesData {
  tables: string[];
  columns: { [tableName: string]: string[] };
}

export interface AmazonVariationsProps {
  variationGroup: string;
  customIdentifier: string;
  variationTheme?: string; // Variation theme code (e.g., "SIZE/COLOR")
  marketplaceName?: string;
  shopAttributes: ShopAttributes;
  marketplaceAttributes: MarketplaceAttributes;
  savedValues?: SavedValues;
  conditionalRules?: ConditionalRule[]; // Conditional logic rules from Amazon API
  neededFormFields?: { [key: string]: string }; // Platform-specific form fields (e.g., Magento form_key)
  i18n?: I18nStrings;
  databaseTables?: DatabaseTablesData; // Database tables/columns for database_value matching (v2 only)
  onValuesChange?: (values: SavedValues) => void;
  onValidationError?: (errors: ValidationError[]) => void;
  className?: string;
  disabled?: boolean;
  onFetchShopAttributeValues?: (attributeCode: string) => Promise<{ [key: string]: string }>;
  apiEndpoint?: string;
  // When set, the "add optional attribute" helper is exposed as
  // window['magnalisterAddOptionalAttribute_' + apiNamespace] instead of the shared
  // window.magnalisterAddOptionalAttribute. Lets multiple instances on one page coexist
  // without racing over a single global (e.g. Temu's variation + category sections).
  apiNamespace?: string;
  // Strict save reporting (Temu opt-in, BUG-018/BUG-019). When true: real save failures
  // show an error toast instead of the unconditional success toast and the external save
  // waits for in-flight saves. V2 DEVIATION from v3: the external-save callback is ALWAYS
  // invoked but receives 'ok' | 'failed' as its first argument (the v2 Save orchestrator
  // wraps callbacks in Promise.all and would hang if the callback were withheld); the
  // orchestrator aborts the submit on 'failed'. Default false — Amazon keeps the previous
  // behavior.
  strictSave?: boolean;
  debugMode?: boolean; // Show keys/values in developer mode when MLSetting::g()->blDebug === true
  wrapInTable?: boolean; // If false, renders only tbody elements (for embedding in existing table). Default: true
  hideHelpColumn?: boolean; // Hide help column. Default: false (v2 compatibility)
  // eBay-specific: Enable custom attributes section where user can define attribute name from shop attributes
  enableCustomAttributes?: boolean;
}

export interface AttributeRowProps {
  attributeKey: string;
  attribute: MarketplaceAttribute;
  isRequired: boolean;
  currentValue?: SavedAttributeValue;
  variationGroup: string;
  marketplaceName: string;
  shopAttributes: ShopAttributes;
  i18n: I18nStrings;
  disabled?: boolean;
  debugMode?: boolean;
  hideHelpColumn?: boolean; // Hide help column (v2 compatibility)
  onAttributeChange: (attributeKey: string, value: SavedAttributeValue) => void;
  onRemoveOptionalAttribute?: (attributeKey: string) => void;
  onFetchShopAttributeValues?: (attributeCode: string) => Promise<{ [key: string]: string }>;
}

export interface MatchingRowProps {
  attributeKey: string;
  amazonAttribute: MarketplaceAttribute;
  shopAttribute: ShopAttribute;
  rowIndex: number;
  rowData: MatchingValue;
  variationGroup: string;
  canRemove: boolean;
  disabled?: boolean;
  debugMode?: boolean;
  isLastRow: boolean;
  i18n: I18nStrings;
  onRowChange: (rowIndex: number, field: { type: string; key: string }, value: string) => void;
  onRemoveRow: (rowIndex: number) => void;
  onAddRow: () => void;
}

export interface OptionalAttributesSelectorProps {
  availableAttributes: Array<{ key: string; attribute: MarketplaceAttribute }>;
  i18n: I18nStrings;
  onAttributeSelect: (attributeKey: string) => void;
}

// Utility types for form handling
export type FormFieldName = `ml[field][variationgroups][${string}][${string}][${string}]`;
export type FormFieldValue = string | boolean | undefined;

// Event handler types
export type AttributeChangeHandler = (attributeKey: string, value: SavedAttributeValue) => void;
export type ValidationHandler = (errors: ValidationError[]) => void;
export type FormSubmitHandler = (values: SavedValues) => void | Promise<void>;

// API response types (for future integration)
export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  errors?: string[];
  message?: string;
}

export interface AttributeValidationResult {
  isValid: boolean;
  errors: ValidationError[];
}

// Hook return types
export interface UseAttributeValidationReturn {
  validateAll: () => ValidationError[];
  validateAttribute: (attributeKey: string, value: SavedAttributeValue) => ValidationError[];
  errors: ValidationError[];
  isValid: boolean;
}

export interface UseAttributeFormReturn {
  values: SavedValues;
  errors: ValidationError[];
  isSubmitting: boolean;
  isDirty: boolean;
  handleAttributeChange: AttributeChangeHandler;
  handleSubmit: (onSubmit: FormSubmitHandler) => (e: React.FormEvent) => void;
  reset: () => void;
  validate: () => boolean;
}

// Additional types for API integration
export interface UseApiIntegrationReturn {
  loading: boolean;
  error: string | null;
  submitAttributes: (values: SavedValues, variationGroup: string) => Promise<ApiResponse>;
  fetchShopAttributes: () => Promise<ShopAttributes>;
  fetchMarketplaceAttributes: (variationGroup: string) => Promise<MarketplaceAttributes>;
  fetchSavedValues: (variationGroup: string, customIdentifier: string) => Promise<SavedValues>;
  clearError: () => void;
}