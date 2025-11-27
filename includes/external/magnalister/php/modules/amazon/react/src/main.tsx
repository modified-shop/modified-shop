import React from 'react'
import ReactDOM from 'react-dom/client'
import AmazonVariations from './AmazonVariations'
import type {AmazonVariationsProps} from './types'
import './main.css'

// Mock data for development
const mockShopAttributes = {
  "Shop Attributes": {
    optGroupClass: "shop-attributes",
    "brand": {
      name: "Brand",
      type: "select" as const
      // No values - will trigger lazy loading
    },
    "color": {
      name: "Color",
      type: "select" as const
      // No values - will trigger lazy loading
    },
    "size": {
      name: "Size",
      type: "select" as const
      // No values - will trigger lazy loading
    },
    "sp_Supplier": {
      name: "Hersteller",
      type: "select" as const
      // No values - will trigger lazy loading
    },
    "a_productstatus": {
      name: "Status",
      type: "select" as const
      // No values - will trigger lazy loading
    },
    "material": {
      name: "Material",
      type: "text" as const
    }
  },
  "Product Details": {
    optGroupClass: "product-details",
    "description": {
      name: "Product Description",
      type: "text" as const
    },
    "weight": {
      name: "Weight",
      type: "text" as const
    },
    "category": {
      name: "Category",
      type: "select" as const
      // No values - will trigger lazy loading
    }
  }
}

const mockMarketplaceAttributes = {
  "Color": {
    value: "Color",
    required: true,
    dataType: "select" as const,
    desc: "The color of the product",
    values: {
      "Red": "Red",
      "Blue": "Blue",
      "Green": "Green",
      "Black": "Black",
      "White": "White"
    }
  },
  "Size": {
    value: "Size",
    required: true,
    dataType: "select" as const,
    desc: "Size of the product",
    values: {
      "XS": "Extra Small",
      "S": "Small",
      "M": "Medium",
      "L": "Large",
      "XL": "Extra Large",
      "XXL": "Extra Extra Large"
    }
  },
  "Brand": {
    value: "Brand",
    required: false,
    dataType: "select" as const,
    desc: "Brand or manufacturer",
    values: {
      "Nike": "Nike",
      "Adidas": "Adidas",
      "Puma": "Puma",
      "Under Armour": "Under Armour"
    }
  },
  "Material": {
    value: "Material",
    required: false,
    dataType: "text" as const,
    desc: "Material composition"
  },
  "Pattern": {
    value: "Pattern",
    required: false,
    dataType: "selectandtext" as const,
    desc: "Pattern or design",
    values: {
      "Solid": "Solid Color",
      "Striped": "Striped",
      "Floral": "Floral"
    }
  }
}

const mockSavedValues = {
  "Color": {
    Code: "color",
    Values: {
      FreeText: "",
      AttributeValue: ""
    }
  },
  "Size": {
    Code: "size",
    Values: {
      FreeText: "",
      AttributeValue: ""
    }
  }
}

const mockI18n = {
  dontUse: "Don't use",
  webShopAttribute: "Web-Shop Attribute",
  pleaseSelect: "Please select...",
  enterFreetext: "Enter custom value",
  useAttributeValue: "Use Amazon attribute value",
  selectAmazonValue: "Select Amazon value...",
  enterAmazonValue: "Enter Amazon value",
  useShopValues: "Use shop values",
  shopValue: "Shop Value",
  marketplaceValue: "Marketplace Value",
  autoMatching: "Auto Matching",
  manualMatching: "Manual Matching",
  requiredAttributesTitle: "Amazon Required Attributes",
  attributesMatchingTitle: "Attributes Matching",
  optionalAttributesTitle: "Amazon Optional Attributes",
  optionalAttributeMatching: "Optional Attribute Matching",
  mandatoryFieldsInfo: "Fields with • are mandatory fields from Amazon.",
  requiredField: "Required field must be assigned",
  fixErrors: "Please fix the following errors:",
  additionalOptions: "Additional Options",
  freetext: "Enter custom value",
  submitButton: "Save",
  loading: "Loading...",
  saveSuccess: "Saved successfully",
  saveFailed: "Save failed",
  validationFailed: "Validation failed"
}

// Mock function to simulate the AJAX call for development
const mockFetchShopAttributeValues = async (attributeCode: string): Promise<{ [key: string]: string }> => {
  console.log(`🔄 [MOCK AJAX] Fetching values for attribute: ${attributeCode}`);

  // Simulate network delay
  await new Promise(resolve => setTimeout(resolve, 1000));

  // Mock data based on attribute code
  const mockValues: { [key: string]: { [key: string]: string } } = {
    'sp_Supplier': {
      'supplier_1': 'Acme Corporation',
      'supplier_2': 'Best Products Ltd',
      'supplier_3': 'Global Supplies Inc',
      'supplier_4': 'Quality Manufacturing Co',
      'supplier_5': 'Premium Materials LLC'
    },
    'a_productstatus': {
      'active': 'Active',
      'inactive': 'Inactive',
      'draft': 'Draft',
      'pending': 'Pending Review',
      'discontinued': 'Discontinued'
    },
    'brand': {
      'nike': 'Nike',
      'adidas': 'Adidas',
      'puma': 'Puma',
      'under_armour': 'Under Armour',
      'reebok': 'Reebok'
    },
    'color': {
      'red': 'Red',
      'blue': 'Blue',
      'green': 'Green',
      'black': 'Black',
      'white': 'White',
      'yellow': 'Yellow',
      'purple': 'Purple'
    },
    'size': {
      's': 'Small',
      'm': 'Medium',
      'l': 'Large',
      'xl': 'Extra Large',
      'xxl': 'XXL'
    },
    'category': {
      'clothing': 'Clothing',
      'shoes': 'Shoes',
      'accessories': 'Accessories',
      'electronics': 'Electronics',
      'sports': 'Sports & Outdoors'
    }
  };

  const values = mockValues[attributeCode] || {};
  console.log(`✅ [MOCK AJAX] Loaded ${Object.keys(values).length} values for ${attributeCode}:`, values);

  return values;
};

const mockProps: AmazonVariationsProps = {
  variationGroup: "clothing_size_color",
  customIdentifier: "dev_test_123",
  marketplaceName: "Amazon",
  shopAttributes: mockShopAttributes,
  marketplaceAttributes: mockMarketplaceAttributes,
  savedValues: mockSavedValues,
  i18n: mockI18n,
  onValuesChange: (values) => {
    console.log('Values changed:', values)
  },
  onValidationError: (errors) => {
    console.log('Validation errors:', errors)
  },
  onFetchShopAttributeValues: mockFetchShopAttributeValues
}

// Render the component
ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <div style={{ padding: '20px', maxWidth: '1200px', margin: '0 auto' }}>
      <header style={{ marginBottom: '30px' }}>
        <h1 style={{ color: '#333', marginBottom: '10px' }}>
          Amazon Variations React Component - Development
        </h1>
        <p style={{ color: '#666', fontSize: '14px' }}>
          This is the standalone React component running in development mode with mock data.
        </p>
      </header>

      <AmazonVariations {...mockProps} />

      <footer style={{ marginTop: '40px', padding: '20px', background: '#f5f5f5', borderRadius: '4px' }}>
        <h3>Development Info</h3>
        <p><strong>Variation Group:</strong> {mockProps.variationGroup}</p>
        <p><strong>Component Version:</strong> Development Build</p>
        <p><strong>React Version:</strong> {React.version}</p>
      </footer>
    </div>
  </React.StrictMode>
)