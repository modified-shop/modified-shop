import {act, renderHook} from '@testing-library/react';
import {useAttributeForm} from '../hooks/useAttributeForm';
import {useApiIntegration} from '../hooks/useApiIntegration';
import {MarketplaceAttributes, SavedAttributeValue, SavedValues} from '../types';

// Mock fetch
const mockFetch = global.fetch as jest.MockedFunction<typeof fetch>;

describe('useAttributeForm', () => {
  const mockMarketplaceAttributes: MarketplaceAttributes = {
    'color': {
      value: 'Color',
      required: true,
      dataType: 'select',
      values: { 'red': 'Red', 'blue': 'Blue' }
    },
    'size': {
      value: 'Size',
      required: false,
      dataType: 'text'
    }
  };

  const mockInitialValues: SavedValues = {
    'color': {
      Code: 'shop_color',
      Values: [{ Shop: { Key: 'red' }, Marketplace: { Key: 'Red' } }]
    }
  };

  it('initializes with provided values', () => {
    const { result } = renderHook(() =>
      useAttributeForm({
        initialValues: mockInitialValues,
        marketplaceAttributes: mockMarketplaceAttributes
      })
    );

    expect(result.current.values).toEqual(mockInitialValues);
    expect(result.current.isDirty).toBe(false);
    expect(result.current.isSubmitting).toBe(false);
  });

  it('validates required fields', () => {
    const { result } = renderHook(() =>
      useAttributeForm({
        initialValues: {},
        marketplaceAttributes: mockMarketplaceAttributes
      })
    );

    act(() => {
      const errors = result.current.validate();
      expect(errors).toBe(false);
    });

    expect(result.current.errors).toHaveLength(1);
    expect(result.current.errors[0].key).toBe('color');
  });

  it('handles attribute changes', () => {
    const { result } = renderHook(() =>
      useAttributeForm({
        initialValues: {},
        marketplaceAttributes: mockMarketplaceAttributes
      })
    );

    const newValue: SavedAttributeValue = {
      Code: 'freetext',
      Values: { FreeText: 'Custom color' }
    };

    act(() => {
      result.current.handleAttributeChange('color', newValue);
    });

    expect(result.current.values.color).toEqual(newValue);
    expect(result.current.isDirty).toBe(true);
  });

  it('validates on change when enabled', () => {
    const { result } = renderHook(() =>
      useAttributeForm({
        initialValues: {},
        marketplaceAttributes: mockMarketplaceAttributes,
        validateOnChange: true
      })
    );

    act(() => {
      result.current.handleAttributeChange('color', { Code: '' });
    });

    expect(result.current.errors).toHaveLength(1);
  });

  it('resets form to initial values', () => {
    const { result } = renderHook(() =>
      useAttributeForm({
        initialValues: mockInitialValues,
        marketplaceAttributes: mockMarketplaceAttributes
      })
    );

    // Make changes
    act(() => {
      result.current.handleAttributeChange('color', { Code: 'different' });
    });

    expect(result.current.isDirty).toBe(true);

    // Reset
    act(() => {
      result.current.reset();
    });

    expect(result.current.values).toEqual(mockInitialValues);
    expect(result.current.isDirty).toBe(false);
    expect(result.current.errors).toHaveLength(0);
  });

  it('handles form submission', async () => {
    const mockOnSubmit = jest.fn().mockResolvedValue(undefined);

    const { result } = renderHook(() =>
      useAttributeForm({
        initialValues: mockInitialValues,
        marketplaceAttributes: mockMarketplaceAttributes
      })
    );

    const mockEvent = {
      preventDefault: jest.fn()
    } as any;

    const submitHandler = result.current.handleSubmit(mockOnSubmit);

    await act(async () => {
      await submitHandler(mockEvent);
    });

    expect(mockEvent.preventDefault).toHaveBeenCalled();
    expect(mockOnSubmit).toHaveBeenCalledWith(mockInitialValues);
  });

  it('prevents submission with validation errors', async () => {
    const mockOnSubmit = jest.fn();

    const { result } = renderHook(() =>
      useAttributeForm({
        initialValues: {},
        marketplaceAttributes: mockMarketplaceAttributes
      })
    );

    const mockEvent = {
      preventDefault: jest.fn()
    } as any;

    const submitHandler = result.current.handleSubmit(mockOnSubmit);

    await act(async () => {
      await submitHandler(mockEvent);
    });

    expect(mockOnSubmit).not.toHaveBeenCalled();
    expect(result.current.errors).toHaveLength(1);
  });
});

