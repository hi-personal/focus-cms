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

export default defineConfig(({ command }) => {

    /**
     * DEV detection (production-safe)
     */
    const isDev = command === 'serve';

    /**
     * BUILD TARGET
     *
     * dev   → always all
     * build → controlled by env (default: app)
     */
    const BUILD_TARGET =
        isDev
            ? 'all'
            : (process.env.BUILD_TARGET || 'app');

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
    console.log('🚀 Mode:', isDev ? 'DEV' : 'BUILD');

    /**
     * Output directory logic
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
     * Entry discovery
     */
    function getViteInputs()
    {
        const inputs = [];

        /*
        |--------------------------------------------------------------------------
        | APP
        |--------------------------------------------------------------------------
        */

        if (BUILD_TARGET === 'app' || BUILD_TARGET === 'all')
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

        if (BUILD_TARGET === 'theme' || BUILD_TARGET === 'all')
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

        if (BUILD_TARGET === 'modules' || BUILD_TARGET === 'all')
        {
            const modulesDir = path.resolve('Modules');

            if (fs.existsSync(modulesDir))
            {
                const modules = fs.readdirSync(modulesDir)
                    .filter(name =>
                        fs.statSync(
                            path.join(modulesDir, name)
                        ).isDirectory()
                    );

                for (const moduleName of modules)
                {
                    const cssPath =
                        `Modules/${moduleName}/resources/css/module.css`;

                    const jsPath =
                        `Modules/${moduleName}/resources/js/module.js`;

                    if (fs.existsSync(cssPath))
                        inputs.push(cssPath);

                    if (fs.existsSync(jsPath))
                        inputs.push(jsPath);
                }
            }
        }

        console.log('\n📦 Vite inputs:\n');
        console.log(inputs);
        console.log('');

        return inputs;
    }

    /**
     * Final config
     */
    return {

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
                input: getViteInputs(),
                refresh: true,
            }),

        ],

        server: {
            host: '0.0.0.0',
            port: 5173,
            strictPort: true,
            cors: true,
            hmr: {
                host: 'localhost'
            }
        },

        build: {

            outDir: getOutDir(),

            emptyOutDir: true,

            manifest: 'manifest.json',

            rollupOptions: {

                output: {

                    entryFileNames:
                        'assets/[name]-[hash].js',

                    chunkFileNames:
                        'assets/[name]-[hash].js',

                    assetFileNames: assetInfo =>
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

    };

});