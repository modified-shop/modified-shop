import React from 'react';
import AmazonVariationsNative from './components/AmazonVariationsNative';
import {createShopAttributeValuesFetcher} from './utils/shopAttributeApi';
import {AmazonVariationsProps} from './types';

/**
 * Extended props that include API endpoint and context needed for lazy loading
 */
interface AmazonVariationsWithLazyLoadingProps extends Omit<AmazonVariationsProps, 'onFetchShopAttributeValues'> {
  /** API endpoint URL from your backend system */
  apiEndpoint: string;
}

/**
 * Amazon Variations component with built-in lazy loading support
 *
 * This wrapper component automatically creates the shop attribute values fetcher
 * and passes it to the AmazonVariationsNative component.
 *
 * Usage in your variations.php:
 *
 * ```php
 * <script>
 * window.renderAmazonVariations({
 *   apiEndpoint: '<?php echo $apiEndpoint; ?>',
 *   variationGroup: '<?php echo $variationGroup; ?>',
 *   shopAttributes: <?php echo json_encode($shopAttributes); ?>,
 *   marketplaceAttributes: <?php echo json_encode($marketplaceAttributes); ?>,
 *   savedValues: <?php echo json_encode($savedValues); ?>,
 *   i18n: <?php echo json_encode($i18nStrings); ?>
 * });
 * </script>
 * ```
 */
const AmazonVariationsWithLazyLoading: React.FC<AmazonVariationsWithLazyLoadingProps> = ({
  apiEndpoint,
  variationGroup,
  ...props
}) => {
  // Create the shop attribute values fetcher function
  const fetchShopAttributeValues = React.useMemo(() => {
    return createShopAttributeValuesFetcher(apiEndpoint, variationGroup);
  }, [apiEndpoint, variationGroup]);

  return (
    <AmazonVariationsNative
      {...props}
      variationGroup={variationGroup}
      onFetchShopAttributeValues={fetchShopAttributeValues}
    />
  );
};

export default AmazonVariationsWithLazyLoading;

/**
 * Global function that can be called from PHP to render the component
 * This allows easy integration with your existing variations.php file
 */
declare global {
  interface Window {
    renderAmazonVariations: (props: AmazonVariationsWithLazyLoadingProps) => void;
  }
}

// Export the render function to window object for PHP integration
if (typeof window !== 'undefined') {
  window.renderAmazonVariations = (props: AmazonVariationsWithLazyLoadingProps) => {
    const container = document.getElementById('amazon-variations-container');
    if (container) {
      // You would use ReactDOM.render here in a real implementation
      console.log('Rendering Amazon Variations with props:', props);

      // For now, just log the configuration - replace with actual ReactDOM.render
      console.log('API Endpoint:', props.apiEndpoint);
      console.log('Variation Group:', props.variationGroup);
      console.log('Shop Attributes:', props.shopAttributes);
      console.log('Marketplace Attributes:', props.marketplaceAttributes);

      // TODO: Replace this with actual React rendering:
      // ReactDOM.render(<AmazonVariationsWithLazyLoading {...props} />, container);
    } else {
      console.error('Amazon variations container not found');
    }
  };
}