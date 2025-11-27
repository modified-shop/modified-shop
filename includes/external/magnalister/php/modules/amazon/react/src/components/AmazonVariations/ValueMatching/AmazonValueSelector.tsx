import React from 'react';
import Select from 'react-select';
import {I18nStrings, MarketplaceAttribute} from '@/types';
import {SELECT_CONFIGS} from '../config/selectConfig';

interface AmazonValueSelectorProps {
  attribute: MarketplaceAttribute;
  value: string;
  onChange: (value: string) => void;
  disabled?: boolean;
  debugMode?: boolean;
  i18n: I18nStrings;
  className?: string;
  shouldHighlight?: boolean; // Trigger highlight animation when conditional rules are applied
}

/**
 * AmazonValueSelector Component
 *
 * Renders either a searchable dropdown (if Amazon has many predefined values),
 * native dropdown (for few values), or a text input (if Amazon accepts freetext)
 * Used when 'attribute_value' option is selected
 *
 * Features:
 * - Highlights with yellow border flash when conditional rules filter options
 * - Automatically scrolls into view when highlighted
 * - Supports both native select and react-select components
 */
const AmazonValueSelector = React.forwardRef<HTMLDivElement, AmazonValueSelectorProps>(({
  attribute,
  value,
  onChange,
  disabled = false,
  debugMode = false,
  i18n,
  className = '',
  shouldHighlight = false
}, ref) => {
  // Internal ref for the container (use external ref if provided, otherwise create internal)
  const containerRef = React.useRef<HTMLDivElement>(null);
  const actualRef = (ref as React.RefObject<HTMLDivElement>) || containerRef;

  // State to manage highlight animation class
  const [isHighlighted, setIsHighlighted] = React.useState(false);

  /**
   * Yellow Border Flash Animation for Conditional Rules
   *
   * When shouldHighlight prop becomes true, this effect animates the border
   * to flash yellow 3 times, drawing user attention to filtered options.
   *
   * Animation Sequence (2.4 seconds total):
   * - 0ms:    Yellow (#ffc107, 3px)
   * - 400ms:  Gray (#ddd, 3px)
   * - 800ms:  Yellow
   * - 1200ms: Gray
   * - 1600ms: Yellow
   * - 2000ms: Gray
   * - 2400ms: Restore original border
   *
   * Technical Implementation:
   * - Uses JavaScript animation instead of CSS @keyframes
   * - Why? PrestaShop/Magnalister CSS with !important would override CSS animations
   * - Solution: Use style.setProperty('border-color', color, 'important') for highest priority
   * - Works with both native <select> and react-select components
   *
   * Browser Compatibility:
   * - style.setProperty with 'important' priority works in all modern browsers
   * - Falls back gracefully if element not found (no error thrown)
   * - IMPORTANT: Cleanup timers on unmount to prevent memory leaks
   */
  React.useEffect(() => {
    // Array to store all timer IDs for cleanup
    const timers: NodeJS.Timeout[] = [];

    console.log('[AmazonValueSelector] shouldHighlight changed:', shouldHighlight);

    if (shouldHighlight) {
      console.log('[AmazonValueSelector] Starting flash animation...');
      // Add highlight class for potential CSS hooks
      setIsHighlighted(true);

      // Get the actual select element (works for both native and react-select)
      const getSelectElement = () => {
        if (actualRef.current) {
          return actualRef.current.querySelector('select') ||
                 actualRef.current.querySelector('.react-select__control');
        }
        return null;
      };

      const selectElement = getSelectElement();
      console.log('[AmazonValueSelector] Select element found:', selectElement);

      if (selectElement) {
        // Store original border styles to restore after animation
        const originalBorder = (selectElement as HTMLElement).style.border;
        const originalBorderColor = (selectElement as HTMLElement).style.borderColor;
        const originalBorderWidth = (selectElement as HTMLElement).style.borderWidth;

        // Define flash sequence: alternating yellow and gray
        // Each flash lasts 400ms (200ms per color change felt too fast)
        // CRITICAL: Last flash stays yellow - no need for separate "reset" setTimeout
        // Why? Parallel setTimeout timers can conflict and override each other
        // Solution: Make final color part of the flash sequence itself
        const flashTimes = [
          { delay: 0, color: '#ffc107', width: '3px' },     // Flash 1: Yellow
          { delay: 400, color: '#ddd', width: '3px' },      // Flash 1: Gray
          { delay: 800, color: '#ffc107', width: '3px' },   // Flash 2: Yellow
          { delay: 1200, color: '#ddd', width: '3px' },     // Flash 2: Gray
          { delay: 1600, color: '#ffc107', width: '3px' },  // Flash 3: Yellow (FINAL - stays yellow)
        ];

        // Schedule each flash color change
        flashTimes.forEach(({ delay, color, width }) => {
          const timer = setTimeout(() => {
            // CRITICAL: Use setProperty with 'important' to override any CSS !important rules
            // This is the ONLY way to override PrestaShop's aggressive CSS specificity
            (selectElement as HTMLElement).style.setProperty('border-color', color, 'important');
            (selectElement as HTMLElement).style.setProperty('border-width', width, 'important');
            (selectElement as HTMLElement).style.setProperty('border-style', 'solid', 'important');
          }, delay);
          timers.push(timer); // Store timer for cleanup
        });

        // ❌ REMOVED: Separate setTimeout for final color
        //
        // Original Implementation (commented out):
        // const resetTimer = setTimeout(() => {
        //   element.style.setProperty('border-color', '#ffc107', 'important');
        //   element.style.setProperty('border-width', '2px', 'important');
        //   element.style.setProperty('border-style', 'solid', 'important');
        //   setIsHighlighted(false);
        // }, 2400);
        // timers.push(resetTimer);
        //
        // Problem Observed: Animation didn't work consistently - border stayed gray
        //
        // Possible Root Causes (unverified):
        // 1. setTimeout race condition: Timer at 1200ms (gray) might execute after 1600ms (yellow)
        // 2. React state updates: setIsHighlighted(false) at 2400ms might re-render and reset styles
        // 3. Event loop timing: Browser might batch style updates unpredictably
        // 4. CSS cascade: Platform CSS might override during state transitions
        //
        // Current Solution (working):
        // - Make final color (yellow) the last element in flashTimes array
        // - No separate timer needed
        // - Border stays yellow after animation completes
        //
        // To Verify Root Cause:
        // - Test with console.log timestamps to check execution order
        // - Monitor React DevTools for unexpected re-renders
        // - Check browser Performance tab for style recalculation timing
        //
        // Lesson Learned (tentative):
        // When animating with setTimeout, prefer including final state in sequence
        // rather than adding separate "reset" timer. This eliminates potential timing issues.
      } else {
        // Element not found - gracefully handle by resetting state
        const fallbackTimer = setTimeout(() => {
          setIsHighlighted(false);
        }, 2400);
        timers.push(fallbackTimer); // Store timer for cleanup
      }
    }

    // Cleanup function to prevent memory leaks
    return () => {
      timers.forEach(timer => clearTimeout(timer));
    };
  }, [shouldHighlight, actualRef]);
  if (attribute.values && Object.keys(attribute.values).length > 0) {
    const amazonOptions = Object.entries(attribute.values).map(([optionValue, label]) => ({
      value: optionValue,
      label: debugMode ? `${label} [${optionValue}]` : (label as string)
    }));

    const totalOptions = amazonOptions.length;
    const useSearchableSelect = totalOptions >= SELECT_CONFIGS.AMAZON_VALUE_SELECTOR.threshold;

    // Find selected option for react-select
    const selectedOption = amazonOptions.find(option => option.value === value) || null;

    // Handle change for react-select
    const handleSelectChange = (option: any) => {
      onChange(option?.value || '');
    };

    if (useSearchableSelect) {
      // Render searchable react-select for many options
      return (
        <div
          ref={actualRef}
          className={`amazon-value-selector-container ${className} ${isHighlighted ? 'highlight-conditional-filter' : ''}`}
        >
          <Select
            options={amazonOptions}
            value={selectedOption}
            onChange={handleSelectChange}
            isDisabled={disabled}
            isSearchable={true}
            isClearable={true}
            placeholder={i18n.selectAmazonValue || 'Select Amazon value...'}
            noOptionsMessage={() => 'No values found'}
            styles={SELECT_CONFIGS.AMAZON_VALUE_SELECTOR.styles}
            className="amazon-value-react-select"
            classNamePrefix="ml-amazon-value"
          />
        </div>
      );
    }

    // Render native dropdown for fewer options
    return (
      <div
        ref={actualRef}
        className={`amazon-value-selector-container ${className} ${isHighlighted ? 'highlight-conditional-filter' : ''}`}
      >
        <select
          value={value}
          onChange={(e) => onChange(e.target.value)}
          disabled={disabled}
          className="amazon-value-selector"
        >
          <option value="">
            {i18n.selectAmazonValue || 'Select Amazon value...'}
          </option>
          {amazonOptions.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
      </div>
    );
  }

  // Render text input for freetext Amazon values
  return (
    <div
      ref={actualRef}
      className={`amazon-value-input-container ${className} ${isHighlighted ? 'highlight-conditional-filter' : ''}`}
    >
      <input
        type="text"
        value={value}
        onChange={(e) => onChange(e.target.value)}
        placeholder={i18n.enterAmazonValue || 'Enter Amazon value'}
        disabled={disabled}
        className="amazon-value-input"
        onFocus={(e) => {
          e.target.style.borderColor = '#80bdff';
          e.target.style.boxShadow = '0 0 0 0.2rem rgba(0, 123, 255, 0.25)';
        }}
        onBlur={(e) => {
          e.target.style.borderColor = '#ced4da';
          e.target.style.boxShadow = 'none';
        }}
      />
    </div>
  );
});

AmazonValueSelector.displayName = 'AmazonValueSelector';

export default AmazonValueSelector;