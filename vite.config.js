/**
 * Focus CMS - vite.config.js
 *
 * Fully isolated build system:
 *
 * app      → public/build
 * theme    → public/build-tmp
 * modules  → public/build-tmp
 *
 * postbuild.mjs copies from build-tmp → final destinations
 */

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import fs from 'fs';

/**
 * BUILD TARGET
 */

const BUILD_TARGET =
    process.env.BUILD_TARGET || 'app';

/**
 * Aktív téma neve
 */

function getCurrentThemeName()
{
    try
    {
        const currentTheme =
            JSON.parse(
                fs.readFileSync(
                    'currentTheme.json',
                    'utf8'
                )
            );

        return currentTheme.theme;
    }
    catch
    {
        return 'default';
    }
}

const themeName = getCurrentThemeName();

console.log('\n🎯 BUILD_TARGET:', BUILD_TARGET);
console.log('🎨 Active theme:', themeName);

/**
 * Build output directory
 *
 * CRITICAL:
 * Only app builds go to public/build
 *
 * theme/modules build to public/build-tmp
 */

function getOutDir()
{
    if (BUILD_TARGET === 'app')
    {
        return 'public/build';
    }

    return 'public/build-tmp';
}

/**
 * Vite config
 */

export default defineConfig({

    resolve: {
        preserveSymlinks: true,

        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@css': path.resolve(__dirname, 'resources/css'),
            '@theme': path.resolve(
                __dirname,
                `Themes/${themeName}`
            ),
            '@node': path.resolve(
                __dirname,
                'node_modules'
            ),
        },
    },

    plugins: [

        laravel({

            input:
                getViteInputs(themeName),

            refresh: true,

        }),

    ],

    server: {

        host: '0.0.0.0',

        port: 5173,

        strictPort: true,

        cors: true,

        hmr: {

            host: '10.0.0.1',

            port: 5173,

        },

    },

    build: {

        /**
         * ISOLATED OUTPUT DIR
         */

        outDir:
            getOutDir(),

        emptyOutDir: true,

        /**
         * Manifest always generated
         */

        manifest: 'manifest.json',

        /**
         * Consistent structure
         */

        rollupOptions: {

            output: {

                entryFileNames:
                    'assets/[name]-[hash].js',

                chunkFileNames:
                    'assets/[name]-[hash].js',

                assetFileNames:
                    assetInfo =>
                {
                    if (
                        assetInfo.name?.endsWith('.css')
                    )
                    {
                        return 'css/[name]-[hash][extname]';
                    }

                    return 'assets/[name]-[hash][extname]';
                },

            },

        },

    },

});

/**
 * Entry discovery
 */

function getViteInputs(themeName)
{
    const inputs = [];

    /*
    |--------------------------------------------------------------------------
    | APP
    |--------------------------------------------------------------------------
    */

    if (
        BUILD_TARGET === 'app'
        || BUILD_TARGET === 'all'
    )
    {
        inputs.push(

            'resources/css/app.css',

            'resources/css/style.css',

            'resources/js/app.js',

            'resources/js/preview-post-content.js',

            'resources/js/uppy.js',

        );
    }

    /*
    |--------------------------------------------------------------------------
    | THEME
    |--------------------------------------------------------------------------
    */

    if (
        BUILD_TARGET === 'theme'
        || BUILD_TARGET === 'all'
    )
    {
        const themeCss =
            `Themes/${themeName}/resources/css/theme.css`;

        const themeJs =
            `Themes/${themeName}/resources/js/theme.js`;

        if (fs.existsSync(themeCss))
            inputs.push(themeCss);

        if (fs.existsSync(themeJs))
            inputs.push(themeJs);
    }

    /*
    |--------------------------------------------------------------------------
    | MODULES
    |--------------------------------------------------------------------------
    */

    if (
        BUILD_TARGET === 'modules'
        || BUILD_TARGET === 'all'
    )
    {
        const modulesDir =
            path.resolve('Modules');

        if (fs.existsSync(modulesDir))
        {
            const modules =
                fs.readdirSync(modulesDir)
                    .filter(name =>
                        fs.statSync(
                            path.join(
                                modulesDir,
                                name
                            )
                        ).isDirectory()
                    );

            for (const moduleName of modules)
            {
                const css =
                    `Modules/${moduleName}/resources/css/module.css`;

                const js =
                    `Modules/${moduleName}/resources/js/module.js`;

                if (fs.existsSync(css))
                    inputs.push(css);

                if (fs.existsSync(js))
                    inputs.push(js);
            }
        }
    }

    console.log('\n📦 Vite inputs:\n');
    console.log(inputs);
    console.log('');

    return inputs;
}