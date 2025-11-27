/**
 * Shop Attribute API utilities for lazy loading
 */

// Type for the API response
interface ShopAttributeValuesResponse {
  success: boolean;
  values?: { [key: string]: string };
  shopAttributeValues?: { [key: string]: string }; // Alternative property name
  message?: string;
}

/**
 * Fetches shop attribute values from the backend API with retry logic
 * @param apiEndpoint - The API endpoint URL
 * @param attributeCode - The shop attribute code to fetch values for
 * @param variationGroup - Current variation group context
 * @param neededFormFields - Platform-specific form fields (e.g., Magento form_key)
 * @returns Promise with the attribute values
 */
export const fetchShopAttributeValues = async (
  apiEndpoint: string,
  attributeCode: string,
  variationGroup: string,
  neededFormFields?: { [key: string]: string }
): Promise<{ [key: string]: string }> => {
  // Create URL-encoded form data for proper array structure ($_POST['ml']['action'])
  const params = new URLSearchParams();
  params.append('ml[action]', 'getShopAttributeValues');
  params.append('ml[attributeCode]', attributeCode);
  params.append('ml[variationGroup]', variationGroup);

  // Add platform-specific form fields (e.g., Magento form_key)
  if (neededFormFields) {
    Object.entries(neededFormFields).forEach(([key, value]) => {
      params.append(key, value);
    });
  }

  // Retry logic: 3 attempts with 1 second delay between attempts
  const maxRetries = 3;
  const retryDelay = 1000; // 1 second

  for (let attempt = 1; attempt <= maxRetries; attempt++) {
    try {
      const response = await fetch(apiEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: params.toString()
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const data: ShopAttributeValuesResponse = await response.json();

      // Handle both 'values' and 'shopAttributeValues' property names
      const attributeValues = data.values || data.shopAttributeValues;

      if (data.success && attributeValues) {
        // Success - return immediately
        if (attempt > 1) {
          console.log(`[shopAttributeApi] ✅ Request succeeded on attempt ${attempt} for ${attributeCode}`);
        }
        return attributeValues;
      } else {
        const errorMessage = data.message || 'Failed to load shop attribute values';
        console.error(`[shopAttributeApi] API error on attempt ${attempt}:`, errorMessage, 'Full response:', data);
        throw new Error(errorMessage);
      }
    } catch (error) {
      const isLastAttempt = attempt === maxRetries;

      if (isLastAttempt) {
        // Final attempt failed - throw error
        console.error(`[shopAttributeApi] ❌ All ${maxRetries} attempts failed for ${attributeCode}:`, error);
        throw error instanceof Error ? error : new Error('Unknown error occurred');
      } else {
        // Not the last attempt - log and retry after delay
        console.warn(`[shopAttributeApi] ⚠️ Attempt ${attempt}/${maxRetries} failed for ${attributeCode}, retrying in ${retryDelay}ms...`);
        await new Promise(resolve => setTimeout(resolve, retryDelay));
      }
    }
  }

  // This should never be reached, but TypeScript requires a return
  throw new Error('Unexpected error: retry loop completed without result');
};

/**
 * Creates a shop attribute values fetcher function for a specific API endpoint and variation group
 * @param apiEndpoint - The API endpoint URL
 * @param variationGroup - Current variation group context
 * @param neededFormFields - Platform-specific form fields (e.g., Magento form_key)
 * @returns Function that can be passed to AmazonVariationsNative component
 */
export const createShopAttributeValuesFetcher = (
  apiEndpoint: string,
  variationGroup: string,
  neededFormFields?: { [key: string]: string }
) => {
  return (attributeCode: string) => fetchShopAttributeValues(apiEndpoint, attributeCode, variationGroup, neededFormFields);
};