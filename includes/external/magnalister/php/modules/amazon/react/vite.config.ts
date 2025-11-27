import {defineConfig} from 'vite';
import react from '@vitejs/plugin-react';
import {resolve} from 'path';
import dts from 'vite-plugin-dts';

export default defineConfig({
    plugins: [
        react({
            jsxRuntime: 'classic',
            jsxImportSource: undefined
        }),
        dts({
            insertTypesEntry: true,
            include: ['src/index.ts', 'src/AmazonVariationsSimple.tsx', 'src/AmazonVariations.tsx', 'src/types/**/*', 'src/hooks/**/*'],
            exclude: [
                'src/**/*.test.*',
                'src/**/*.spec.*',
                'src/test/**/*',
                'src/components/**/*',  // Exclude problematic components
                'src/AmazonVariationsExample.tsx',  // Exclude example
                'src/main.tsx'  // Exclude development entry point
            ]
        })
    ],
    // Development server configuration
    server: {
        port: 3000,
        open: true,
        host: true,
        cors: true
    },
    build: {
        lib: {
            entry: resolve(__dirname, 'v2-overrides/bundle-with-globals.ts'),
            name: 'MagnalisterAmazonVariationsV2',
            formats: ['umd'],  // Only UMD for standalone bundle
            fileName: () => `AmazonVariationsV2.bundle.js`
        },
        rollupOptions: {
            // Remove external dependencies - bundle React and ReactDOM with our component
            // external: ['react', 'react-dom'],  // Commented out to bundle everything
            output: {
                // No globals needed since we're bundling everything
                // globals: {
                //   'react': 'React',
                //   'react-dom': 'ReactDOM'
                // }
            }
        },
        sourcemap: true,
        minify: 'esbuild'  // Use esbuild instead of terser for CSP compatibility
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'src'),
            // V2 Override: Redirect AttributeRow to v2-overrides version (imports v2 ValueMatchingTable)
            '@/components/AmazonVariations/AttributeRow': resolve(__dirname, 'v2-overrides/AttributeRow.tsx'),
            // V2 Override: Redirect ValueMatchingTable to v2-overrides version (checkbox disabled)
            '@/components/AmazonVariations/ValueMatching/ValueMatchingTable': resolve(__dirname, 'v2-overrides/ValueMatchingTable.tsx')
        }
    },
    define: {
        __DEV__: JSON.stringify(process.env.NODE_ENV !== 'production'),
        'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'production'),
        'process.env': JSON.stringify({})
    }
});