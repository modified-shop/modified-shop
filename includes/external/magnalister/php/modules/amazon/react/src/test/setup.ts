import '@testing-library/jest-dom';
import {configure} from '@testing-library/react';

// Configure testing library
configure({
  testIdAttribute: 'data-testid'
});

// Mock react-window for testing
jest.mock('react-window', () => ({
  FixedSizeList: ({ children, itemCount, itemSize, ...props }: any) => {
    const items = Array.from({ length: itemCount }, (_, index) =>
      children({ index, style: { height: itemSize } })
    );
    return <div data-testid="virtualized-list" {...props}>{items}</div>;
  }
}));

// Mock react-select
jest.mock('react-select', () => {
  const React = require('react');

  return {
    __esModule: true,
    default: React.forwardRef(({
      options = [],
      value,
      onChange,
      placeholder,
      isDisabled,
      className,
      ...props
    }: any, ref: any) => {
      const flatOptions = options.flatMap((opt: any) =>
        opt.options ? opt.options : [opt]
      );

      return (
        <select
          ref={ref}
          value={value?.value || ''}
          onChange={(e) => {
            const selectedOption = flatOptions.find((opt: any) => opt.value === e.target.value);
            onChange?.(selectedOption || null);
          }}
          disabled={isDisabled}
          className={className}
          data-testid="react-select"
          {...props}
        >
          <option value="">{placeholder}</option>
          {flatOptions.map((option: any) => (
            <option
              key={option.value}
              value={option.value}
              disabled={option.isDisabled}
            >
              {option.label}
            </option>
          ))}
        </select>
      );
    })
  };
});

// Mock lodash.debounce
jest.mock('lodash.debounce', () => {
  return (fn: any) => {
    fn.cancel = jest.fn();
    return fn;
  };
});

// Mock fetch for API tests
global.fetch = jest.fn();

// Console error/warning suppression for cleaner test output
const originalError = console.error;
const originalWarn = console.warn;

beforeAll(() => {
  console.error = (...args) => {
    if (
      typeof args[0] === 'string' &&
      (args[0].includes('Warning') || args[0].includes('validateDOMNesting'))
    ) {
      return;
    }
    originalError.call(console, ...args);
  };

  console.warn = (...args) => {
    if (
      typeof args[0] === 'string' &&
      args[0].includes('componentWillReceiveProps')
    ) {
      return;
    }
    originalWarn.call(console, ...args);
  };
});

afterAll(() => {
  console.error = originalError;
  console.warn = originalWarn;
});

// Reset all mocks after each test
afterEach(() => {
  jest.clearAllMocks();
});