/**
 * Utility functions for evaluating conditional rules from Amazon API
 *
 * Conditional rules dynamically restrict allowed values in target fields
 * based on the current values of source fields.
 */

import {ConditionalRule, ConditionalRuleCondition, SavedValues} from '@/types';

/**
 * Evaluate a single condition against the current form values
 *
 * Example fieldValue structures:
 * 1. Amazon attribute value (new structure):
 *    { Code: "attribute_value", Values: { AttributeValue: "polo_shirt" } }
 *
 * 2. Free text (new structure):
 *    { Code: "freetext", Values: { FreeText: "custom text" } }
 *
 * 3. Shop attribute with matching (new structure):
 *    { Code: "size", Values: [
 *        { Shop: { Key: "L", Value: "Large" }, Marketplace: { Key: "large", Value: "Large" } }
 *    ]}
 *
 * 4. Legacy structure (backward compatibility):
 *    { Code: "attribute_value", AttributeValue: "polo_shirt" }
 */
function evaluateCondition(
  condition: ConditionalRuleCondition,
  currentValues: SavedValues
): boolean {
  const fieldValue = currentValues[condition.field];

  // If field has no value, condition fails
  if (!fieldValue) {
    return false;
  }

  // Check if field has Code set (shop attribute selected in first dropdown)
  // This is important: if Code exists, the field is "active" even without Amazon values
  const hasCode = Boolean(fieldValue.Code);

  // Extract the actual value(s) from SavedAttributeValue structure
  // This will contain all possible values that should be checked against the condition
  let actualValues: string[] = [];

  // Check nested Values field (can be string, object, or array)
  if (fieldValue.Values) {
    if (typeof fieldValue.Values === 'string') {
      // Case 0: Direct string value (backend format for simple values)
      // Structure: Values: "as3" or Values: "polo_shirt"
      actualValues.push(fieldValue.Values);
    } else if (Array.isArray(fieldValue.Values)) {
      // Case 1: Array of matching values (for shop attributes with value matching)
      // Structure: [{ Shop: {...}, Marketplace: { Key: "large", Value: "Large" } }, ...]
      // We check the Marketplace.Key because that's what Amazon API expects
      fieldValue.Values.forEach((matching: any) => {
        if (matching?.Marketplace?.Key) {
          actualValues.push(matching.Marketplace.Key);
        }
      });
    } else if (typeof fieldValue.Values === 'object') {
      // Case 2: Single value object
      // valuesObj can be:
      // - { AttributeValue: "polo_shirt" } when user selects an Amazon predefined value
      // - { FreeText: "custom text" } when user enters free text
      const valuesObj = fieldValue.Values as any;
      if (valuesObj.AttributeValue) {
        actualValues.push(valuesObj.AttributeValue);
      }
      if (valuesObj.FreeText) {
        actualValues.push(valuesObj.FreeText);
      }
    }
  }

  // Check legacy top-level fields (backward compatibility)
  // Old structure: { Code: "...", AttributeValue: "value" }
  if (fieldValue.AttributeValue) {
    actualValues.push(fieldValue.AttributeValue);
  }

  if (fieldValue.FreeTextValue) {
    actualValues.push(fieldValue.FreeTextValue);
  }

  // Evaluate based on operator
  // Normalize condition.value to always be an array for consistent checking
  // Example: "polo_shirt" becomes ["polo_shirt"]
  //          ["polo_shirt", "t_shirt"] stays ["polo_shirt", "t_shirt"]
  const conditionValue = Array.isArray(condition.value) ? condition.value : [condition.value];

  switch (condition.operator) {
    case 'equals':
      // Check if any actual value equals any of the condition values
      // Example: actualValues=["polo_shirt"], conditionValue=["polo_shirt"] → true
      // Example: actualValues=["t_shirt"], conditionValue=["polo_shirt"] → false
      if (actualValues.length === 0) {
        console.log(`[ConditionalRules] equals check FAILED - no actualValues. Field:`, condition.field, 'fieldValue:', fieldValue, 'hasCode:', hasCode);
        return false; // No value - fail
      }
      return actualValues.some(val => conditionValue.includes(val));

    case 'in':
      // For 'in' operator: if field has Code set (shop attribute selected) but no Amazon value yet,
      // we consider the condition as PASSED (field is "in" the list of active/required fields)
      // This allows conditional rules to trigger even when dependent fields are not yet filled
      // Example: age_range_description__value has Code="kind" but no Values → PASS (hasCode=true)
      // Example: age_range_description__value has Values with "Kid" → check if "Kid" in condition values
      if (actualValues.length === 0 && hasCode) {
        return true; // Field has Code (shop attribute selected) but no value - pass for 'in' operator
      }
      if (actualValues.length === 0) {
        return false; // No Code and no values - fail
      }
      return actualValues.some(val => conditionValue.includes(val));

    case 'notEquals':
      // Check if NO actual value equals any condition value (inverse of equals)
      // Example: actualValues=["polo_shirt"], conditionValue=["t_shirt"] → true
      // Example: actualValues=["polo_shirt"], conditionValue=["polo_shirt"] → false
      // If no values found, condition fails (we need a value to check inequality)
      if (actualValues.length === 0) {
        return false;
      }
      return !actualValues.some(val => conditionValue.includes(val));

    case 'notIn':
      // For 'notIn' operator: if field has Code set but no value selected,
      // we pass the condition (field has shop attribute, so it's "not in" any specific Amazon value list)
      if (actualValues.length === 0 && hasCode) {
        return true; // Field has Code but no value - pass for 'notIn' operator
      }
      if (actualValues.length === 0) {
        return false; // No Code and no values - fail
      }
      // Check if NO actual value is in the condition value array
      // Example: actualValues=["polo_shirt"], conditionValue=["t_shirt", "dress_shirt"] → true
      return !actualValues.some(val => conditionValue.includes(val));

    default:
      console.warn(`[ConditionalRules] Unknown operator: ${condition.operator}`);
      return false;
  }
}

