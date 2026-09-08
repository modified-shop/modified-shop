// Bundle entry point that exports React and ReactDOM to global scope
import React from 'react';
import ReactDOM from 'react-dom/client';

// Import component CSS to include in bundle
import './components/AmazonVariations/styles.css';

// Import the main components
import AmazonVariationsComponent from './AmazonVariations';
import CustomAttributeRowComponent from './components/ebay/CustomAttributeRow';

// Export React and ReactDOM to global scope for compatibility.
// Also save references under __magnalister* names that cannot be overwritten
// by host pages (e.g. WordPress loading its own React via var declaration).
// The PHP rendering code (variations.php) uses __magnalisterReact to avoid dual-instance crashes.
if (typeof window !== 'undefined') {
  (window as any).React = React;
  (window as any).ReactDOM = ReactDOM;

  // Safe references that survive WordPress React overwriting window.React
  (window as any).__magnalisterReact = React;
  (window as any).__magnalisterReactDOM = ReactDOM;

  // Explicitly export components to window for PHP access
  // This namespace is used by all marketplaces
  (window as any).MagnalisterVariations = {
    // Amazon components
    AmazonVariations: AmazonVariationsComponent,
    // eBay uses the same component with enableCustomAttributes=true
    EbayVariations: AmazonVariationsComponent,
    // eBay custom attribute row component
    EbayCustomAttributeRow: CustomAttributeRowComponent,
    // Export React version for debugging
    version: React.version,
    // Internal flag to check if our bundle loaded
    __bundleLoaded: true
  };

  // Legacy export for backward compatibility with existing Amazon code
  // PHP expects: window.MagnalisterAmazonVariations.AmazonVariations
  (window as any).MagnalisterAmazonVariations = {
    AmazonVariations: AmazonVariationsComponent,
    version: React.version,
    __bundleLoaded: true
  };
}

// Export our main components as named exports
export { AmazonVariationsComponent as AmazonVariations };
export { CustomAttributeRowComponent as EbayCustomAttributeRow };

// Export as default for UMD global access (Amazon component for backward compatibility)
export default AmazonVariationsComponent;

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

// eBay components exports
export type {
  CustomAttributeValue,
  CustomAttributeRowProps
} from './components/ebay/CustomAttributeRow';

// Version
export const VERSION = '1.0.0';
