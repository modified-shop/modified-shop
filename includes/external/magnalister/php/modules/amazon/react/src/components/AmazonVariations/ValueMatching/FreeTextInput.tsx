import React from 'react';
import {I18nStrings} from '../../../types';

interface FreeTextInputProps {
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
  disabled?: boolean;
  i18n: I18nStrings;
  className?: string;
}

/**
 * FreeTextInput Component
 *
 * Simple text input for custom values
 * Used when 'freetext' option is selected
 */
const FreeTextInput: React.FC<FreeTextInputProps> = ({
  value,
  onChange,
  placeholder,
  disabled = false,
  i18n,
  className = ''
}) => {
  return (
    <div className={`freetext-input-container ${className}`}>
      <input
        type="text"
        value={value || ''}
        onChange={(e) => onChange(e.target.value)}
        placeholder={placeholder || i18n.enterFreetext || 'Enter custom value'}
        disabled={disabled}
        className="freetext-input"
      />
    </div>
  );
};

export default FreeTextInput;