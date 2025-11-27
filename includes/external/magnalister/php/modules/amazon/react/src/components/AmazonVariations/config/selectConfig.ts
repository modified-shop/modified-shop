/**
 * Configuration for searchable select components
 */

// Search thresholds - when to use react-select vs native select
export const SEARCH_THRESHOLDS = {
  ATTRIBUTE_SELECTOR: 10,  // Shop attributes threshold
  AMAZON_VALUE_SELECTOR: 8, // Amazon values threshold
  GENERAL: 8               // Default threshold for other selects
};

// Shared styles for react-select components
export const REACT_SELECT_STYLES = {
  control: (provided: any, state: any) => ({
    ...provided,
    height: '43px',
    minHeight: '43px',
    maxHeight: '43px',
    borderRadius: '0',
    border: '1px solid #ced4da',
    fontSize: '14px',
    display: 'flex',
    alignItems: 'center',
    '&:hover': {
      borderColor: '#adb5bd'
    },
    boxShadow: state.isFocused ? '0 0 0 0.2rem rgba(0, 123, 255, 0.25)' : 'none',
    borderColor: state.isFocused ? '#80bdff' : '#ced4da'
  }),
  option: (provided: any, state: any) => ({
    ...provided,
    fontSize: '14px',
    padding: '8px 12px',
    backgroundColor: state.isSelected
      ? '#007bff'
      : state.isFocused
        ? '#f8f9fa'
        : 'white',
    color: state.isSelected ? 'white' : '#495057',
    '&:hover': {
      backgroundColor: state.isSelected ? '#007bff' : '#f8f9fa'
    }
  }),
  placeholder: (provided: any) => ({
    ...provided,
    fontSize: '14px',
    color: '#6c757d'
  }),
  singleValue: (provided: any) => ({
    ...provided,
    fontSize: '14px',
    color: '#495057'
  }),
  menu: (provided: any) => ({
    ...provided,
    zIndex: 1000,
    border: '1px solid #ced4da',
    borderRadius: '0',
    boxShadow: '0 2px 4px rgba(0,0,0,0.1)'
  }),
  menuList: (provided: any) => ({
    ...provided,
    maxHeight: '200px',
    overflowY: 'auto'
  }),
  noOptionsMessage: (provided: any) => ({
    ...provided,
    fontSize: '14px',
    color: '#6c757d',
    padding: '8px 12px'
  }),
  loadingMessage: (provided: any) => ({
    ...provided,
    fontSize: '14px',
    color: '#6c757d'
  }),
  indicatorSeparator: () => ({
    display: 'none'
  }),
  valueContainer: (provided: any) => ({
    ...provided,
    padding: '0 8px',
    height: '41px',
    display: 'flex',
    alignItems: 'center'
  }),
  input: (provided: any) => ({
    ...provided,
    margin: '0',
    padding: '0'
  })
};

// Styles for react-select with margin top (for value matching components)
export const REACT_SELECT_STYLES_WITH_MARGIN = {
  ...REACT_SELECT_STYLES,
  control: (provided: any, state: any) => ({
    ...REACT_SELECT_STYLES.control(provided, state),
    // marginTop: '8px'
  })
};

// Default react-select props
export const DEFAULT_REACT_SELECT_PROPS = {
  isSearchable: true,
  isClearable: false,
  menuPortalTarget: typeof document !== 'undefined' ? document.body : null,
  menuPosition: 'absolute' as const,
  blurInputOnSelect: true,
  closeMenuOnSelect: true,
  hideSelectedOptions: false,
  tabSelectsValue: true
};

// Configuration for different select types
export const SELECT_CONFIGS = {
  ATTRIBUTE_SELECTOR: {
    threshold: SEARCH_THRESHOLDS.ATTRIBUTE_SELECTOR,
    styles: REACT_SELECT_STYLES,
    props: {
      ...DEFAULT_REACT_SELECT_PROPS,
      isClearable: false
    }
  },
  AMAZON_VALUE_SELECTOR: {
    threshold: SEARCH_THRESHOLDS.AMAZON_VALUE_SELECTOR,
    styles: REACT_SELECT_STYLES_WITH_MARGIN,
    props: {
      ...DEFAULT_REACT_SELECT_PROPS,
      isClearable: true
    }
  }
};