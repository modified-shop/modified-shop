// Native-only entry point for UMD build
// Only exports components that don't require external dependencies (react-select, emotion, etc.)

export { default as AmazonVariations } from './AmazonVariationsNative';
export { default as AmazonVariationsNative } from './AmazonVariationsNative';

// Hooks (these don't depend on external libraries)
export { useAttributeForm } from './hooks/useAttributeForm';
export { useApiIntegration, createApiErrorHandler } from './hooks/useApiIntegration';

// Types
export type {
  // Core component props
  AmazonVariationsProps,
  AttributeRowProps,
  MatchingRowProps,
  OptionalAttributesSelectorProps,

  // Data structures
  ShopAttribute,
  ShopAttributeGroup,
  ShopAttributes,
  MarketplaceAttribute,
  MarketplaceAttributes,
  SavedAttributeValue,
  SavedValues,
  MatchingValue,
  AttributeValues,

  // UI types
  SelectOption,
  SelectOptionGroup,
  SelectOptions,
  ValidationError,
  I18nStrings,

  // Hook types
  UseAttributeFormReturn,
  UseApiIntegrationReturn,

  // API types
  ApiResponse,
  AttributeValidationResult,

  // Event handlers
  AttributeChangeHandler,
  ValidationHandler,
  FormSubmitHandler
} from './types';

// Version
export const VERSION = '1.0.0';