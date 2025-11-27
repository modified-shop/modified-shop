import React, {useCallback, useEffect, useState} from 'react';
import AmazonVariations from '../AmazonVariations';
import {useAttributeForm} from '../hooks/useAttributeForm';
import {useApiIntegration, withApiErrorBoundary} from '../hooks/useApiIntegration';
import {AmazonVariationsProps, I18nStrings, MarketplaceAttributes, SavedValues, ShopAttributes} from '../types';

interface AmazonVariationsWithFormProps extends Omit<AmazonVariationsProps, 'shopAttributes' | 'marketplaceAttributes' | 'savedValues'> {
  // Optional props that can be provided or will be fetched
  shopAttributes?: ShopAttributes;
  marketplaceAttributes?: MarketplaceAttributes;
  savedValues?: SavedValues;

  // API configuration
  apiBaseUrl?: string;
  apiHeaders?: Record<string, string>;

  // Event handlers
  onSubmitSuccess?: (data: any) => void;
  onSubmitError?: (error: Error) => void;
  onDataLoad?: (data: { shopAttributes: ShopAttributes; marketplaceAttributes: MarketplaceAttributes; savedValues: SavedValues }) => void;

  // UI customization
  showSubmitButton?: boolean;
  showResetButton?: boolean;
  submitButtonText?: string;
  resetButtonText?: string;
  loadingText?: string;

  // Auto-save functionality
  autoSave?: boolean;
  autoSaveDelay?: number;
}

/**
 * Enhanced Amazon Variations component with integrated form handling and API calls
 */
