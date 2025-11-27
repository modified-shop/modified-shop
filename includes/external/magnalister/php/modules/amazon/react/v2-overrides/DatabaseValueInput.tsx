/**
 * DatabaseValueInput Component (V2 Only)
 *
 * Input fields for database value matching
 * Used when 'database_value' option is selected
 *
 * Requires 3 fields:
 * - Table: Database table name
 * - Column: Column name in the table
 * - Alias: Product ID alias/field name
 */

import React from 'react';
import {I18nStrings} from '../../../types';

export interface DatabaseValue {
    Table?: string;
    Column?: string;
    Alias?: string;
}

interface DatabaseValueInputProps {
    value: DatabaseValue;
    onChange: (value: DatabaseValue) => void;
    disabled?: boolean;
    i18n: I18nStrings;
    className?: string;
}

const DatabaseValueInput: React.FC<DatabaseValueInputProps> = ({
                                                                   value = {},
                                                                   onChange,
                                                                   disabled = false,
                                                                   i18n,
                                                                   className = ''
                                                               }) => {
    const handleFieldChange = (field: keyof DatabaseValue, fieldValue: string) => {
        onChange({
            ...value,
            [field]: fieldValue
        });
    };

    return (
        <div className={`database-value-input-container ${className}`}>
            <div style={{display: 'flex', flexDirection: 'column', gap: '8px'}}>
                <div style={{display: 'flex', alignItems: 'center', gap: '8px'}}>
                    <label style={{minWidth: '80px', fontWeight: 'normal'}}>
                        {i18n.databaseTableLabel || 'Table'}:
                    </label>
                    <input
                        type="text"
                        value={value.Table || ''}
                        onChange={(e) => handleFieldChange('Table', e.target.value)}
                        placeholder={i18n.databaseTablePlaceholder || 'Enter table name'}
                        disabled={disabled}
                        className="database-input"
                        style={{flex: 1, maxWidth: '300px'}}
                    />
                </div>

                <div style={{display: 'flex', alignItems: 'center', gap: '8px'}}>
                    <label style={{minWidth: '80px', fontWeight: 'normal'}}>
                        {i18n.databaseColumnLabel || 'Column'}:
                    </label>
                    <input
                        type="text"
                        value={value.Column || ''}
                        onChange={(e) => handleFieldChange('Column', e.target.value)}
                        placeholder={i18n.databaseColumnPlaceholder || 'Enter column name'}
                        disabled={disabled}
                        className="database-input"
                        style={{flex: 1, maxWidth: '300px'}}
                    />
                </div>

                <div style={{display: 'flex', alignItems: 'center', gap: '8px'}}>
                    <label style={{minWidth: '80px', fontWeight: 'normal'}}>
                        {i18n.databaseAliasLabel || 'Alias'}:
                    </label>
                    <input
                        type="text"
                        value={value.Alias || ''}
                        onChange={(e) => handleFieldChange('Alias', e.target.value)}
                        placeholder={i18n.databaseAliasPlaceholder || 'Enter product ID alias'}
                        disabled={disabled}
                        className="database-input"
                        style={{flex: 1, maxWidth: '300px'}}
                    />
                </div>
            </div>
        </div>
    );
};

export default DatabaseValueInput;