/**
 * Evaluate all rules and return filtered allowed values for a target field
 *
 * Example flow:
 * 1. User selects shirt_form_type__value = "polo_shirt"
 * 2. Rule says: if shirt_form_type = "polo_shirt" then collar_style can only be ["Polokragen"]
 * 3. This function evaluates rules for collar_style__value
 * 4. Returns ["Polokragen"] to filter the dropdown options
 *
 * Rule structure example:
 * {
 *   sourceFields: ["shirt_form_type__value"],           // Fields that trigger this rule
 *   targetField: "collar_style__value",                 // Field that gets filtered
 *   conditions: [{                                       // Conditions to check (AND logic)
 *     field: "shirt_form_type__value",
 *     operator: "in",
 *     value: ["polo_shirt"]
 *   }],
 *   allowedValues: ["Polokragen"]                       // Only these values allowed when condition matches
 * }
 *
 * @param rules - Array of conditional rules from Amazon API
 * @param currentValues - Current form values (all attributes with their selected values)
 * @param targetField - The field to get allowed values for (e.g., "collar_style__value")
 * @returns Array of allowed values if rules match, null if no restrictions apply
 */
export function evaluateConditionalRules(
  rules: ConditionalRule[],
  currentValues: SavedValues,
  targetField: string,
  debug: boolean = false
): string[] | null {
  if (debug) {
    console.log(`[conditionalRules] 🔍 Evaluating for ${targetField}`, {
      totalRules: rules?.length || 0,
      currentValuesKeys: Object.keys(currentValues || {})
    });
  }

  if (!rules || rules.length === 0) {
    if (debug) {
      console.log(`[conditionalRules] ⚪ No rules provided`);
    }
    return null; // No rules, no restrictions
  }

  // Find all rules that target this field
  const applicableRules = rules.filter(rule => rule.targetField === targetField);

  if (debug) {
    console.log(`[conditionalRules] 🎯 Found ${applicableRules.length} applicable rules for ${targetField}`);
  }

  if (applicableRules.length === 0) {
    if (debug) {
      console.log(`[conditionalRules] ⚪ No rules target ${targetField}`);
    }
    return null; // No rules for this field, no restrictions
  }

  // Evaluate each applicable rule
  // We check rules in order and return the first match (OR logic between rules)
  // Within each rule, ALL conditions must be met (AND logic)
  for (const rule of applicableRules) {
    if (debug) {
      console.log(`[conditionalRules] 📋 Checking rule #${rule.schemaRuleIndex || '?'}:`, {
        sourceFields: rule.sourceFields,
        conditions: rule.conditions,
        allowedValues: rule.allowedValues
      });
    }

    // Check if ALL conditions in the rule are met (AND logic within a rule)
    // Example: If rule has 2 conditions, BOTH must be true
    // Condition 1: shirt_form_type = "polo_shirt" AND
    // Condition 2: material = "cotton"
    const allConditionsMet = rule.conditions.every(condition => {
      const result = evaluateCondition(condition, currentValues);

      // Enhanced debug logging
      if (debug) {
        const savedValue = currentValues[condition.field];
        let extractedValue = 'NOT_FOUND';
        if (savedValue?.Values && !Array.isArray(savedValue.Values)) {
          const vals = savedValue.Values as any;
          if (vals.AttributeValue) {
            extractedValue = vals.AttributeValue;
          }
        } else if (savedValue?.AttributeValue) {
          extractedValue = savedValue.AttributeValue;
        }

        console.log(`[conditionalRules] 🔍 Condition check:`, {
          field: condition.field,
          operator: condition.operator,
          expectedValue: condition.value,
          extractedValue: extractedValue,
          fullSavedValue: savedValue,
          result: result ? '✅ PASS' : '❌ FAIL'
        });
      }
      return result;
    });

    if (debug) {
      console.log(`[conditionalRules] ${allConditionsMet ? '✅' : '❌'} All conditions met: ${allConditionsMet}`);
    }

    // If all conditions are met, return the allowed values from this rule
    if (allConditionsMet) {
      if (debug) {
        console.log(`[conditionalRules] ✅ MATCH! Returning allowed values:`, rule.allowedValues);
      }
      return rule.allowedValues || null;
    }
  }

  // No matching rules found, no restrictions apply
  if (debug) {
    console.log(`[conditionalRules] ⚪ No matching rules for ${targetField}`);
  }
  return null;
}