const AmazonVariationsWithForm: React.FC<AmazonVariationsWithFormProps> = ({
  variationGroup,
  customIdentifier,
  marketplaceName = 'Amazon',
  shopAttributes: providedShopAttributes,
  marketplaceAttributes: providedMarketplaceAttributes,
  savedValues: providedSavedValues,
  i18n = {},
  apiBaseUrl = '/api',
  apiHeaders = {},
  onSubmitSuccess,
  onSubmitError,
  onDataLoad,
  showSubmitButton = true,
  showResetButton = true,
  submitButtonText = 'Save Configuration',
  resetButtonText = 'Reset',
  loadingText = 'Loading...',
  autoSave = false,
  autoSaveDelay = 2000,
  className,
  disabled = false,
  ...rest
}) => {
  // State for fetched data
  const [shopAttributes, setShopAttributes] = useState<ShopAttributes>(providedShopAttributes || {});
  const [marketplaceAttributes, setMarketplaceAttributes] = useState<MarketplaceAttributes>(providedMarketplaceAttributes || {});
  const [savedValues, setSavedValues] = useState<SavedValues>(providedSavedValues || {});
  const [dataLoaded, setDataLoaded] = useState(false);

  // API integration hook
  const {
    loading: apiLoading,
    error: apiError,
    submitAttributes,
    fetchShopAttributes,
    fetchMarketplaceAttributes,
    fetchSavedValues,
    clearError
  } = useApiIntegration({
    config: {
      baseUrl: apiBaseUrl,
      headers: apiHeaders
    },
    onError: onSubmitError,
    onSuccess: onSubmitSuccess
  });

  // Form management hook
  const {
    values: formValues,
    errors: formErrors,
    isSubmitting,
    isDirty,
    handleAttributeChange,
    handleSubmit,
    reset,
    validate
  } = useAttributeForm({
    initialValues: savedValues,
    marketplaceAttributes,
    validateOnChange: true
  });

  // Load initial data
  const loadData = useCallback(async () => {
    try {
      const promises: Promise<any>[] = [];

      // Fetch shop attributes if not provided
      if (!providedShopAttributes) {
        promises.push(fetchShopAttributes().then(setShopAttributes));
      }

      // Fetch marketplace attributes if not provided
      if (!providedMarketplaceAttributes && variationGroup) {
        promises.push(fetchMarketplaceAttributes(variationGroup).then(setMarketplaceAttributes));
      }

      // Fetch saved values if not provided
      if (!providedSavedValues && variationGroup && customIdentifier) {
        promises.push(fetchSavedValues(variationGroup, customIdentifier).then(setSavedValues));
      }

      if (promises.length > 0) {
        await Promise.all(promises);
      }

      // Call onDataLoad callback
      if (onDataLoad) {
        onDataLoad({
          shopAttributes: providedShopAttributes || shopAttributes,
          marketplaceAttributes: providedMarketplaceAttributes || marketplaceAttributes,
          savedValues: providedSavedValues || savedValues
        });
      }

      setDataLoaded(true);
    } catch (error) {
      console.error('Failed to load data:', error);
    }
  }, [
    providedShopAttributes,
    providedMarketplaceAttributes,
    providedSavedValues,
    variationGroup,
    customIdentifier,
    fetchShopAttributes,
    fetchMarketplaceAttributes,
    fetchSavedValues,
    shopAttributes,
    marketplaceAttributes,
    savedValues,
    onDataLoad
  ]);

  // Load data on mount or when key props change
  useEffect(() => {
    if (variationGroup && customIdentifier) {
      loadData();
    }
  }, [loadData, variationGroup, customIdentifier]);

  // Auto-save functionality
  useEffect(() => {
    if (!autoSave || !isDirty || isSubmitting) return;

    const timeoutId = setTimeout(async () => {
      try {
        if (validate()) {
          await submitAttributes(formValues, variationGroup);
        }
      } catch (error) {
        console.error('Auto-save failed:', error);
      }
    }, autoSaveDelay);

    return () => clearTimeout(timeoutId);
  }, [autoSave, isDirty, isSubmitting, formValues, variationGroup, validate, submitAttributes, autoSaveDelay]);

  // Form submission handler
  const onSubmit = useCallback(async (values: SavedValues) => {
    try {
      const response = await submitAttributes(values, variationGroup);

      if (response.success) {
        onSubmitSuccess?.(response.data);
      } else {
        throw new Error(response.message || 'Submission failed');
      }
    } catch (error) {
      onSubmitError?.(error instanceof Error ? error : new Error('Submission failed'));
      throw error;
    }
  }, [submitAttributes, variationGroup, onSubmitSuccess, onSubmitError]);

  // Handle form reset
  const handleReset = useCallback(() => {
    reset();
    clearError();
  }, [reset, clearError]);

  // Enhanced i18n with form-specific strings
  const enhancedI18n: I18nStrings = {
    ...i18n,
    submitButton: submitButtonText,
    resetButton: resetButtonText,
    loading: loadingText,
    autoSaveIndicator: 'Auto-saving...',
    saveSuccess: 'Configuration saved successfully',
    saveFailed: 'Failed to save configuration',
    validationFailed: 'Please fix validation errors before submitting'
  };

  // Show loading state
  if (!dataLoaded || apiLoading) {
    return (
      <div className="amazon-variations-loading" style={{ textAlign: 'center', padding: '40px' }}>
        <div style={{ fontSize: '16px', marginBottom: '10px' }}>
          {loadingText}
        </div>
        <div style={{
          width: '40px',
          height: '40px',
          border: '4px solid #f3f3f3',
          borderTop: '4px solid #007bff',
          borderRadius: '50%',
          animation: 'spin 1s linear infinite',
          margin: '0 auto'
        }}></div>
        <style jsx>{`
          @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
          }
        `}</style>
      </div>
    );
  }

  // Show error state
  if (apiError) {
    return (
      <div className="amazon-variations-error" style={{
        padding: '20px',
        border: '1px solid #f5c6cb',
        borderRadius: '4px',
        backgroundColor: '#f8d7da',
        color: '#721c24'
      }}>
        <h3>Error Loading Data</h3>
        <p>{apiError}</p>
        <button
          onClick={loadData}
          style={{
            padding: '8px 16px',
            backgroundColor: '#dc3545',
            color: 'white',
            border: 'none',
            borderRadius: '4px',
            cursor: 'pointer'
          }}
        >
          Retry
        </button>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit(onSubmit)} className={`amazon-variations-form ${className || ''}`}>
      {/* Auto-save indicator */}
      {autoSave && isSubmitting && (
        <div style={{
          position: 'fixed',
          top: '20px',
          right: '20px',
          padding: '8px 12px',
          backgroundColor: '#59E28D',
          color: 'white',
          borderRadius: '4px',
          fontSize: '14px',
          zIndex: 1000
        }}>
          {enhancedI18n.autoSaveIndicator}
        </div>
      )}

      {/* Main component */}
      <AmazonVariations
        variationGroup={variationGroup}
        customIdentifier={customIdentifier}
        marketplaceName={marketplaceName}
        shopAttributes={shopAttributes}
        marketplaceAttributes={marketplaceAttributes}
        savedValues={formValues}
        i18n={enhancedI18n}
        onValuesChange={handleAttributeChange}
        onValidationError={() => {}} // Handled by form hook
        className={className}
        disabled={disabled || isSubmitting}
        {...rest}
      />

      {/* Form actions */}
      {(showSubmitButton || showResetButton) && (
        <div style={{
          marginTop: '20px',
          padding: '15px',
          borderTop: '2px solid #ccc',
          display: 'flex',
          gap: '10px',
          alignItems: 'center'
        }}>
          {showSubmitButton && (
            <button
              type="submit"
              disabled={disabled || isSubmitting || formErrors.length > 0}
              style={{
                padding: '10px 20px',
                backgroundColor: formErrors.length > 0 ? '#6c757d' : '#007bff',
                color: 'white',
                border: 'none',
                borderRadius: '4px',
                cursor: disabled || isSubmitting || formErrors.length > 0 ? 'not-allowed' : 'pointer',
                fontSize: '16px'
              }}
            >
              {isSubmitting ? 'Saving...' : submitButtonText}
            </button>
          )}

          {showResetButton && (
            <button
              type="button"
              onClick={handleReset}
              disabled={disabled || isSubmitting || !isDirty}
              style={{
                padding: '10px 20px',
                backgroundColor: '#6c757d',
                color: 'white',
                border: 'none',
                borderRadius: '4px',
                cursor: disabled || isSubmitting || !isDirty ? 'not-allowed' : 'pointer',
                fontSize: '16px'
              }}
            >
              {resetButtonText}
            </button>
          )}

          {/* Form status indicators */}
          <div style={{ marginLeft: 'auto', fontSize: '14px', color: '#6c757d' }}>
            {isDirty && !isSubmitting && (
              <span style={{ color: '#ffc107' }}>● Unsaved changes</span>
            )}
            {formErrors.length > 0 && (
              <span style={{ color: '#dc3545', marginLeft: '10px' }}>
                ⚠ {formErrors.length} error{formErrors.length !== 1 ? 's' : ''}
              </span>
            )}
            {!isDirty && !formErrors.length && (
              <span style={{ color: '#59E28D' }}>✓ All saved</span>
            )}
          </div>
        </div>
      )}

      {/* Debug info in development */}
      {process.env.NODE_ENV === 'development' && (
        <details style={{ marginTop: '20px', fontSize: '12px' }}>
          <summary>Debug Info</summary>
          <pre style={{ backgroundColor: '#f8f9fa', padding: '10px', fontSize: '11px' }}>
            {JSON.stringify({
              isDirty,
              isSubmitting,
              errorsCount: formErrors.length,
              valuesCount: Object.keys(formValues).length,
              autoSave
            }, null, 2)}
          </pre>
        </details>
      )}
    </form>
  );
};

export default withApiErrorBoundary(AmazonVariationsWithForm);