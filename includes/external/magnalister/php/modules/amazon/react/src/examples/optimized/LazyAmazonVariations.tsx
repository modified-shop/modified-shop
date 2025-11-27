import React, {lazy, Suspense, useCallback, useMemo, useState} from 'react';
import {AmazonVariationsProps} from '../../types';

// Lazy load the main component
const AmazonVariations = lazy(() => import('../../AmazonVariations'));

// Loading fallback component
const LoadingFallback: React.FC<{ message?: string }> = ({
  message = 'Loading Amazon Variations...'
}) => (
  <div className="lazy-loading-container" style={{
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    justifyContent: 'center',
    padding: '40px',
    minHeight: '200px'
  }}>
    <div style={{
      width: '40px',
      height: '40px',
      border: '4px solid #f3f3f3',
      borderTop: '4px solid #007bff',
      borderRadius: '50%',
      animation: 'spin 1s linear infinite',
      marginBottom: '16px'
    }} />
    <div style={{ fontSize: '16px', color: '#666' }}>
      {message}
    </div>
    <style jsx>{`
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
    `}</style>
  </div>
);

// Error boundary for lazy loading
class LazyErrorBoundary extends React.Component<
  { children: React.ReactNode; fallback?: React.ComponentType<{ error: Error; retry: () => void }> },
  { hasError: boolean; error: Error | null }
> {
  constructor(props: any) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error: Error) {
    return { hasError: true, error };
  }

  componentDidCatch(error: Error, errorInfo: React.ErrorInfo) {
    console.error('Lazy loading error:', error, errorInfo);
  }

  retry = () => {
    this.setState({ hasError: false, error: null });
  };

  render() {
    if (this.state.hasError) {
      const FallbackComponent = this.props.fallback;
      if (FallbackComponent) {
        return <FallbackComponent error={this.state.error!} retry={this.retry} />;
      }

      return (
        <div style={{
          padding: '20px',
          border: '1px solid #f5c6cb',
          borderRadius: '4px',
          backgroundColor: '#f8d7da',
          color: '#721c24',
          textAlign: 'center'
        }}>
          <h3>Failed to load component</h3>
          <p>{this.state.error?.message || 'Unknown error'}</p>
          <button
            onClick={this.retry}
            style={{
              padding: '8px 16px',
              backgroundColor: '#dc3545',
              color: 'white',
              border: 'none',
              borderRadius: '4px',
              cursor: 'pointer'
            }}
          >
            Retry
          </button>
        </div>
      );
    }

    return this.props.children;
  }
}

// Optimized lazy wrapper with preloading
interface LazyAmazonVariationsProps extends AmazonVariationsProps {
  preload?: boolean;
  loadingMessage?: string;
  fallbackComponent?: React.ComponentType<{ error: Error; retry: () => void }>;
}

const LazyAmazonVariations: React.FC<LazyAmazonVariationsProps> = ({
  preload = false,
  loadingMessage,
  fallbackComponent,
  ...props
}) => {
  const [isPreloaded, setIsPreloaded] = useState(false);

  // Preload the component when needed
  const preloadComponent = useCallback(() => {
    if (!isPreloaded) {
      import('../../AmazonVariations').then(() => {
        setIsPreloaded(true);
      });
    }
  }, [isPreloaded]);

  // Auto-preload if requested
  React.useEffect(() => {
    if (preload) {
      preloadComponent();
    }
  }, [preload, preloadComponent]);

  // Memoized loading fallback
  const loadingFallback = useMemo(() => (
    <LoadingFallback message={loadingMessage} />
  ), [loadingMessage]);

  return (
    <LazyErrorBoundary fallback={fallbackComponent}>
      <Suspense fallback={loadingFallback}>
        <div
          onMouseEnter={preloadComponent} // Preload on hover
          onFocus={preloadComponent} // Preload on focus
        >
          <AmazonVariations {...props} />
        </div>
      </Suspense>
    </LazyErrorBoundary>
  );
};

export default React.memo(LazyAmazonVariations);