describe('useApiIntegration', () => {
  beforeEach(() => {
    mockFetch.mockClear();
  });

  it('initializes with default state', () => {
    const { result } = renderHook(() => useApiIntegration());

    expect(result.current.loading).toBe(false);
    expect(result.current.error).toBe(null);
  });

  it('handles successful API calls', async () => {
    const mockData = { success: true, data: { id: 1 } };
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: jest.fn().mockResolvedValue(mockData)
    } as any);

    const { result } = renderHook(() => useApiIntegration());

    await act(async () => {
      const response = await result.current.submitAttributes({}, '123');
      expect(response).toEqual(mockData);
    });

    expect(result.current.loading).toBe(false);
    expect(result.current.error).toBe(null);
  });

  it('handles API errors', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: false,
      status: 500
    } as any);

    const { result } = renderHook(() => useApiIntegration());

    await act(async () => {
      try {
        await result.current.submitAttributes({}, '123');
      } catch (error) {
        expect(error).toBeInstanceOf(Error);
      }
    });

    expect(result.current.error).toBeTruthy();
  });

  it('sets loading state during API calls', async () => {
    const mockData = { success: true };
    mockFetch.mockImplementation(() =>
      new Promise(resolve =>
        setTimeout(() =>
          resolve({
            ok: true,
            json: jest.fn().mockResolvedValue(mockData)
          } as any), 100)
      )
    );

    const { result } = renderHook(() => useApiIntegration());

    // Start API call
    act(() => {
      result.current.submitAttributes({}, '123');
    });

    expect(result.current.loading).toBe(true);

    // Wait for completion
    await act(async () => {
      await new Promise(resolve => setTimeout(resolve, 150));
    });

    expect(result.current.loading).toBe(false);
  });

  it('fetches shop attributes', async () => {
    const mockData = { data: { group1: { attr1: { name: 'Attribute 1', type: 'text' } } } };
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: jest.fn().mockResolvedValue(mockData)
    } as any);

    const { result } = renderHook(() => useApiIntegration());

    let response;
    await act(async () => {
      response = await result.current.fetchShopAttributes();
    });

    expect(response).toEqual(mockData.data);
    expect(mockFetch).toHaveBeenCalledWith('/api/shop/attributes', expect.any(Object));
  });

  it('fetches marketplace attributes', async () => {
    const mockData = { data: { color: { value: 'Color', required: true, dataType: 'select' } } };
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: jest.fn().mockResolvedValue(mockData)
    } as any);

    const { result } = renderHook(() => useApiIntegration());

    let response;
    await act(async () => {
      response = await result.current.fetchMarketplaceAttributes('123');
    });

    expect(response).toEqual(mockData.data);
    expect(mockFetch).toHaveBeenCalledWith('/api/amazon/variations/123/attributes', expect.any(Object));
  });

  it('fetches saved values', async () => {
    const mockData = { data: { color: { Code: 'red' } } };
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: jest.fn().mockResolvedValue(mockData)
    } as any);

    const { result } = renderHook(() => useApiIntegration());

    let response;
    await act(async () => {
      response = await result.current.fetchSavedValues('123', 'test');
    });

    expect(response).toEqual(mockData.data);
    expect(mockFetch).toHaveBeenCalledWith(
      '/api/amazon/variations/123/values?customIdentifier=test',
      expect.any(Object)
    );
  });

  it('clears errors', () => {
    const { result } = renderHook(() => useApiIntegration());

    // Simulate an error
    act(() => {
      result.current.clearError();
    });

    expect(result.current.error).toBe(null);
  });

  it('handles network timeouts', async () => {
    const abortError = new DOMException('The user aborted a request.', 'AbortError');
    mockFetch.mockRejectedValueOnce(abortError);

    const { result } = renderHook(() =>
      useApiIntegration({
        config: { timeout: 100 }
      })
    );

    await act(async () => {
      try {
        await result.current.submitAttributes({}, '123');
      } catch (error) {
        expect(error).toBe(abortError);
      }
    });
  });

  it('calls success and error callbacks', async () => {
    const onSuccess = jest.fn();
    const onError = jest.fn();

    const mockData = { success: true };
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: jest.fn().mockResolvedValue(mockData)
    } as any);

    const { result } = renderHook(() =>
      useApiIntegration({
        onSuccess,
        onError
      })
    );

    await act(async () => {
      await result.current.submitAttributes({}, '123');
    });

    expect(onSuccess).toHaveBeenCalledWith(mockData);
    expect(onError).not.toHaveBeenCalled();
  });
});