import {generateConditionalRulesHelpText} from './conditionalRulesHelper';
import {ConditionalRule, MarketplaceAttribute} from '../types';

/**
 * Unit tests for the HTML escaping in the conditional-rules help text.
 *
 * The generated string is rendered via dangerouslySetInnerHTML, so every
 * data-derived interpolation (attribute key, display name, example values)
 * must be escaped. The attribute context data-target-attribute="..." matters
 * most: an unescaped quote there would break out of the attribute and allow
 * injecting arbitrary attributes/handlers.
 */

const rule = (sourceFields: string | string[], targetField: string): ConditionalRule => ({
  sourceFields,
  targetField,
  conditions: [],
  allowedValues: []
});

const attribute = (value: string, values?: Record<string, string>): MarketplaceAttribute => ({
  value,
  required: false,
  dataType: 'select',
  ...(values ? {values} : {})
});

describe('generateConditionalRulesHelpText — escaping of data-derived values', () => {
  test('escapes markup in the attribute display name', () => {
    const html = generateConditionalRulesHelpText(
      'color_name',
      [rule(['size_name'], 'color_name')],
      {size_name: attribute('<img src=x onerror=alert(1)>')},
      {}
    );

    expect(html).not.toContain('<img src=x');
    expect(html).toContain('&lt;img src=x onerror=alert(1)&gt;');
  });

  test('escapes quotes in the attribute key inside data-target-attribute', () => {
    // Unknown key: also exercises the displayName-falls-back-to-key path
    const maliciousKey = 'size" onmouseover="alert(1)';
    const html = generateConditionalRulesHelpText(
      'color_name',
      [rule([maliciousKey], 'color_name')],
      {},
      {}
    );

    // The raw key must never appear — an unescaped " would end the attribute
    expect(html).not.toContain(maliciousKey);
    expect(html).toContain('data-target-attribute="size&quot; onmouseover=&quot;alert(1)"');
  });

  test('escapes markup, ampersands and quotes in example values', () => {
    const html = generateConditionalRulesHelpText(
      'color_name',
      [rule(['size_name'], 'color_name')],
      {
        size_name: attribute('Size', {
          a: '<script>alert(1)</script>',
          b: 'M & L',
          c: '36" waist'
        })
      },
      {}
    );

    expect(html).not.toContain('<script');
    expect(html).toContain('&lt;script&gt;alert(1)&lt;/script&gt;');
    expect(html).toContain('M &amp; L');
    expect(html).toContain('36&quot; waist');
  });

  test('escapes in the "affects" direction too', () => {
    const html = generateConditionalRulesHelpText(
      'size_name',
      [rule(['size_name'], 'color_name')],
      {color_name: attribute("<b onclick='alert(1)'>Color</b>")},
      {}
    );

    expect(html).not.toContain('<b onclick');
    expect(html).toContain('&lt;b onclick=&#39;alert(1)&#39;&gt;Color&lt;/b&gt;');
  });

  test('renders benign names and keys unchanged inside the link', () => {
    const html = generateConditionalRulesHelpText(
      'color_name',
      [rule(['size_name'], 'color_name')],
      {size_name: attribute('Size Name')},
      {}
    );

    expect(html).toContain('data-target-attribute="size_name"');
    expect(html).toContain('>Size Name</a>');
  });
});
