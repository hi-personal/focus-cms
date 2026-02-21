/**
 * Focus CMS - postbuild.mjs
 *
 * Handles isolated build output for:
 * - Core app (already handled by Vite)
 * - Active Theme
 * - All Modules (auto-discovery)
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

/**
 * Resolve manifest path robustly
 */
function resolveManifestPath(buildDir) {

    const candidates = [
        path.join(buildDir, 'manifest.json'),
        path.join(buildDir, '.vite', 'manifest.json'),
    ];

    for (const p of candidates) {
        if (fs.existsSync(p)) return p;
    }

    throw new Error('Vite manifest.json not found in public/build');
}

/**
 * Collect all related manifest entries recursively
 */
function collectAllRelatedAssets(manifest, entryKey) {

    const visited = new Set();
    const queue = [entryKey];

    while (queue.length) {

        const key = queue.pop();

        if (visited.has(key)) continue;
        if (!manifest[key]) continue;

        visited.add(key);

        for (const importKey of manifest[key].imports || []) {
            queue.push(importKey);
        }
    }

    return [...visited];
}

/**
 * Copy manifest-related assets
 */
function copyManifestAssets({
    manifest,
    entryKey,
    buildDir,
    targetDir,
    type
}) {

    if (!manifest[entryKey]) return;

    const relatedKeys =
        collectAllRelatedAssets(manifest, entryKey);

    for (const key of relatedKeys) {

        const file = manifest[key]?.file;

        if (!file) continue;

        const src = path.join(buildDir, file);

        if (!fs.existsSync(src)) continue;

        const dest = path.join(
            targetDir,
            type,
            path.basename(file)
        );

        fs.copyFileSync(src, dest);

        console.log(`📄 Copied: ${dest}`);
    }
}

/**
 * Copy static assets from vite.assets.js
 */
async function copyStaticAssets(configPath, targetDir) {

    if (!fs.existsSync(configPath)) return;

    const config =
        await import(`file://${path.resolve(configPath)}`);

    const assets =
        config.themeAssets ||
        config.moduleAssets ||
        [];

    for (const asset of assets) {

        const srcDir = path.resolve(asset.src);

        if (!fs.existsSync(srcDir)) continue;

        const destDir =
            path.join(targetDir, asset.dest);

        fs.mkdirSync(destDir, { recursive: true });

        for (const file of fs.readdirSync(srcDir)) {

            const srcFile = path.join(srcDir, file);

            if (!fs.statSync(srcFile).isFile()) continue;

            const destFile =
                path.join(destDir, file);

            fs.copyFileSync(srcFile, destFile);

            console.log(`📁 Asset copied: ${destFile}`);
        }
    }
}

/**
 * Process active theme
 */
async function processTheme(manifest, buildDir) {

    const currentTheme =
        JSON.parse(fs.readFileSync(
            'currentTheme.json',
            'utf8'
        ));

    const themeName = currentTheme.theme;

    const themeRoot =
        path.join('Themes', themeName);

    const themeBuildDir =
        path.join(themeRoot, 'public', 'build');

    console.log(`\n🎨 Processing theme: ${themeName}`);

    fs.rmSync(themeBuildDir, {
        recursive: true,
        force: true
    });

    fs.mkdirSync(
        path.join(themeBuildDir, 'css'),
        { recursive: true }
    );

    fs.mkdirSync(
        path.join(themeBuildDir, 'assets'),
        { recursive: true }
    );

    fs.writeFileSync(
        path.join(themeBuildDir, 'manifest.json'),
        JSON.stringify(manifest, null, 2)
    );

    copyManifestAssets({
        manifest,
        entryKey:
            `Themes/${themeName}/resources/css/theme.css`,
        buildDir,
        targetDir: themeBuildDir,
        type: 'css'
    });

    copyManifestAssets({
        manifest,
        entryKey:
            `Themes/${themeName}/resources/js/theme.js`,
        buildDir,
        targetDir: themeBuildDir,
        type: 'assets'
    });

    await copyStaticAssets(
        path.join(themeRoot, 'vite.assets.js'),
        themeBuildDir
    );

    console.log(`✅ Theme ready: ${themeName}`);
}

/**
 * Process all modules (auto discovery)
 */
async function processModules(manifest, buildDir) {

    const modulesRoot = path.resolve('Modules');

    if (!fs.existsSync(modulesRoot)) {
        console.log('\n📦 No modules found');
        return;
    }

    const modules =
        fs.readdirSync(modulesRoot)
            .filter(name =>
                fs.statSync(
                    path.join(modulesRoot, name)
                ).isDirectory()
            );

    for (const moduleName of modules) {

        console.log(`\n📦 Processing module: ${moduleName}`);

        const moduleRoot =
            path.join(modulesRoot, moduleName);

        const moduleBuildDir =
            path.join(
                moduleRoot,
                'public',
                'build'
            );

        fs.rmSync(moduleBuildDir, {
            recursive: true,
            force: true
        });

        fs.mkdirSync(
            path.join(moduleBuildDir, 'css'),
            { recursive: true }
        );

        fs.mkdirSync(
            path.join(moduleBuildDir, 'assets'),
            { recursive: true }
        );

        fs.writeFileSync(
            path.join(moduleBuildDir, 'manifest.json'),
            JSON.stringify(manifest, null, 2)
        );

        copyManifestAssets({
            manifest,
            entryKey:
                `Modules/${moduleName}/resources/css/module.css`,
            buildDir,
            targetDir: moduleBuildDir,
            type: 'css'
        });

        copyManifestAssets({
            manifest,
            entryKey:
                `Modules/${moduleName}/resources/js/module.js`,
            buildDir,
            targetDir: moduleBuildDir,
            type: 'assets'
        });

        await copyStaticAssets(
            path.join(moduleRoot, 'vite.assets.js'),
            moduleBuildDir
        );

        console.log(`✅ Module ready: ${moduleName}`);
    }
}

/**
 * Main execution
 */
async function main() {

    try {

        const buildDir =
            path.resolve('public/build');

        const manifestPath =
            resolveManifestPath(buildDir);

        const manifest =
            JSON.parse(
                fs.readFileSync(
                    manifestPath,
                    'utf8'
                )
            );

        await processTheme(manifest, buildDir);

        await processModules(manifest, buildDir);

        console.log(
            '\n🚀 All theme and module assets built successfully\n'
        );

    } catch (err) {

        console.error(
            '\n❌ Postbuild failed:',
            err
        );

        process.exit(1);
    }
}

main();