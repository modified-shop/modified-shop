/**
 * Style Utilities for overriding PrestaShop CSS with !important
 *
 * PrestaShop uses aggressive CSS with !important flags that can override React inline styles.
 * This utility converts React style objects to CSS strings with !important flags.
 */

/**
 * Convert a React style object to a CSS string with !important on all properties
 *
 * @param styleObj - React style object (camelCase properties)
 * @returns CSS string with !important flags (e.g., "color: red !important; font-size: 14px !important;")
 *
 * @example
 * const styles = { color: 'red', fontSize: '14px', backgroundColor: '#fff' };
 * const cssString = styleObjectToCssString(styles);
 * // Returns: "color: red !important; font-size: 14px !important; background-color: #fff !important;"
 *
 * // Use with dangerouslySetInnerHTML:
 * <div dangerouslySetInnerHTML={{ __html: `<div style="${cssString}">Content</div>` }} />
 */
export function styleObjectToCssString(styleObj: Record<string, any>): string {
  return Object.entries(styleObj)
    .map(([key, value]) => {
      // Convert camelCase to kebab-case
      const cssKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
      return `${cssKey}: ${value} !important`;
    })
    .join('; ');
}

/**
 * Apply style object to an element with !important flags
 *
 * @param element - HTML element to style
 * @param styleObj - React style object (camelCase properties)
 *
 * @example
 * const element = document.querySelector('.my-element');
 * applyStyleWithImportant(element, { color: 'red', fontSize: '14px' });
 */
export function applyStyleWithImportant(
  element: HTMLElement | null,
  styleObj: Record<string, any>
): void {
  if (!element) return;

  Object.entries(styleObj).forEach(([key, value]) => {
    const cssKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
    element.style.setProperty(cssKey, value as string, 'important');
  });
}
