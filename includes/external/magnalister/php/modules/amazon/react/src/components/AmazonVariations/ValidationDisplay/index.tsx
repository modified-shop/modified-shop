import React from 'react';
import {I18nStrings, ValidationError} from '../../../types';

interface ValidationDisplayProps {
  errors: ValidationError[];
  i18n: I18nStrings;
  className?: string;
}

/**
 * ValidationDisplay Component
 *
 * Shows validation errors in a styled error box
 * Only renders when there are errors to display
 */
const ValidationDisplay: React.FC<ValidationDisplayProps> = ({
  errors,
  i18n,
  className = ''
}) => {
  if (errors.length === 0) {
    return null;
  }

  return (
    <div
      className={`validation-display noticeBox ml-error-box ${className}`}
    >
      <strong>{i18n.fixErrors || 'Please fix the following errors:'}</strong>
      <ul style={{ margin: '10px 0 0 20px', padding: 0 }}>
        {errors.map((error, index) => (
          <li key={`${error.key}-${index}`}>
            <span className="error-field-name">{error.name}</span>:
            <span className="error-message"> {error.message}</span>
          </li>
        ))}
      </ul>
    </div>
  );
};

export default ValidationDisplay;