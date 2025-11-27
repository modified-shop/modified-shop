import React, {useState} from 'react';
import AmazonVariations from './AmazonVariations';

// Example data structure based on the original PHP implementation
const exampleShopAttributes = {
  "Varianten": {
    "optGroupClass": "variation",
    "c_7": {
      "name": "Varianten: Ausstattung",
      "type": "select",
      "values": {
        "10": "Full",
        "9": "Half",
        "8": "Zero"
      }
    },
    "c_5": {
      "name": "Color",
      "type": "select",
      "values": {
        "red": "Red",
        "blue": "Blue",
        "green": "Green"
      }
    },
    "c_6": {
      "name": "Size",
      "type": "select",
      "values": {
        "S": "Small",
        "M": "Medium",
        "L": "Large"
      }
    }
  },
  "Produkt-Standardfelder": {
    "optGroupClass": "default",
    "p_articleName": {
      "name": "Artikel-Bezeichnung",
      "type": "text"
    },
    "pd_Number": {
      "name": "Artikelnummer",
      "type": "text"
    },
    "p_description": {
      "name": "Kurzbeschreibung",
      "type": "text"
    }
  },
  "Eigenschaften": {
    "optGroupClass": "property",
    "pp_1": {
      "name": "Size",
      "type": "multiSelect",
      "values": {
        "XS": "Extra Small",
        "S": "Small",
        "M": "Medium",
        "L": "Large",
        "XL": "Extra Large"
      }
    },
    "pp_2": {
      "name": "Material",
      "type": "multiSelect",
      "values": {
        "cotton": "Cotton",
        "polyester": "Polyester",
        "wool": "Wool"
      }
    }
  },
  "Weitere Optionen": {
    "optGroupClass": "additionalOptions"
  }
};

const exampleMarketplaceAttributes = {
  "size_name": {
    "value": "Size",
    "required": true,
    "dataType": "select",
    "desc": "The size of the item",
    "values": {
      "XS": "Extra Small",
      "S": "Small",
      "M": "Medium",
      "L": "Large",
      "XL": "Extra Large",
      "XXL": "Extra Extra Large"
    }
  },
  "color_name": {
    "value": "Color",
    "required": true,
    "dataType": "select",
    "desc": "The color of the item",
    "values": {
      "Red": "Red",
      "Blue": "Blue",
      "Green": "Green",
      "Black": "Black",
      "White": "White"
    }
  },
  "material_type": {
    "value": "Material",
    "required": false,
    "dataType": "select",
    "desc": "The material the item is made from",
    "values": {
      "cotton": "Cotton",
      "polyester": "Polyester",
      "wool": "Wool",
      "silk": "Silk"
    }
  },
  "style_name": {
    "value": "Style",
    "required": false,
    "dataType": "text",
    "desc": "The style description of the item"
  }
};

const exampleSavedValues = {
  "size_name": {
    "Code": "pp_1",
    "Values": [
      {
        "Shop": { "Key": "S" },
        "Marketplace": { "Key": "S" }
      },
      {
        "Shop": { "Key": "M" },
        "Marketplace": { "Key": "M" }
      }
    ]
  },
  "color_name": {
    "Code": "freetext",
    "Values": { "FreeText": "Custom Color Description" }
  }
};

const exampleI18n = {
  dontUse: "Don't use",
  webShopAttribute: "Web-Shop Attribute",
  pleaseSelect: "Please select...",
  enterFreetext: "Enter custom value",
  useAttributeValue: "Use Amazon attribute value",
  selectAmazonValue: "Select Amazon value...",
  enterAmazonValue: "Enter Amazon value",
  useShopValues: "Use shop values",
  shopValue: "Shop Value",
  marketplaceValue: "Amazon Value",
  autoMatching: "Auto-Matching",
  manualMatching: "Manual entry",
  requiredAttributesTitle: "Amazon Required Attributes",
  attributesMatchingTitle: "Attributes Matching",
  optionalAttributesTitle: "Amazon Optional Attributes",
  optionalAttributeMatching: "Optional Attribute and Value Matching",
  optionalAttributeInfo: "Select an optional Amazon attribute to match with your shop attributes",
  mandatoryFieldsInfo: "Fields with • are mandatory fields from Amazon.",
  requiredField: "Required field must be assigned",
  fixErrors: "Please fix the following errors:"
};

