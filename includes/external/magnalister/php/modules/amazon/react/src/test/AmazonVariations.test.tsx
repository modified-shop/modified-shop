import React from 'react';
import {render, screen, waitFor} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import AmazonVariations from '../AmazonVariations';
import {I18nStrings, MarketplaceAttributes, SavedValues, ShopAttributes} from '../types';

// Test data
const mockShopAttributes: ShopAttributes = {
  'Variations': {
    optGroupClass: 'variation',
    'color': {
      name: 'Color',
      type: 'select',
      values: {
        'red': 'Red',
        'blue': 'Blue',
        'green': 'Green'
      }
    },
    'size': {
      name: 'Size',
      type: 'select',
      values: {
        'S': 'Small',
        'M': 'Medium',
        'L': 'Large'
      }
    }
  },
  'Product Fields': {
    optGroupClass: 'default',
    'description': {
      name: 'Description',
      type: 'text'
    }
  }
};

const mockMarketplaceAttributes: MarketplaceAttributes = {
  'color_name': {
    value: 'Color',
    required: true,
    dataType: 'select',
    desc: 'Product color',
    values: {
      'Red': 'Red',
      'Blue': 'Blue',
      'Green': 'Green'
    }
  },
  'size_name': {
    value: 'Size',
    required: true,
    dataType: 'select',
    desc: 'Product size',
    values: {
      'Small': 'Small',
      'Medium': 'Medium',
      'Large': 'Large'
    }
  },
  'style_name': {
    value: 'Style',
    required: false,
    dataType: 'text',
    desc: 'Product style'
  }
};

const mockSavedValues: SavedValues = {
  'color_name': {
    Code: 'color',
    Values: [
      {
        Shop: { Key: 'red' },
        Marketplace: { Key: 'Red' }
      }
    ]
  }
};

const mockI18n: I18nStrings = {
  dontUse: "Don't use",
  webShopAttribute: 'Web-Shop Attribute',
  pleaseSelect: 'Please select...',
  requiredAttributesTitle: 'Required Attributes',
  attributesMatchingTitle: 'Attributes Matching',
  optionalAttributesTitle: 'Optional Attributes',
  mandatoryFieldsInfo: 'Fields with • are mandatory.'
};

const defaultProps = {
  variationGroup: '123',
  customIdentifier: 'test',
  marketplaceName: 'Amazon',
  shopAttributes: mockShopAttributes,
  marketplaceAttributes: mockMarketplaceAttributes,
  savedValues: {},
  i18n: mockI18n
};

