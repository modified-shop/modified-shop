import {useCallback, useEffect, useRef, useState} from 'react';
import {
    FormSubmitHandler,
    MarketplaceAttributes,
    SavedAttributeValue,
    SavedValues,
    UseAttributeFormReturn,
    ValidationError
} from '../types';

interface UseAttributeFormProps {
  initialValues: SavedValues;
  marketplaceAttributes: MarketplaceAttributes;
  onSubmit?: FormSubmitHandler;
  validateOnChange?: boolean;
}

/**
 * Custom hook for managing Amazon Variations form state and validation
 */
export const useAttributeForm = ({
  initialValues = {},
  marketplaceAttributes = {},
  onSubmit,
  validateOnChange = true
}: UseAttributeFormProps): UseAttributeFormReturn => {
  const [values, setValues] = useState<SavedValues>(initialValues);
  const [errors, setErrors] = useState<ValidationError[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isDirty, setIsDirty] = useState(false);
  const initialValuesRef = useRef(initialValues);

  // Update initial values ref when it changes
  useEffect(() => {
    initialValuesRef.current = initialValues;
    setValues(initialValues);
    setIsDirty(false);
  }, [initialValues]);

  // Validate a single attribute
  const validateAttribute = useCallback((
    attributeKey: string,
    value: SavedAttributeValue
  ): ValidationError[] => {
    const attribute = marketplaceAttributes[attributeKey];
    const fieldErrors: ValidationError[] = [];

    if (!attribute) return fieldErrors;

    // Required field validation
    if (attribute.required && (!value?.Code || value.Code === '')) {
      fieldErrors.push({
        key: attributeKey,
        name: attribute.value || attributeKey,
        message: 'This field is required'
      });
    }

    // Value-specific validation
    if (value?.Code && value.Code !== '') {
      // Validate freetext values
      if (value.Code === 'freetext' && value.Values) {
        const freeTextValue = (value.Values as any)?.FreeText;
        if (!freeTextValue || freeTextValue.trim() === '') {
          fieldErrors.push({
            key: attributeKey,
            name: attribute.value || attributeKey,
            message: 'Free text value cannot be empty'
          });
        }
      }

      // Validate attribute values
      if (value.Code === 'attribute_value' && value.Values) {
        const attrValue = (value.Values as any)?.AttributeValue;
        if (!attrValue || attrValue === '') {
          fieldErrors.push({
            key: attributeKey,
            name: attribute.value || attributeKey,
            message: 'Attribute value must be selected or entered'
          });
        }
      }

      // Validate matching values
      if (Array.isArray(value.Values)) {
        const matchingValues = value.Values;
        let hasValidMatching = false;

        matchingValues.forEach((matching, index) => {
          if (matching.Shop?.Key && matching.Shop.Key !== 'noselection' &&
              matching.Marketplace?.Key && matching.Marketplace.Key !== 'noselection') {
            hasValidMatching = true;
          }
        });

        if (matchingValues.length > 0 && !hasValidMatching) {
          fieldErrors.push({
            key: attributeKey,
            name: attribute.value || attributeKey,
            message: 'At least one valid attribute matching is required'
          });
        }
      }
    }

    return fieldErrors;
  }, [marketplaceAttributes]);

  // Validate all attributes
  const validateAll = useCallback((): ValidationError[] => {
    const allErrors: ValidationError[] = [];

    Object.entries(values).forEach(([attributeKey, value]) => {
      const fieldErrors = validateAttribute(attributeKey, value);
      allErrors.push(...fieldErrors);
    });

    // Also validate required fields that might not be in values yet
    Object.entries(marketplaceAttributes).forEach(([attributeKey, attribute]) => {
      if (attribute.required && !values[attributeKey]?.Code) {
        allErrors.push({
          key: attributeKey,
          name: attribute.value || attributeKey,
          message: 'This field is required'
        });
      }
    });

    setErrors(allErrors);
    return allErrors;
  }, [values, marketplaceAttributes, validateAttribute]);

  // Handle attribute change
  const handleAttributeChange = useCallback((
    attributeKey: string,
    newValue: SavedAttributeValue
  ) => {
    setValues(prev => {
      const updated = {
        ...prev,
        [attributeKey]: newValue
      };

      // Check if form is dirty
      const isDirtyNow = JSON.stringify(updated) !== JSON.stringify(initialValuesRef.current);
      setIsDirty(isDirtyNow);

      return updated;
    });

    // Validate on change if enabled
    if (validateOnChange) {
      const fieldErrors = validateAttribute(attributeKey, newValue);
      setErrors(prev => {
        // Remove existing errors for this field
        const filteredErrors = prev.filter(error => error.key !== attributeKey);
        // Add new errors
        return [...filteredErrors, ...fieldErrors];
      });
    }
  }, [validateAttribute, validateOnChange]);

  // Handle form submission
  const handleSubmit = useCallback((onSubmitCallback: FormSubmitHandler) => {
    return async (e: React.FormEvent) => {
      e.preventDefault();

      if (isSubmitting) return;

      setIsSubmitting(true);

      try {
        // Validate all fields
        const validationErrors = validateAll();

        if (validationErrors.length > 0) {
          setIsSubmitting(false);
          return;
        }

        // Call the provided submit handler
        await onSubmitCallback(values);

        // Reset dirty state on successful submission
        setIsDirty(false);
        initialValuesRef.current = values;
      } catch (error) {
        console.error('Form submission error:', error);
      } finally {
        setIsSubmitting(false);
      }
    };
  }, [isSubmitting, validateAll, values]);

  // Reset form to initial values
  const reset = useCallback(() => {
    setValues(initialValuesRef.current);
    setErrors([]);
    setIsDirty(false);
    setIsSubmitting(false);
  }, []);

  // Validate form and return boolean
  const validate = useCallback((): boolean => {
    const validationErrors = validateAll();
    return validationErrors.length === 0;
  }, [validateAll]);

  return {
    values,
    errors,
    isSubmitting,
    isDirty,
    handleAttributeChange,
    handleSubmit,
    reset,
    validate
  };
};