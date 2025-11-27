/**
 * Basic HTML Sanitizer for attribute descriptions
 *
 * This sanitizer allows only safe HTML tags commonly used in attribute descriptions
 * and removes potentially dangerous content like scripts, event handlers, etc.
 */

// Allowed HTML tags for attribute descriptions
const ALLOWED_TAGS = [
  'b', 'strong', 'i', 'em', 'u', 'br', 'p', 'div', 'span',
  'ul', 'ol', 'li', 'a', 'small', 'sup', 'sub', 'mark'
];

// Allowed attributes for specific tags
const ALLOWED_ATTRIBUTES: Record<string, string[]> = {
  'a': ['href', 'title', 'target'],
  'span': ['class'],
  'div': ['class'],
  'p': ['class']
};

/**
 * Simple HTML sanitizer that removes dangerous content
 * while preserving safe formatting tags
 */
export function sanitizeHtml(html: string): string {
  if (!html || typeof html !== 'string') {
    return '';
  }

  // Create a temporary DOM element to parse HTML
  const tempDiv = document.createElement('div');
  tempDiv.innerHTML = html;

  // Recursive function to clean elements
  function cleanElement(element: Element): void {
    const tagName = element.tagName.toLowerCase();

    // Remove disallowed tags
    if (!ALLOWED_TAGS.includes(tagName)) {
      // Replace with text content to preserve the text
      const textNode = document.createTextNode(element.textContent || '');
      element.parentNode?.replaceChild(textNode, element);
      return;
    }

    // Clean attributes
    const allowedAttrs = ALLOWED_ATTRIBUTES[tagName] || [];
    const attributesToRemove: string[] = [];

    for (let i = 0; i < element.attributes.length; i++) {
      const attr = element.attributes[i];
      const attrName = attr.name.toLowerCase();

      // Remove event handlers and dangerous attributes
      if (attrName.startsWith('on') ||
          attrName === 'javascript:' ||
          attrName === 'style' ||
          !allowedAttrs.includes(attrName)) {
        attributesToRemove.push(attr.name);
      }
    }

    // Remove dangerous attributes
    attributesToRemove.forEach(attrName => {
      element.removeAttribute(attrName);
    });

    // For links, ensure they're safe
    if (tagName === 'a') {
      const href = element.getAttribute('href');
      if (href && (href.startsWith('javascript:') || href.startsWith('data:'))) {
        element.removeAttribute('href');
      }
    }

    // Recursively clean child elements
    const children = Array.from(element.children);
    children.forEach(child => cleanElement(child));
  }

  // Clean all elements
  const elements = Array.from(tempDiv.children);
  elements.forEach(element => cleanElement(element));

  return tempDiv.innerHTML;
}

/**
 * Safe HTML renderer that sanitizes content before rendering
 * Use this for dangerouslySetInnerHTML to ensure content is safe
 */
export function createSafeHtml(html: string): { __html: string } {
  return { __html: sanitizeHtml(html) };
}