const AmazonVariationsExample = () => {
  const [formData, setFormData] = useState({});

  const handleFormSubmit = (e) => {
    e.preventDefault();
    console.log('Form submitted with data:', formData);
  };

  return (
    <div style={{ padding: '20px', maxWidth: '1200px', margin: '0 auto' }}>
      <h1>Amazon Variations React Component Example</h1>

      <div style={{ marginBottom: '20px', padding: '15px', backgroundColor: '#f5f5f5', borderRadius: '5px' }}>
        <h3>Instructions:</h3>
        <ul>
          <li>This component converts the PHP variations.php into a React component</li>
          <li>Required attributes (marked with •) must be filled</li>
          <li>Optional attributes can be added using the selector at the bottom</li>
          <li>Different shop attribute types trigger different matching interfaces</li>
          <li>The component handles form validation and state management</li>
        </ul>
      </div>

      <form onSubmit={handleFormSubmit}>
        <AmazonVariations
          variationGroup="443"
          customIdentifier="test"
          marketplaceName="Amazon"
          shopAttributes={exampleShopAttributes}
          marketplaceAttributes={exampleMarketplaceAttributes}
          savedValues={exampleSavedValues}
          i18n={exampleI18n}
        />

        <div style={{ marginTop: '20px', padding: '15px', borderTop: '2px solid #ccc' }}>
          <button type="submit" className="mlbtn">
            Save Configuration
          </button>
          <button
            type="button"
            className="mlbtn"
            style={{ marginLeft: '10px' }}
            onClick={() => console.log('Current form state:', formData)}
          >
            Debug State
          </button>
        </div>
      </form>

      {/* CSS Styles for the component */}
      <style jsx>{`
        .attributesTable {
          width: 100%;
          border-collapse: collapse;
          margin: 20px 0;
        }

        .attributesTable th,
        .attributesTable td {
          padding: 10px;
          border: 1px solid #ddd;
          text-align: left;
        }

        .attributesTable th {
          background-color: #f8f9fa;
          font-weight: bold;
        }

        .attributesTable .headline th {
          background-color: #e9ecef;
          text-align: center;
        }

        .attributesTable .headline h4 {
          margin: 5px 0;
          font-size: 14px;
        }

        .attributesTable .spacer td {
          padding: 5px;
          border: none;
        }

        .mlbtn {
          padding: 8px 16px;
          background-color: #007bff;
          color: white;
          border: none;
          border-radius: 4px;
          cursor: pointer;
          font-size: 14px;
        }

        .mlbtn:hover {
          background-color: #0056b3;
        }

        .mlbtn.action {
          background-color: #28a745;
          padding: 5px 10px;
          font-size: 12px;
        }

        .mlbtn.action:hover {
          background-color: #1e7e34;
        }

        .mlbtn:disabled {
          background-color: #6c757d;
          cursor: not-allowed;
          opacity: 0.5;
        }

        .magnalisterAttributeAjaxForm {
          margin-top: 10px;
        }

        .attribute-matching-table {
          border-collapse: collapse;
        }

        .attribute-matching-table th,
        .attribute-matching-table td {
          padding: 8px;
          border: 1px solid #dadada;
        }

        .noticeBox {
          padding: 15px;
          margin: 15px 0;
          border-radius: 4px;
        }

        .ml-error-box {
          background-color: #f8d7da;
          border: 1px solid #f5c6cb;
          color: #721c24;
        }

        .ml-error-box ul {
          margin: 10px 0 0 20px;
        }

        .ml-form-subfields-main-container {
          display: flex;
          flex-direction: column;
          gap: 10px;
        }

        .ml-subfield-field-container {
          display: flex;
          align-items: center;
          gap: 10px;
        }

        .ml-field-flex-align-center {
          display: flex;
          align-items: center;
          gap: 8px;
          margin-bottom: 10px;
        }
      `}</style>
    </div>
  );
};

export default AmazonVariationsExample;