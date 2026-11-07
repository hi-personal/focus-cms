import { defineConfig } from 'vite';
import tailwindcss from "@tailwindcss/vite";
import laravel from "laravel-vite-plugin";
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '../../..');
const isolatedNodeModules = path.resolve(__dirname, 'node_modules');
const isolatedTargetName = "admin";
/* Enforce isolated resolution for bare imports from application source files. */
const focusIsolatedPackageResolver = {
    name: 'focus-isolated-package-resolver',
    enforce: 'pre',

    async resolveId(source, importer) {
        if (
            source.startsWith('.') ||
            source.startsWith('/') ||
            source.startsWith('\\0') ||
            source.startsWith('node:')
        ) {
            return null;
        }

        // Let Vite resolve project-root source files and configured entry points.
        const projectSourcePath = path.resolve(projectRoot, source);

        if (
            projectSourcePath.startsWith(projectRoot + path.sep) &&
            (
                source.startsWith('resources/') ||
                source.startsWith('Themes/')
            )
        ) {
            return null;
        }
        /*
         * App source files are outside the generated target directory.
         * Resolve their bare imports from the isolated target instead.
         *
         * For imports originating inside a package, keep the real importer
         * so nested package dependencies can resolve in their own context.
         */
        const importerIsInsideTarget =
            importer &&
            path.relative(isolatedNodeModules, importer) !== '..' &&
            !path.relative(isolatedNodeModules, importer).startsWith('..' + path.sep) &&
            !path.isAbsolute(path.relative(isolatedNodeModules, importer));

        const resolutionImporter = importerIsInsideTarget
            ? importer
            : path.join(__dirname, '__focus_target_resolver__.js');

        const resolved = await this.resolve(
            source,
            resolutionImporter,
            { skipSelf: true }
        );

        if (!resolved) {
            throw new Error(
                `Cannot resolve '${source}' from this target. ` +
                `Install it in tools/${isolatedTargetName}/generated.`
            );
        }

        if (resolved.external || resolved.id.startsWith('node:')) {
            return null;
        }

        const resolvedId = resolved.id.startsWith('file:')
            ? fileURLToPath(resolved.id)
            : resolved.id;

        const resolvedPath = resolvedId.split('?')[0];

        // Vite's optimized dependencies are served from its own cache directory.
        const isolatedViteCache = path.resolve(__dirname, '.vite');
        const cacheRelative = path.relative(isolatedViteCache, resolvedPath);

        if (
            cacheRelative === '' ||
            (
                cacheRelative !== '..' &&
                !cacheRelative.startsWith('..' + path.sep) &&
                !path.isAbsolute(cacheRelative)
            )
        ) {
            return resolved.id;
        }

        if (!path.isAbsolute(resolvedPath)) {
            return null;
        }

        const relative = path.relative(isolatedNodeModules, resolvedPath);

        if (
            relative === '..' ||
            relative.startsWith('..' + path.sep) ||
            path.isAbsolute(relative)
        ) {
            throw new Error(
                `Import '${source}' resolved outside this target's isolated ` +
                `node_modules: ${resolvedPath}. ` +
                `Install the dependency in tools/${isolatedTargetName}/generated.`
            );
        }

        return resolved.id;
    },
};

export default defineConfig({
    "root": projectRoot,
    "cacheDir": path.resolve(projectRoot, "tools/admin/generated/.vite"),
    "resolve": {
        "preserveSymlinks": true,
    },
    "server": {
        "host": "0.0.0.0",
        "port": 5173,
        "strictPort": true,
        "origin": "http://localhost:5173",
        "cors": {
            "origin": [
                "http://localhost:90",
                "http://0.0.0.0:90",
            ],
        },
        "hmr": {
            "host": "localhost",
        },
        "fs": {
            "strict": false,
        },
        "watch": {
            "ignored": [
                "**/public/page-cache/**",
            ],
        },
    },
    "build": {
        "outDir": path.resolve(projectRoot, "public/build/admin"),
        "assetsDir": "assets",
        "manifest": "manifest.json",
        "emptyOutDir": false,
    },
    plugins: [
        focusIsolatedPackageResolver,
        tailwindcss(),
        laravel({
    "input": [
        "resources/js/app.js",
        "resources/css/app.css",
    ],
    "refresh": [
        "resources/views/**/*.blade.php",
    ],
    "hotFile": path.resolve(projectRoot, "public/build/admin/hot"),
    "buildDirectory": "build/admin",
}),
    ],
});
