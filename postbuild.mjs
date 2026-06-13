/**
 * Focus CMS - postbuild.mjs
 *
 * Handles isolated build output for:
 *
 * source:
 *   public/build-tmp/
 *
 * targets:
 *   public/themepublic/build/
 *   Modules/<Module>/public/build/
 *
 * Never touches public/build
 */

import fs from 'fs';
import path from 'path';

const BUILD_TARGET =
    process.env.BUILD_TARGET || 'all';

const BUILD_TMP_DIR =
    path.resolve('public/build-tmp');


/**
 * Ensure directory exists
 */
function ensureDir(dir)
{
    fs.mkdirSync(dir, { recursive: true });
}


/**
 * Read manifest
 */
function readManifest()
{
    const manifestPath =
        path.join(BUILD_TMP_DIR, 'manifest.json');

    if (!fs.existsSync(manifestPath))
    {
        throw new Error(
            'manifest.json not found in build-tmp'
        );
    }

    return JSON.parse(
        fs.readFileSync(manifestPath, 'utf8')
    );
}


/**
 * Copy file safely
 */
function copyFileSafe(src, dest)
{
    ensureDir(path.dirname(dest));

    fs.copyFileSync(src, dest);

    console.log('📄 Copied:', dest);
}


/**
 * Copy manifest entry recursively
 */
function copyManifestEntry(manifest, entryKey, targetRoot)
{
    if (!manifest[entryKey])
        return;

    const visited = new Set();

    function walk(key)
    {
        if (!manifest[key]) return;

        if (visited.has(key)) return;

        visited.add(key);

        const chunk = manifest[key];

        if (chunk.file)
        {
            copyFileSafe(
                path.join(BUILD_TMP_DIR, chunk.file),
                path.join(targetRoot, chunk.file)
            );
        }

        if (chunk.css)
        {
            for (const css of chunk.css)
            {
                copyFileSafe(
                    path.join(BUILD_TMP_DIR, css),
                    path.join(targetRoot, css)
                );
            }
        }

        if (chunk.imports)
        {
            for (const imp of chunk.imports)
            {
                walk(imp);
            }
        }
    }

    walk(entryKey);
}


/**
 * Process Theme
 */
function processTheme(manifest)
{
    const themeConfig =
        JSON.parse(
            fs.readFileSync(
                'currentTheme.json',
                'utf8'
            )
        );

    const themeName =
        themeConfig.theme;

    console.log(
        '\n🎨 Processing theme:',
        themeName
    );

    const targetDir =
        path.resolve(
            'public/themepublic/build'
        );

    fs.rmSync(targetDir, {
        recursive: true,
        force: true,
    });

    ensureDir(targetDir);

    fs.writeFileSync(
        path.join(targetDir, 'manifest.json'),
        JSON.stringify(manifest, null, 2)
    );

    copyManifestEntry(
        manifest,
        `Themes/${themeName}/resources/js/theme.js`,
        targetDir
    );

    copyManifestEntry(
        manifest,
        `Themes/${themeName}/resources/css/theme.css`,
        targetDir
    );

    console.log('✅ Theme ready');
}


/**
 * Process Modules
 */
function processModules(manifest)
{
    const modulesDir =
        path.resolve('Modules');

    if (!fs.existsSync(modulesDir))
    {
        console.log('\n📦 No modules found');
        return;
    }

    const modules =
        fs.readdirSync(modulesDir)
            .filter(name =>
                fs.statSync(
                    path.join(modulesDir, name)
                ).isDirectory()
            );

    for (const moduleName of modules)
    {
        console.log(
            '\n📦 Processing module:',
            moduleName
        );

        /**
         * 🔑 TARGET DIR (public/modulepublic)
         */

        const targetDir =
            path.resolve(
                `public/modulepublic/${moduleName}/build`
            );

        fs.rmSync(targetDir, {
            recursive: true,
            force: true,
        });

        ensureDir(targetDir);

        /**
         * Copy manifest
         */

        fs.writeFileSync(
            path.join(targetDir, 'manifest.json'),
            JSON.stringify(manifest, null, 2)
        );

        /**
         * Copy assets
         */

        copyManifestEntry(
            manifest,
            `Modules/${moduleName}/resources/js/module.js`,
            targetDir
        );

        copyManifestEntry(
            manifest,
            `Modules/${moduleName}/resources/css/module.css`,
            targetDir
        );

        console.log('✅ Module ready');
    }
}


/**
 * Cleanup
 */
function cleanup()
{
    if (fs.existsSync(BUILD_TMP_DIR))
    {
        fs.rmSync(
            BUILD_TMP_DIR,
            { recursive: true, force: true }
        );

        console.log('\n🧹 build-tmp removed');
    }
}


/**
 * Main
 */
function main()
{
    console.log('\n🚀 Focus CMS postbuild');

    console.log('🎯 BUILD_TARGET:', BUILD_TARGET);

    if (!fs.existsSync(BUILD_TMP_DIR))
    {
        console.log('No build-tmp found');
        return;
    }

    const manifest =
        readManifest();

    if (
        BUILD_TARGET === 'theme'
        || BUILD_TARGET === 'all'
    )
    {
        processTheme(manifest);
    }

    if (
        BUILD_TARGET === 'modules'
        || BUILD_TARGET === 'all'
    )
    {
        processModules(manifest);
    }

    cleanup();

    console.log('\n✅ Postbuild finished\n');
}

main();