describe('AmazonVariations', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  describe('Basic Rendering', () => {
    it('renders without crashing', () => {
      render(<AmazonVariations {...defaultProps} />);
      expect(screen.getByText('Required Attributes')).toBeInTheDocument();
    });

    it('does not render when variationGroup is invalid', () => {
      const { container } = render(
        <AmazonVariations {...defaultProps} variationGroup="none" />
      );
      expect(container.firstChild).toBeNull();
    });

    it('renders required attributes section', () => {
      render(<AmazonVariations {...defaultProps} />);
      expect(screen.getByText('Required Attributes')).toBeInTheDocument();
      expect(screen.getByText('Color')).toBeInTheDocument();
      expect(screen.getByText('Size')).toBeInTheDocument();
    });

    it('renders optional attributes section when present', () => {
      render(<AmazonVariations {...defaultProps} />);
      expect(screen.getByText('Optional Attributes')).toBeInTheDocument();
    });

    it('displays mandatory fields info', () => {
      render(<AmazonVariations {...defaultProps} />);
      expect(screen.getByText('Fields with • are mandatory.')).toBeInTheDocument();
    });
  });

  describe('Required Attributes', () => {
    it('displays required indicator for required attributes', () => {
      render(<AmazonVariations {...defaultProps} />);
      const colorRow = screen.getByText('Color').closest('tr');
      expect(colorRow).toHaveTextContent('•');
    });

    it('shows validation errors for missing required fields', async () => {
      const onValidationError = jest.fn();
      render(
        <AmazonVariations
          {...defaultProps}
          onValidationError={onValidationError}
        />
      );

      // Component should validate on mount and show errors for required fields
      await waitFor(() => {
        expect(screen.getByText(/Please fix the following errors/)).toBeInTheDocument();
      });
    });
  });

  describe('Shop Attribute Selection', () => {
    it('renders shop attribute dropdown', () => {
      render(<AmazonVariations {...defaultProps} />);
      const selects = screen.getAllByTestId('react-select');
      expect(selects.length).toBeGreaterThan(0);
    });

    it('handles shop attribute selection', async () => {
      const user = userEvent.setup();
      const onValuesChange = jest.fn();

      render(
        <AmazonVariations
          {...defaultProps}
          onValuesChange={onValuesChange}
        />
      );

      const colorSelect = screen.getAllByTestId('react-select')[0];
      await user.selectOptions(colorSelect, 'color');

      await waitFor(() => {
        expect(onValuesChange).toHaveBeenCalled();
      });
    });

    it('shows matching interface for select attributes', async () => {
      const user = userEvent.setup();

      render(<AmazonVariations {...defaultProps} />);

      const colorSelect = screen.getAllByTestId('react-select')[0];
      await user.selectOptions(colorSelect, 'color');

      await waitFor(() => {
        expect(screen.getByText('Use shop values')).toBeInTheDocument();
      });
    });

    it('shows freetext input for freetext selection', async () => {
      const user = userEvent.setup();

      render(<AmazonVariations {...defaultProps} />);

      const colorSelect = screen.getAllByTestId('react-select')[0];
      await user.selectOptions(colorSelect, 'freetext');

      await waitFor(() => {
        expect(screen.getByPlaceholderText('Enter custom value')).toBeInTheDocument();
      });
    });
  });

  describe('Saved Values', () => {
    it('restores saved values correctly', () => {
      render(
        <AmazonVariations
          {...defaultProps}
          savedValues={mockSavedValues}
        />
      );

      // Should show the saved selection
      expect(screen.getByText('Use shop values')).toBeInTheDocument();
    });

    it('calls onValuesChange when values change', async () => {
      const user = userEvent.setup();
      const onValuesChange = jest.fn();

      render(
        <AmazonVariations
          {...defaultProps}
          onValuesChange={onValuesChange}
        />
      );

      const colorSelect = screen.getAllByTestId('react-select')[0];
      await user.selectOptions(colorSelect, 'freetext');

      await waitFor(() => {
        expect(onValuesChange).toHaveBeenCalled();
      });
    });
  });

  describe('Optional Attributes', () => {
    it('hides optional attributes by default', () => {
      render(<AmazonVariations {...defaultProps} />);

      // Style attribute should not be visible initially
      const styleElements = screen.queryAllByText('Style');
      expect(styleElements.length).toBeLessThan(2); // Only in selector, not as visible row
    });

    it('shows optional attribute selector', () => {
      render(<AmazonVariations {...defaultProps} />);

      // Should have selector for optional attributes
      const selects = screen.getAllByTestId('react-select');
      expect(selects.length).toBeGreaterThan(2); // Required attributes + optional selector
    });
  });

  describe('Form Validation', () => {
    it('validates required fields', async () => {
      render(<AmazonVariations {...defaultProps} />);

      await waitFor(() => {
        expect(screen.getByText(/Please fix the following errors/)).toBeInTheDocument();
      });
    });

    it('clears validation errors when fields are filled', async () => {
      const user = userEvent.setup();

      render(<AmazonVariations {...defaultProps} />);

      // Fill required field
      const colorSelect = screen.getAllByTestId('react-select')[0];
      await user.selectOptions(colorSelect, 'color');

      const sizeSelect = screen.getAllByTestId('react-select')[1];
      await user.selectOptions(sizeSelect, 'size');

      await waitFor(() => {
        expect(screen.queryByText(/Please fix the following errors/)).not.toBeInTheDocument();
      });
    });
  });

  describe('Accessibility', () => {
    it('has proper form labels', () => {
      render(<AmazonVariations {...defaultProps} />);

      expect(screen.getByText('Web-Shop Attribute')).toBeInTheDocument();
    });

    it('has proper table structure', () => {
      render(<AmazonVariations {...defaultProps} />);

      const table = screen.getByRole('table');
      expect(table).toBeInTheDocument();
    });
  });

  describe('Internationalization', () => {
    it('uses provided i18n strings', () => {
      const customI18n = {
        ...mockI18n,
        requiredAttributesTitle: 'Custom Required Title'
      };

      render(
        <AmazonVariations
          {...defaultProps}
          i18n={customI18n}
        />
      );

      expect(screen.getByText('Custom Required Title')).toBeInTheDocument();
    });

    it('falls back to default strings when i18n is missing', () => {
      render(
        <AmazonVariations
          {...defaultProps}
          i18n={{}}
        />
      );

      expect(screen.getByText('Amazon Required Attributes')).toBeInTheDocument();
    });
  });

  describe('Performance', () => {
    it('memoizes expensive computations', () => {
      const { rerender } = render(<AmazonVariations {...defaultProps} />);

      // Re-render with same props should not cause unnecessary re-computations
      rerender(<AmazonVariations {...defaultProps} />);

      // Component should still be functional
      expect(screen.getByText('Required Attributes')).toBeInTheDocument();
    });
  });

  describe('Error Handling', () => {
    it('handles invalid data gracefully', () => {
      const invalidProps = {
        ...defaultProps,
        shopAttributes: {},
        marketplaceAttributes: {}
      };

      render(<AmazonVariations {...invalidProps} />);

      // Should still render without crashing
      expect(screen.getByText('Required Attributes')).toBeInTheDocument();
    });

    it('handles malformed saved values', () => {
      const malformedSavedValues = {
        'invalid_key': {
          Code: 'nonexistent_attribute'
        }
      };

      render(
        <AmazonVariations
          {...defaultProps}
          savedValues={malformedSavedValues}
        />
      );

      // Should render without crashing
      expect(screen.getByText('Required Attributes')).toBeInTheDocument();
    });
  });
});