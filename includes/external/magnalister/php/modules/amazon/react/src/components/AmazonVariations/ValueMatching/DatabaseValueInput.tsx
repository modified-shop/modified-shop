/**
 * DatabaseValueInput Component
 *
 * Input fields for database value matching (V2 feature - backward compatible)
 * Used when 'database_value' option is selected
 *
 * Requires 3 fields:
 * - Table: Database table name (dropdown with list of tables)
 * - Column: Column name in the table (dropdown with list of columns from selected table)
 * - Alias: Product ID alias/field name (text input)
 *
 * This component is only used when database_value Code is selected.
 * It doesn't affect V3 functionality (V3 doesn't use database_value).
 */

import React from 'react';
import {I18nStrings} from '../../../types';
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
}

// Mock data for tables and columns (will be replaced with API call)
// In production, this should come from backend API
const DATABASE_TABLES = [
    'products',
    'products_description',
    'manufacturers',
    'categories',
    'products_attributes',
    'customers'
];

// Mock function to get columns for a table (will be replaced with API call)
const getTableColumns = (tableName: string): string[] => {
    // This is mock data - in production, fetch from API
    const columnMap: Record<string, string[]> = {
        'products': ['products_id', 'products_model', 'products_quantity', 'products_price', 'products_status', 'products_weight', 'products_ean'],
        'products_description': ['products_id', 'products_name', 'products_description', 'products_short_description', 'products_meta_title'],
        'manufacturers': ['manufacturers_id', 'manufacturers_name', 'manufacturers_image'],
        'categories': ['categories_id', 'parent_id', 'categories_status'],
        'products_attributes': ['products_attributes_id', 'products_id', 'options_id', 'options_values_id', 'options_values_price'],
        'customers': ['customers_id', 'customers_firstname', 'customers_lastname', 'customers_email_address']
    };
    return columnMap[tableName] || [];
};

const DatabaseValueInput: React.FC<DatabaseValueInputProps> = ({
                                                                   value = {},
                                                                   onChange,
                                                                   disabled = false,
                                                                   i18n,
                                                                   className = ''
                                                               }) => {
    // State for available columns based on selected table
    const [availableColumns, setAvailableColumns] = React.useState<string[]>([]);

    // Load columns when table changes
    React.useEffect(() => {
        if (value.Table) {
            const columns = getTableColumns(value.Table);
            setAvailableColumns(columns);
        } else {
            setAvailableColumns([]);
        }
    }, [value.Table]);

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
    const tableOptions = DATABASE_TABLES.map(table => ({
        value: table,
        label: table
    }));

    // Convert column list to react-select options
    const columnOptions = availableColumns.map(column => ({
        value: column,
        label: column
    }));

    // Custom styles for react-select to match v2 form design
    const selectStyles = {
        container: (base: any) => ({
            ...base,
            flex: 1,
            maxWidth: '350px',
            fontSize: '12px'
        }),
        control: (base: any, state: any) => ({
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
        valueContainer: (base: any) => ({
            ...base,
            height: '28px',
            padding: '0 8px'
        }),
        input: (base: any) => ({
            ...base,
            margin: '0',
            padding: '0',
            fontSize: '12px'
        }),
        indicatorsContainer: (base: any) => ({
            ...base,
            height: '28px'
        }),
        dropdownIndicator: (base: any) => ({
            ...base,
            padding: '4px'
        }),
        clearIndicator: (base: any) => ({
            ...base,
            padding: '4px'
        }),
        menu: (base: any) => ({
            ...base,
            fontSize: '12px',
            zIndex: 9999
        }),
        option: (base: any, state: any) => ({
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
        placeholder: (base: any) => ({
            ...base,
            fontSize: '12px',
            color: '#999'
        }),
        singleValue: (base: any) => ({
            ...base,
            fontSize: '12px',
            color: '#333'
        })
    };

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
                        Table:
                    </label>
                    <Select
                        value={value.Table ? {value: value.Table, label: value.Table} : null}
                        onChange={(option) => handleFieldChange('Table', option?.value || '')}
                        options={tableOptions}
                        placeholder="Select table..."
                        isDisabled={disabled}
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
                        Column:
                    </label>
                    <Select
                        value={value.Column ? {value: value.Column, label: value.Column} : null}
                        onChange={(option) => handleFieldChange('Column', option?.value || '')}
                        options={columnOptions}
                        placeholder={value.Table ? 'Select column...' : 'Select table first...'}
                        isDisabled={disabled || !value.Table || availableColumns.length === 0}
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
                        Alias:
                    </label>
                    <input
                        type="text"
                        value={value.Alias || ''}
                        onChange={(e) => handleFieldChange('Alias', e.target.value)}
                        placeholder="Enter product ID alias"
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
