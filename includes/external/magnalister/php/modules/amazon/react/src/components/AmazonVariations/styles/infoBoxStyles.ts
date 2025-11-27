/**
 * Modern and elegant info box styles for Amazon Variations component
 *
 * Usage:
 * import { INFO_BOX_STYLES, DESCRIPTION_BOX_STYLES } from './styles/infoBoxStyles';
 */

/**
 * Info box style for important messages (e.g., "Use shop values" message)
 *
 * Features:
 * - Soft gradient background (blue to light blue)
 * - Subtle shadow for depth
 * - Rounded corners
 * - Clean typography
 */
export const INFO_BOX_STYLES = {
  container: {
    padding: '14px 16px',
    background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    borderRadius: '8px',
    marginTop: '12px',
    fontSize: '14px',
    boxShadow: '0 2px 8px rgba(102, 126, 234, 0.15)',
    color: '#ffffff',
    lineHeight: '1.6'
  },
  header: {
    display: 'flex',
    alignItems: 'center',
    gap: '8px',
    fontWeight: '600',
    marginBottom: '6px',
    fontSize: '14px'
  },
  icon: {
    width: '18px',
    height: '18px',
    filter: 'brightness(0) invert(1)', // Make icon white
    flexShrink: 0
  },
  text: {
    margin: '0',
    fontSize: '13px',
    opacity: 0.95,
    lineHeight: '1.5'
  }
};

/**
 * Description box style for attribute descriptions
 *
 * Features:
 * - Clean left border accent
 * - Light gray background
 * - Subtle padding
 * - Professional typography
 */
export const DESCRIPTION_BOX_STYLES = {
  container: {
    padding: '12px 14px',
    backgroundColor: '#f8f9fa',
    borderLeft: '4px solid #667eea',
    borderRadius: '4px',
    marginTop: '8px',
    fontSize: '13px',
    color: '#495057',
    lineHeight: '1.6'
  },
  text: {
    margin: '0',
    color: '#495057'
  }
};

/**
 * Conditional rules help box style
 *
 * Features:
 * - Modern card design
 * - Soft shadow
 * - Icon with gradient accent
 * - Clean link styling
 */
export const CONDITIONAL_RULES_BOX_STYLES = {
  container: {
    marginTop: '10px',
    padding: '14px 16px',
    backgroundColor: '#ffffff',
    border: '1px solid #e9ecef',
    borderRadius: '8px',
    boxShadow: '0 2px 6px rgba(0, 0, 0, 0.06)',
    fontSize: '13px',
    lineHeight: '1.6'
  },
  innerContainer: {
    display: 'flex',
    alignItems: 'flex-start',
    gap: '10px'
  },
  icon: {
    width: '18px',
    height: '18px',
    flexShrink: 0,
    marginTop: '2px',
    opacity: 0.8
  },
  content: {
    flex: 1,
    color: '#495057'
  },
  divider: {
    marginTop: '10px'
  },
  link: {
    color: '#667eea',
    textDecoration: 'none',
    fontWeight: '500',
    transition: 'color 0.2s ease',
    cursor: 'pointer'
  },
  linkHover: {
    color: '#764ba2',
    textDecoration: 'underline'
  },
  example: {
    color: 'rgba(73, 80, 87, 0.7)',
    fontStyle: 'italic',
    fontSize: '12px'
  }
};
