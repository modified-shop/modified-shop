import React from 'react';
// V2 OVERRIDE: Import from v2-overrides to use V2-specific ValueMatchingTable (checkbox disabled)
import AmazonVariations from './AmazonVariations';
import type {AmazonVariationsProps} from '@/types';
import './v2-custom-styles.css';

/**
 * V2 Wrapper for AmazonVariations Component
 *
 * Simple wrapper that forces v2-specific props and imports V2-specific overrides.
 * Sets wrapInTable=false to render only tbody elements (no wrapper div/table).
 * Uses v2-overrides/AmazonVariations which imports V2 ValueMatchingTable (checkbox disabled).
 *
 * @see ./AmazonVariations.tsx - V2 Override component
 * @see ../src/AmazonVariations.tsx - Original V3 component
 */
const AmazonVariationsV2Wrapper: React.FC<AmazonVariationsProps> = (props) => {
    return (
        <AmazonVariations
            {...props}
            wrapInTable={false}      // V2: No wrapper div/table, only tbody elements
            hideHelpColumn={true}    // V2: Hide help column for cleaner layout
        />
    );
};

export default AmazonVariationsV2Wrapper;
