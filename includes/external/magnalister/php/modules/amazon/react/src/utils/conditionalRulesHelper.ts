import {ConditionalRule, MarketplaceAttribute} from '../types';
import {CONDITIONAL_RULES_BOX_STYLES} from '../components/AmazonVariations/styles/infoBoxStyles';
import InfoIcon from '../assets/info_tooltip.png';

/**
 * Generate a help text explaining which attributes affect or are affected by this attribute
 * based on conditional rules.
 *
 * @param attributeKey - Current attribute key
 * @param conditionalRules - Array of conditional rules
 * @param allAttributes - All marketplace attributes (to get display names)
 * @param i18n - Internationalization strings
 * @returns HTML string with clickable links to related attributes, or empty string if no rules
 */
export function generateConditionalRulesHelpText(
  attributeKey: string,
  conditionalRules: ConditionalRule[],
  allAttributes: Record<string, MarketplaceAttribute>,
  i18n: any
): string {
  if (!conditionalRules || conditionalRules.length === 0) {
    return '';
  }

  // Find rules where this attribute is the TARGET (affected by other attributes)
  const rulesAsTarget = conditionalRules.filter(rule => rule.targetField === attributeKey);

  // Find rules where this attribute is a SOURCE (affects other attributes)
  const rulesAsSource = conditionalRules.filter(rule => {
    if (!rule.sourceFields) return false;
    // Handle both array and string formats
    if (Array.isArray(rule.sourceFields)) {
      return rule.sourceFields.includes(attributeKey);
    }
    // If it's a string, check if it matches
    return rule.sourceFields === attributeKey;
  });

  if (rulesAsTarget.length === 0 && rulesAsSource.length === 0) {
    return ''; // This attribute is not involved in any conditional rules
  }

  const helpParts: string[] = [];

  // Part 1: Attributes that AFFECT this attribute (this is TARGET)
  if (rulesAsTarget.length > 0) {
    const sourceAttributeKeys = new Set<string>();
    rulesAsTarget.forEach(rule => {
      if (rule.sourceFields) {
        // Handle both array and string formats
        if (Array.isArray(rule.sourceFields)) {
          rule.sourceFields.forEach(sourceKey => sourceAttributeKeys.add(sourceKey));
        } else {
          sourceAttributeKeys.add(rule.sourceFields);
        }
      }
    });

    const sourceAttributeNames = Array.from(sourceAttributeKeys)
      .map(key => {
        const attr = allAttributes[key];
        const displayName = attr?.value || key;

        // Get example values from the attribute (first 3 values as examples)
        let exampleText = '';
        if (attr?.values) {
          const valueExamples = Object.values(attr.values).slice(0, 3);
          if (valueExamples.length > 0) {
            exampleText = ` <span style="color: rgba(73, 80, 87, 0.7) !important; font-style: italic !important; font-size: 12px !important;">(e.g. "${valueExamples.join('", "')}"${valueExamples.length === 3 ? ', ...' : ''})</span>`;
          }
        }

        // Create clickable link with example
        return `<li style="margin-bottom: 6px !important;"><a href="#" class="ml-js-noBlockUi conditional-rule-link" data-target-attribute="${key}" style="color: #667eea !important; text-decoration: none !important; font-weight: 500 !important; cursor: pointer !important;">${displayName}</a>${exampleText}</li>`;
      });

    if (sourceAttributeNames.length > 0) {
      const affectedByText = i18n?.conditionalRulesAffectedBy || 'This field options are filtered based on';
      helpParts.push(`<div><strong>${affectedByText}:</strong><ul style="margin: 4px 0 0 0 !important; padding-left: 20px !important;">${sourceAttributeNames.join('')}</ul></div>`);
    }
  }

  // Part 2: Attributes that ARE AFFECTED by this attribute (this is SOURCE)
  if (rulesAsSource.length > 0) {
    const targetAttributeKeys = new Set<string>();
    rulesAsSource.forEach(rule => {
      targetAttributeKeys.add(rule.targetField);
    });

    const targetAttributeNames = Array.from(targetAttributeKeys)
      .map(key => {
        const attr = allAttributes[key];
        const displayName = attr?.value || key;

        // Get example values from the attribute (first 3 values as examples)
        let exampleText = '';
        if (attr?.values) {
          const valueExamples = Object.values(attr.values).slice(0, 3);
          if (valueExamples.length > 0) {
            exampleText = ` <span style="color: rgba(73, 80, 87, 0.7) !important; font-style: italic !important; font-size: 12px !important;">(e.g. "${valueExamples.join('", "')}"${valueExamples.length === 3 ? ', ...' : ''})</span>`;
          }
        }

        // Create clickable link with example
        return `<li style="margin-bottom: 6px !important;"><a href="#" class="ml-js-noBlockUi conditional-rule-link" data-target-attribute="${key}" style="color: #667eea !important; text-decoration: none !important; font-weight: 500 !important; cursor: pointer !important;">${displayName}</a>${exampleText}</li>`;
      });

    if (targetAttributeNames.length > 0) {
      const affectsText = i18n?.conditionalRulesAffects || 'Changing this field will filter options in';
      helpParts.push(`<div><strong>${affectsText}:</strong><ul style="margin: 4px 0 0 0 !important; padding-left: 20px !important;">${targetAttributeNames.join('')}</ul></div>`);
    }
  }

  if (helpParts.length === 0) {
    return '';
  }

  // Combine all parts with modern card design
  // Add !important to override PrestaShop's aggressive CSS
  const containerStyles = Object.entries(CONDITIONAL_RULES_BOX_STYLES.container)
    .map(([key, value]) => `${key.replace(/([A-Z])/g, '-$1').toLowerCase()}: ${value} !important`)
    .join('; ');

  const innerContainerStyles = Object.entries(CONDITIONAL_RULES_BOX_STYLES.innerContainer)
    .map(([key, value]) => `${key.replace(/([A-Z])/g, '-$1').toLowerCase()}: ${value} !important`)
    .join('; ');

  const iconStyles = Object.entries(CONDITIONAL_RULES_BOX_STYLES.icon)
    .map(([key, value]) => `${key.replace(/([A-Z])/g, '-$1').toLowerCase()}: ${value} !important`)
    .join('; ');

  const contentStyles = Object.entries(CONDITIONAL_RULES_BOX_STYLES.content)
    .map(([key, value]) => `${key.replace(/([A-Z])/g, '-$1').toLowerCase()}: ${value} !important`)
    .join('; ');

  return `
    <div class="conditional-rules-help" style="${containerStyles}">
      <div style="${innerContainerStyles}">
        <img src="${InfoIcon}" alt="Info" style="${iconStyles}" />
        <div style="${contentStyles}">
          ${helpParts.join('<div style="margin-top: 10px !important;"></div>')}
        </div>
      </div>
    </div>
  `;
}

