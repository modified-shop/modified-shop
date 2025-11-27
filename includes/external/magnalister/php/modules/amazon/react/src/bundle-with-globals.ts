// Bundle entry point that exports React and ReactDOM to global scope
import React from 'react';
import ReactDOM from 'react-dom/client';
// Also export the legacy ReactDOM.render for React 17 compatibility
import {render} from 'react-dom';

// Import component CSS to include in bundle
import './components/AmazonVariations/styles.css';

// Import the main component
import AmazonVariationsComponent from './AmazonVariations';

// Export React and ReactDOM to global scope for compatibility
// Only set if not already exists to avoid conflicts with existing React installations
if (typeof window !== 'undefined') {
  if (!(window as any).React) {
    (window as any).React = React;
  }
  if (!(window as any).ReactDOM) {
    (window as any).ReactDOM = ReactDOM;
  }
  if (!(window as any).ReactDOM.render) {
    (window as any).ReactDOM.render = render;
  }

  // Explicitly export component to window for PHP access
  // PHP expects: window.MagnalisterAmazonVariations.AmazonVariations
  (window as any).MagnalisterAmazonVariations = {
    AmazonVariations: AmazonVariationsComponent,
    // Export React version for debugging
    version: '18.2.0',
    // Internal flag to check if our bundle loaded
    __bundleLoaded: true
  };
}

// Export our main component as named export
export { AmazonVariationsComponent as AmazonVariations };

// Export as default for UMD global access
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

// Version
export const VERSION = '1.0.0';
