/**
 * DatabaseValueInput Component (V2 Only)
 *
 * Input fields for database value matching with dropdowns for Table and Column
 * Used when 'database_value' option is selected
 *
 * Requires 3 fields:
 * - Table: Database table name (dropdown with list of tables from PHP)
 * - Column: Column name in the table (dropdown, filtered by selected table)
 * - Alias: Product ID alias/field name (text input)
 */

import React from 'react';
import {DatabaseTablesData, I18nStrings} from '../src/types';
import Select from 'react-select';

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
    databaseTables?: DatabaseTablesData; // Tables and columns from PHP
}

const DatabaseValueInput: React.FC<DatabaseValueInputProps> = ({
                                                                   value = {},
                                                                   onChange,
                                                                   disabled = false,
                                                                   i18n,
                                                                   className = '',
                                                                   databaseTables
                                                               }) => {
    // Get available columns for selected table
    const availableColumns = React.useMemo(() => {
        if (!value.Table || !databaseTables?.columns) {
            return [];
        }
        return databaseTables.columns[value.Table] || [];
    }, [value.Table, databaseTables?.columns]);

    const handleFieldChange = (field: keyof DatabaseValue, fieldValue: string) => {
        const newValue = {
            ...value,
            [field]: fieldValue
        };

        // If table changed, reset column selection
        if (field === 'Table' && fieldValue !== value.Table) {
            newValue.Column = '';
        }

        onChange(newValue);
    };

    // Convert table list to react-select options
    const tableOptions = React.useMemo(() => {
        if (!databaseTables?.tables) {
            return [];
        }
        return databaseTables.tables.map(table => ({
            value: table,
            label: table
        }));
    }, [databaseTables?.tables]);

    // Convert column list to react-select options
    const columnOptions = React.useMemo(() => {
        return availableColumns.map(column => ({
            value: column,
            label: column
        }));
    }, [availableColumns]);

    // Custom styles for react-select to match v2 form design
    const selectStyles = {
        container: (base: Record<string, unknown>) => ({
            ...base,
            flex: 1,
            maxWidth: '350px',
            fontSize: '12px'
        }),
        control: (base: Record<string, unknown>, state: { isFocused: boolean; isDisabled: boolean }) => ({
            ...base,
            minHeight: '28px',
            height: '28px',
            fontSize: '12px',
            borderColor: state.isFocused ? '#5b9dd9' : '#ddd',
            boxShadow: state.isFocused ? '0 0 2px rgba(91, 157, 217, 0.8)' : 'none',
            '&:hover': {
                borderColor: state.isFocused ? '#5b9dd9' : '#aaa'
            },
            backgroundColor: state.isDisabled ? '#f5f5f5' : '#fff',
            cursor: state.isDisabled ? 'not-allowed' : 'default'
        }),
        valueContainer: (base: Record<string, unknown>) => ({
            ...base,
            height: '28px',
            padding: '0 8px'
        }),
        input: (base: Record<string, unknown>) => ({
            ...base,
            margin: '0',
            padding: '0',
            fontSize: '12px'
        }),
        indicatorsContainer: (base: Record<string, unknown>) => ({
            ...base,
            height: '28px'
        }),
        dropdownIndicator: (base: Record<string, unknown>) => ({
            ...base,
            padding: '4px'
        }),
        clearIndicator: (base: Record<string, unknown>) => ({
            ...base,
            padding: '4px'
        }),
        menu: (base: Record<string, unknown>) => ({
            ...base,
            fontSize: '12px',
            zIndex: 9999
        }),
        option: (base: Record<string, unknown>, state: { isSelected: boolean; isFocused: boolean }) => ({
            ...base,
            fontSize: '12px',
            padding: '6px 12px',
            backgroundColor: state.isSelected ? '#5b9dd9' : state.isFocused ? '#e8f4f8' : '#fff',
            color: state.isSelected ? '#fff' : '#333',
            cursor: 'pointer',
            '&:active': {
                backgroundColor: state.isSelected ? '#5b9dd9' : '#d0e9f5'
            }
        }),
        placeholder: (base: Record<string, unknown>) => ({
            ...base,
            fontSize: '12px',
            color: '#999'
        }),
        singleValue: (base: Record<string, unknown>) => ({
            ...base,
            fontSize: '12px',
            color: '#333'
        })
    };

    // If no databaseTables provided, fallback to text inputs
    if (!databaseTables || !databaseTables.tables || databaseTables.tables.length === 0) {
        return (
            <div className={`database-value-input-container ${className}`}>
                <div style={{display: 'flex', flexDirection: 'column', gap: '8px'}}>
                    <div style={{display: 'flex', alignItems: 'center', gap: '8px'}}>
                        <label style={{minWidth: '80px', fontWeight: 'normal'}}>
                            {i18n.databaseTableLabel || 'Tabelle'}:
                        </label>
                        <input
                            type="text"
                            value={value.Table || ''}
                            onChange={(e) => handleFieldChange('Table', e.target.value)}
                            placeholder={i18n.databaseTablePlaceholder || 'Tabellenname eingeben'}
                            disabled={disabled}
                            className="database-input"
                            style={{flex: 1, maxWidth: '300px'}}
                        />
                    </div>

                    <div style={{display: 'flex', alignItems: 'center', gap: '8px'}}>
                        <label style={{minWidth: '80px', fontWeight: 'normal'}}>
                            {i18n.databaseColumnLabel || 'Spalte'}:
                        </label>
                        <input
                            type="text"
                            value={value.Column || ''}
                            onChange={(e) => handleFieldChange('Column', e.target.value)}
                            placeholder={i18n.databaseColumnPlaceholder || 'Spaltenname eingeben'}
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
                            placeholder={i18n.databaseAliasPlaceholder || 'Produkt-ID-Alias eingeben'}
                            disabled={disabled}
                            className="database-input"
                            style={{flex: 1, maxWidth: '300px'}}
                        />
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className={`database-value-input-container ${className}`}>
            <div style={{display: 'flex', flexDirection: 'column', gap: '6px', padding: '4px 0'}}>
                {/* Table Dropdown */}
                <div style={{display: 'flex', alignItems: 'center', gap: '10px'}}>
                    <label style={{
                        minWidth: '70px',
                        fontWeight: '600',
                        fontSize: '12px',
                        color: '#555',
                        textAlign: 'right'
                    }}>
                        {i18n.databaseTableLabel || 'Tabelle'}:
                    </label>
                    <Select
                        value={value.Table ? {value: value.Table, label: value.Table} : null}
                        onChange={(option) => handleFieldChange('Table', option?.value || '')}
                        options={tableOptions}
                        placeholder={i18n.databaseTablePlaceholder || 'Tabelle wählen...'}
                        isDisabled={disabled}
                        isClearable
                        isSearchable
                        className="database-table-select"
                        classNamePrefix="ml-db-table"
                        styles={selectStyles}
                        menuPortalTarget={document.body}
                        menuPosition="fixed"
                    />
                </div>

                {/* Column Dropdown */}
                <div style={{display: 'flex', alignItems: 'center', gap: '10px'}}>
                    <label style={{
                        minWidth: '70px',
                        fontWeight: '600',
                        fontSize: '12px',
                        color: '#555',
                        textAlign: 'right'
                    }}>
                        {i18n.databaseColumnLabel || 'Spalte'}:
                    </label>
                    <Select
                        value={value.Column ? {value: value.Column, label: value.Column} : null}
                        onChange={(option) => handleFieldChange('Column', option?.value || '')}
                        options={columnOptions}
                        placeholder={value.Table ? (i18n.databaseColumnPlaceholder || 'Spalte wählen...') : 'Zuerst Tabelle wählen...'}
                        isDisabled={disabled || !value.Table || availableColumns.length === 0}
                        isClearable
                        isSearchable
                        className="database-column-select"
                        classNamePrefix="ml-db-column"
                        styles={selectStyles}
                        menuPortalTarget={document.body}
                        menuPosition="fixed"
                    />
                </div>

                {/* Alias Text Input */}
                <div style={{display: 'flex', alignItems: 'center', gap: '10px'}}>
                    <label style={{
                        minWidth: '70px',
                        fontWeight: '600',
                        fontSize: '12px',
                        color: '#555',
                        textAlign: 'right'
                    }}>
                        {i18n.databaseAliasLabel || 'Alias'}:
                    </label>
                    <input
                        type="text"
                        value={value.Alias || ''}
                        onChange={(e) => handleFieldChange('Alias', e.target.value)}
                        placeholder={i18n.databaseAliasPlaceholder || 'Produkt-ID-Alias eingeben'}
                        disabled={disabled}
                        className="database-input"
                        style={{
                            flex: 1,
                            maxWidth: '350px',
                            height: '28px',
                            padding: '0 8px',
                            fontSize: '12px',
                            border: '1px solid #ddd',
                            borderRadius: '3px',
                            backgroundColor: disabled ? '#f5f5f5' : '#fff',
                            color: '#333',
                            outline: 'none',
                            transition: 'border-color 0.2s, box-shadow 0.2s',
                            boxSizing: 'border-box'
                        }}
                        onFocus={(e) => {
                            e.target.style.borderColor = '#5b9dd9';
                            e.target.style.boxShadow = '0 0 2px rgba(91, 157, 217, 0.8)';
                        }}
                        onBlur={(e) => {
                            e.target.style.borderColor = '#ddd';
                            e.target.style.boxShadow = 'none';
                        }}
                    />
                </div>
            </div>
        </div>
    );
};

export default DatabaseValueInput;