import {useCallback, useRef, useState} from 'react';
import {ApiResponse, MarketplaceAttributes, SavedValues, ShopAttributes} from '../types';

interface ApiConfig {
  baseUrl?: string;
  headers?: Record<string, string>;
  timeout?: number;
}

interface UseApiIntegrationProps {
  config?: ApiConfig;
  onError?: (error: Error) => void;
  onSuccess?: (data: any) => void;
}

interface UseApiIntegrationReturn {
  loading: boolean;
  error: string | null;
  submitAttributes: (values: SavedValues, variationGroup: string) => Promise<ApiResponse>;
  fetchShopAttributes: () => Promise<ShopAttributes>;
  fetchMarketplaceAttributes: (variationGroup: string) => Promise<MarketplaceAttributes>;
  fetchSavedValues: (variationGroup: string, customIdentifier: string) => Promise<SavedValues>;
  clearError: () => void;
}

/**
 * Custom hook for handling API integration with the backend
 */
export const useApiIntegration = ({
  config = {},
  onError,
  onSuccess
}: UseApiIntegrationProps = {}): UseApiIntegrationReturn => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const abortControllerRef = useRef<AbortController | null>(null);

  const {
    baseUrl = '/api',
    headers = { 'Content-Type': 'application/json' },
    timeout = 30000
  } = config;

  // Generic API call wrapper
  const apiCall = useCallback(async <T = any>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<T> => {
    // Cancel any ongoing request
    if (abortControllerRef.current) {
      abortControllerRef.current.abort();
    }

    // Create new abort controller
    abortControllerRef.current = new AbortController();

    setLoading(true);
    setError(null);

    try {
      const controller = abortControllerRef.current;
      const timeoutId = setTimeout(() => controller.abort(), timeout);

      const response = await fetch(`${baseUrl}${endpoint}`, {
        ...options,
        headers: {
          ...headers,
          ...options.headers
        },
        signal: controller.signal
      });

      clearTimeout(timeoutId);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data: T = await response.json();
      onSuccess?.(data);
      return data;

    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Unknown error occurred';
      setError(errorMessage);
      onError?.(err instanceof Error ? err : new Error(errorMessage));
      throw err;
    } finally {
      setLoading(false);
      abortControllerRef.current = null;
    }
  }, [baseUrl, headers, timeout, onError, onSuccess]);

  // Submit attribute values to backend
  const submitAttributes = useCallback(async (
    values: SavedValues,
    variationGroup: string
  ): Promise<ApiResponse> => {
    const payload = {
      variationGroup,
      attributes: values,
      timestamp: new Date().toISOString()
    };

    return apiCall<ApiResponse>('/amazon/variations/save', {
      method: 'POST',
      body: JSON.stringify(payload)
    });
  }, [apiCall]);

  // Fetch shop attributes from backend
  const fetchShopAttributes = useCallback(async (): Promise<ShopAttributes> => {
    const response = await apiCall<{ data: ShopAttributes }>('/shop/attributes');
    return response.data;
  }, [apiCall]);

  // Fetch marketplace attributes for a specific variation group
  const fetchMarketplaceAttributes = useCallback(async (
    variationGroup: string
  ): Promise<MarketplaceAttributes> => {
    const response = await apiCall<{ data: MarketplaceAttributes }>(
      `/amazon/variations/${encodeURIComponent(variationGroup)}/attributes`
    );
    return response.data;
  }, [apiCall]);

  // Fetch saved values for a specific variation group and product
  const fetchSavedValues = useCallback(async (
    variationGroup: string,
    customIdentifier: string
  ): Promise<SavedValues> => {
    const response = await apiCall<{ data: SavedValues }>(
      `/amazon/variations/${encodeURIComponent(variationGroup)}/values?customIdentifier=${encodeURIComponent(customIdentifier)}`
    );
    return response.data || {};
  }, [apiCall]);

  // Clear error state
  const clearError = useCallback(() => {
    setError(null);
  }, []);

  return {
    loading,
    error,
    submitAttributes,
    fetchShopAttributes,
    fetchMarketplaceAttributes,
    fetchSavedValues,
    clearError
  };
};

// Export a simple error handler function instead of HOC to avoid JSX in .ts file
export const createApiErrorHandler = (onError?: (error: Error) => void) => {
  return (error: Error) => {
    console.error('API Error:', error);
    onError?.(error);
  };
};