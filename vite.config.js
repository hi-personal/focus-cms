/**
 * Focus CMS - vite.config.js
 */

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import fs from 'fs';

/**
 * Aktív téma neve
 */
function getCurrentThemeName() {
  try {
    const currentTheme = JSON.parse(
      fs.readFileSync('currentTheme.json', 'utf8')
    );
    return currentTheme.theme;
  } catch {
    return 'default';
  }
}

const themeName = getCurrentThemeName();
console.log('🎨 Active theme:', themeName);

export default defineConfig(() => {

  return {
    resolve: {
      preserveSymlinks: true,
      alias: {
        '@': path.resolve(__dirname, 'resources/js'),
        '@css': path.resolve(__dirname, 'resources/css'),
        '@theme': path.resolve(__dirname, `Themes/${themeName}`),
        '@node': path.resolve(__dirname, 'node_modules'),
      },
    },

    plugins: [
      laravel({
        input: getViteInputs(themeName),
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
      outDir: 'public/build',
      emptyOutDir: true,

      /**
       * 🔑 KRITIKUS:
       * Laravel @vite CSAK ezt fogadja el
       */
      manifest: 'manifest.json',

      rollupOptions: {
        output: {
          entryFileNames: 'assets/[name]-[hash].js',
          chunkFileNames: 'assets/[name]-[hash].js',

          assetFileNames: assetInfo => {
            if (assetInfo.name?.endsWith('.css')) {
              return 'css/[name]-[hash][extname]';
            }
            return 'assets/[name]-[hash][extname]';
          },
        },
      },
    },
  };
});

/**
 * Vite entrypointok (admin + frontend + theme)
 */
function getViteInputs(themeName) {

    const buildTarget =
        process.env.BUILD_TARGET || 'all';

    const inputs = [];

    /*
     |--------------------------------------------------------------------------
     | App
     |--------------------------------------------------------------------------
     */

    if (buildTarget === 'all' || buildTarget === 'app') {

        inputs.push(
            'resources/css/app.css',
            'resources/css/style.css',
            'resources/js/app.js',
            'resources/js/preview-post-content.js',
            'resources/js/uppy.js'
        );
    }

    /*
     |--------------------------------------------------------------------------
     | Theme
     |--------------------------------------------------------------------------
     */

    if (buildTarget === 'all') {

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
     | Modules (AUTO DISCOVERY)
     |--------------------------------------------------------------------------
     */

    const modulesDir =
        path.resolve('Modules');

    if (fs.existsSync(modulesDir)) {

        const modules =
            fs.readdirSync(modulesDir)
                .filter(name =>
                    fs.statSync(
                        path.join(modulesDir, name)
                    ).isDirectory()
                );

        for (const moduleName of modules) {

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

    console.log('\n📦 Vite inputs:\n', inputs, '\n');

    return inputs;
}