/**
 * Get all source fields that affect a target field
 *
 * This is useful for determining which field changes should trigger re-evaluation
 *
 * @param rules - Array of conditional rules
 * @param targetField - The target field
 * @returns Array of source field names that affect this target
 */
export function getSourceFieldsForTarget(
  rules: ConditionalRule[],
  targetField: string
): string[] {
  if (!rules || rules.length === 0) {
    return [];
  }

  const sourceFields = new Set<string>();

  rules
    .filter(rule => rule.targetField === targetField)
    .forEach(rule => {
      // Handle both array and string formats
      if (Array.isArray(rule.sourceFields)) {
        rule.sourceFields.forEach((field: string) => sourceFields.add(field));
      } else {
        sourceFields.add(rule.sourceFields);
      }
    });

  return Array.from(sourceFields);
}

/**
 * Check if a field is a target field in any conditional rule
 *
 * @param rules - Array of conditional rules
 * @param fieldName - The field to check
 * @returns true if field is a target in any rule
 */
export function isTargetField(
  rules: ConditionalRule[],
  fieldName: string
): boolean {
  if (!rules || rules.length === 0) {
    return false;
  }

  return rules.some(rule => rule.targetField === fieldName);
}

/**
 * Check if a field is a source field in any conditional rule
 *
 * @param rules - Array of conditional rules
 * @param fieldName - The field to check
 * @returns Array of target fields that depend on this source field
 */
export function getAffectedTargetFields(
  rules: ConditionalRule[],
  sourceField: string
): string[] {
  if (!rules || rules.length === 0) {
    return [];
  }

  const affectedTargets = new Set<string>();

  rules.forEach(rule => {
    // Handle both array and string formats
    const hasSourceField = Array.isArray(rule.sourceFields)
      ? rule.sourceFields.includes(sourceField)
      : rule.sourceFields === sourceField;

    if (hasSourceField) {
      affectedTargets.add(rule.targetField);
    }
  });

  return Array.from(affectedTargets);
}
