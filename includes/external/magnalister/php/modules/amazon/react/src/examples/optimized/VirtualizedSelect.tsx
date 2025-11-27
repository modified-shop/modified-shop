import React, {useCallback, useMemo, useRef, useState} from 'react';
import Select, {components, MenuListProps, OptionProps} from 'react-select';
import {FixedSizeList as List} from 'react-window';
import {SelectOption, SelectOptions} from '../../types';

interface VirtualizedSelectProps {
  options: SelectOptions;
  value?: SelectOption | null;
  onChange?: (option: SelectOption | null) => void;
  placeholder?: string;
  isDisabled?: boolean;
  isSearchable?: boolean;
  className?: string;
  name?: string;
  id?: string;
  maxHeight?: number;
  itemHeight?: number;
  threshold?: number; // Minimum items before virtualization kicks in
}

// Flatten grouped options for virtualization
const flattenOptions = (options: SelectOptions): SelectOption[] => {
  return options.flatMap(option =>
    'options' in option ? option.options : [option]
  );
};

// Virtual list item component
const VirtualizedOption: React.FC<OptionProps<SelectOption>> = (props) => {
  const { children, ...rest } = props;
  return (
    <components.Option {...rest}>
      {children}
    </components.Option>
  );
};

// Virtualized menu list component
const VirtualizedMenuList: React.FC<MenuListProps<SelectOption> & {
  maxHeight: number;
  itemHeight: number;
}> = (props) => {
  const { options, children, maxHeight, getValue, itemHeight } = props;
  const [value] = getValue();
  const initialOffset = options.indexOf(value) * itemHeight;

  const listRef = useRef<List>(null);

  // Scroll to selected item when menu opens
  React.useEffect(() => {
    if (listRef.current && value) {
      const index = options.findIndex(option => option.value === value.value);
      if (index >= 0) {
        listRef.current.scrollToItem(index, 'center');
      }
    }
  }, [value, options]);

  if (!children || !Array.isArray(children)) {
    return <div>{children}</div>;
  }

  const height = Math.min(maxHeight, children.length * itemHeight);

  const Row = ({ index, style }: { index: number; style: React.CSSProperties }) => (
    <div style={style}>
      {children[index]}
    </div>
  );

  return (
    <List
      ref={listRef}
      height={height}
      itemCount={children.length}
      itemSize={itemHeight}
      initialScrollOffset={initialOffset}
      style={{ maxHeight }}
    >
      {Row}
    </List>
  );
};

/**
 * Virtualized Select component for handling large option lists efficiently
 */
const VirtualizedSelect: React.FC<VirtualizedSelectProps> = ({
  options,
  value,
  onChange,
  placeholder = 'Select...',
  isDisabled = false,
  isSearchable = true,
  className,
  name,
  id,
  maxHeight = 200,
  itemHeight = 35,
  threshold = 100
}) => {
  const [inputValue, setInputValue] = useState('');

  // Flatten options for processing
  const flatOptions = useMemo(() => flattenOptions(options), [options]);

  // Filter options based on input
  const filteredOptions = useMemo(() => {
    if (!inputValue) return options;

    const searchTerm = inputValue.toLowerCase();
    const filtered = flatOptions.filter(option =>
      option.label.toLowerCase().includes(searchTerm) ||
      option.value.toLowerCase().includes(searchTerm)
    );

    return filtered;
  }, [options, flatOptions, inputValue]);

  // Determine if we should use virtualization
  const shouldVirtualize = flatOptions.length >= threshold;

  // Handle input change for search
  const handleInputChange = useCallback((newValue: string) => {
    setInputValue(newValue);
  }, []);

  // Custom components for virtualization
  const customComponents = useMemo(() => {
    if (!shouldVirtualize) {
      return {};
    }

    return {
      MenuList: (props: MenuListProps<SelectOption>) => (
        <VirtualizedMenuList
          {...props}
          maxHeight={maxHeight}
          itemHeight={itemHeight}
        />
      ),
      Option: VirtualizedOption
    };
  }, [shouldVirtualize, maxHeight, itemHeight]);

  // Performance optimized styles
  const customStyles = useMemo(() => ({
    menuList: (base: any) => ({
      ...base,
      maxHeight: shouldVirtualize ? maxHeight : 200,
      padding: 0
    }),
    option: (base: any, state: any) => ({
      ...base,
      height: itemHeight,
      display: 'flex',
      alignItems: 'center',
      cursor: state.isDisabled ? 'not-allowed' : 'pointer',
      backgroundColor: state.isSelected
        ? '#007bff'
        : state.isFocused
        ? '#f8f9fa'
        : 'transparent',
      color: state.isSelected ? 'white' : 'inherit',
      ':hover': {
        backgroundColor: state.isSelected ? '#007bff' : '#f8f9fa'
      }
    }),
    control: (base: any, state: any) => ({
      ...base,
      borderColor: state.isFocused ? '#007bff' : '#ced4da',
      boxShadow: state.isFocused ? '0 0 0 0.2rem rgba(0,123,255,.25)' : 'none',
      '&:hover': {
        borderColor: '#007bff'
      }
    }),
    placeholder: (base: any) => ({
      ...base,
      color: '#6c757d'
    }),
    loadingIndicator: (base: any) => ({
      ...base,
      color: '#007bff'
    })
  }), [shouldVirtualize, maxHeight, itemHeight]);

  return (
    <Select<SelectOption>
      id={id}
      name={name}
      className={className}
      options={filteredOptions as any}
      value={value}
      onChange={onChange}
      onInputChange={handleInputChange}
      inputValue={inputValue}
      placeholder={placeholder}
      isDisabled={isDisabled}
      isSearchable={isSearchable}
      components={customComponents}
      styles={customStyles}
      filterOption={() => true} // We handle filtering manually
      menuPlacement="auto"
      menuPosition="absolute"
      blurInputOnSelect
      captureMenuScroll
      closeMenuOnScroll={(event) => {
        // Keep menu open when scrolling within the menu
        return event.target === document;
      }}
      // Performance optimizations
      isClearable={false}
      isLoading={false}
      menuShouldScrollIntoView={false}
      // Accessibility
      aria-label={placeholder}
      aria-describedby={`${id}-help`}
    />
  );
};

export default React.memo(VirtualizedSelect, (prevProps, nextProps) => {
  // Custom comparison for better performance
  return (
    prevProps.value?.value === nextProps.value?.value &&
    prevProps.isDisabled === nextProps.isDisabled &&
    prevProps.options === nextProps.options &&
    prevProps.placeholder === nextProps.placeholder
  );
});