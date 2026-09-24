import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const projectRoot = path.resolve(__dirname, '../../..');

export default defineConfig({
    root: projectRoot,

    cacheDir: path.resolve(
        projectRoot,
        'tools/front/generated/.vite'
    ),

    plugins: [
        tailwindcss(),

        laravel({
            input: [
    "Themes/FocusDefaultTheme/resources/js/theme.js",
    "Themes/FocusDefaultTheme/resources/css/theme.css"
],

            refresh: [
    "Themes/FocusDefaultTheme/resources/views/**/*.blade.php"
],

            hotFile: path.resolve(
                projectRoot,
                'public/build/front/hot'
            ),
            buildDirectory: 'build/front',
        }),
    ],

    server: {
        host: '0.0.0.0',
        port: 5174,
        strictPort: true,

        origin: 'http://localhost:5174',

        cors: {
            origin: [
                'http://localhost:90',
                'http://0.0.0.0:90',
            ],
        },

        hmr: {
            host: 'localhost',
        },

        fs: {
            strict: false,
        },

        watch: {
            ignored: [
                '**/public/page-cache/**',
            ],
        },
    },

    resolve: {
        alias: {
            '@boxicons/js': path.resolve(
                projectRoot,
                'tools/front/generated/node_modules/@boxicons/js'
            ),
        },

        modules: [
            path.resolve(
                projectRoot,
                'tools/front/generated/node_modules'
            ),

            path.resolve(
                projectRoot,
                'node_modules'
            ),
        ],
    },

    build: {
        outDir: path.resolve(
            projectRoot,
            'public/build/front'
        ),

        assetsDir: 'assets',
        manifest: 'manifest.json',
        emptyOutDir: false,
    },
});