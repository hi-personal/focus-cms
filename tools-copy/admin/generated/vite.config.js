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
        'tools/admin/generated/.vite'
    ),

    plugins: [
        tailwindcss(),

        laravel({
            input: [
    "resources/js/app.js",
    "resources/css/app.css"
],

            refresh: [
    "resources/views/**/*.blade.php"
],

            hotFile: path.resolve(
                projectRoot,
                'public/build/admin/hot'
            ),
            buildDirectory: 'build/admin',
        }),
    ],

    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,

        origin: 'http://localhost:5173',

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
        modules: [
            path.resolve(
                projectRoot,
                'tools/admin/generated/node_modules'
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
            'public/build/admin'
        ),

        assetsDir: 'assets',
        manifest: 'manifest.json',
        emptyOutDir: false,
    },
});