/**
 * Initialize click handlers for conditional rule links.
 * This function should be called after the component mounts or updates.
 */
export function initializeConditionalRuleLinks() {
  // Remove existing listeners first to prevent duplicates
  const existingLinks = document.querySelectorAll('.conditional-rule-link');
  existingLinks.forEach(link => {
    const newLink = link.cloneNode(true);
    link.parentNode?.replaceChild(newLink, link);
  });

  // Add new listeners
  const links = document.querySelectorAll('.conditional-rule-link');
  console.log(`[ConditionalRules] Found ${links.length} conditional rule links to initialize`);

  links.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation(); // Prevent event bubbling

      const targetAttribute = (e.currentTarget as HTMLElement).getAttribute('data-target-attribute');
      console.log(`[ConditionalRules] Link clicked for attribute: ${targetAttribute}`);

      if (targetAttribute) {
        /**
         * Helper function to scroll to and highlight the target row
         */
        const scrollToAndHighlight = () => {
          // Find the target attribute row by data-attribute-key
          const targetRow = document.querySelector(`[data-attribute-key="${targetAttribute}"]`);
          console.log(`[ConditionalRules] Target row found:`, targetRow);

          if (targetRow) {
            // Scroll to the target row with smooth animation
            targetRow.scrollIntoView({
              behavior: 'smooth',
              block: 'center'
            });

            // Add temporary highlight effect (yellow flash 3 times)
            const firstInput = targetRow.querySelector('input, select, textarea') as HTMLElement;
            if (firstInput) {
              // Flash yellow border 3 times
              let flashCount = 0;
              const flashInterval = setInterval(() => {
                if (flashCount >= 6) {
                  clearInterval(flashInterval);
                  firstInput.style.removeProperty('border-color');
                  firstInput.style.removeProperty('border-width');
                  return;
                }

                // Toggle between yellow and default
                if (flashCount % 2 === 0) {
                  firstInput.style.setProperty('border-color', '#ffc107', 'important');
                  firstInput.style.setProperty('border-width', '3px', 'important');
                } else {
                  firstInput.style.removeProperty('border-color');
                  firstInput.style.removeProperty('border-width');
                }

                flashCount++;
              }, 400);
            }
          } else {
            console.warn(`[ConditionalRules] Target row still not found after adding attribute: ${targetAttribute}`);
          }
        };

        // Check if target row exists
        const targetRow = document.querySelector(`[data-attribute-key="${targetAttribute}"]`);

        if (targetRow) {
          // Row exists - just scroll to it
          scrollToAndHighlight();
        } else {
          // Row doesn't exist - it might be an optional attribute that hasn't been added yet
          console.log(`[ConditionalRules] Target row not found, checking if it's an optional attribute: ${targetAttribute}`);

          // Check if global function exists to add optional attributes
          if ((window as any).magnalisterAddOptionalAttribute) {
            console.log(`[ConditionalRules] Adding optional attribute first: ${targetAttribute}`);

            // Add the optional attribute, then scroll to it when DOM updates
            (window as any).magnalisterAddOptionalAttribute(targetAttribute, () => {
              // Wait a bit more for DOM to fully update (React render + browser paint)
              setTimeout(() => {
                scrollToAndHighlight();
              }, 200);
            });
          } else {
            // Global function not available (shouldn't happen, but handle gracefully)
            console.warn(`[ConditionalRules] Cannot add attribute - magnalisterAddOptionalAttribute not available`);
            scrollToAndHighlight(); // Try to scroll anyway (will show warning)
          }
        }
      }
    });
  });
}
