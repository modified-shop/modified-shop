import React from 'react';

/**
 * HighlightController - Manages highlight animation state for attribute rows
 *
 * This class provides a clear, trackable API for managing highlight animations
 * instead of using React's anonymous setState functions.
 *
 * Why use a class instead of React hooks?
 * - IDE can track all method calls (Ctrl+Click works)
 * - Clear separation of concerns
 * - Easier to debug and understand flow
 * - No anonymous functions that are hard to trace
 *
 * @example
 * const controller = new HighlightController(attributeKey);
 * controller.enable(); // Turn on highlight
 * controller.disable(); // Turn off highlight
 * const isActive = controller.isActive(); // Check state
 */
export class HighlightController {
  private attributeKey: string;
  private active: boolean = false;
  private callbacks: Set<(active: boolean) => void> = new Set();

  /**
   * Create a new highlight controller for an attribute
   * @param attributeKey - The key of the attribute being controlled
   */
  constructor(attributeKey: string) {
    this.attributeKey = attributeKey;
  }

  /**
   * Enable the highlight animation
   * This will trigger yellow border flash in AmazonValueSelector
   * @see AmazonValueSelector.tsx for animation implementation
   */
  public enable(): void {
    console.log(`[HighlightController] ${this.attributeKey}: Enabling highlight`);
    this.active = true;
    this.notifyListeners();
  }

  /**
   * Disable the highlight animation
   * This will stop the flash and return border to normal
   * @see AmazonValueSelector.tsx for animation implementation
   */
  public disable(): void {
    console.log(`[HighlightController] ${this.attributeKey}: Disabling highlight`);
    this.active = false;
    this.notifyListeners();
  }

  /**
   * Check if highlight is currently active
   * @returns true if highlight animation is active
   */
  public isActive(): boolean {
    return this.active;
  }

  /**
   * Register a callback to be notified when highlight state changes
   * This is used by React components to sync with controller state
   * @param callback - Function to call when state changes
   */
  public subscribe(callback: (active: boolean) => void): void {
    this.callbacks.add(callback);
  }

  /**
   * Unregister a callback
   * @param callback - Function to remove from listeners
   */
  public unsubscribe(callback: (active: boolean) => void): void {
    this.callbacks.delete(callback);
  }

  /**
   * Notify all registered callbacks of state change
   * @private
   */
  private notifyListeners(): void {
    // Use try-catch to prevent errors if callback fails (e.g., during unmount)
    this.callbacks.forEach(callback => {
      try {
        callback(this.active);
      } catch (error) {
        // Silently ignore errors during callback execution (component may be unmounting)
        console.debug(`[HighlightController] ${this.attributeKey}: Callback error (component unmounting?)`, error);
      }
    });
  }

  /**
   * Clean up all listeners
   * Call this when component unmounts
   */
  public destroy(): void {
    console.log(`[HighlightController] ${this.attributeKey}: Destroying controller`);
    this.callbacks.clear();
  }
}

/**
 * Hook to use HighlightController in React components
 * This bridges the gap between class-based controller and React hooks
 *
 * @param attributeKey - The key of the attribute
 * @returns Controller instance and current active state
 *
 * @example
 * const { controller, isActive } = useHighlightController('collar_style');
 * controller.enable(); // Turn on highlight
 * controller.disable(); // Turn off highlight
 */
export function useHighlightController(attributeKey: string) {
  const [controller] = React.useState(() => new HighlightController(attributeKey));
  const [isActive, setIsActive] = React.useState(false);

  React.useEffect(() => {
    // Subscribe to controller changes
    const handleChange = (active: boolean) => {
      setIsActive(active);
    };

    controller.subscribe(handleChange);

    // Cleanup on unmount
    return () => {
      controller.unsubscribe(handleChange);
      controller.destroy();
    };
  }, [controller]);

  return { controller, isActive };
}
