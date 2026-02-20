import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

async function processThemeAssets() {
  try {
    const buildDir = path.resolve('./public/build');

    // === 0. Manifest helyének robosztus feloldása ===
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

    const mainManifestPath = resolveManifestPath(buildDir);
    const mainManifest = JSON.parse(
      fs.readFileSync(mainManifestPath, 'utf8')
    );

    const currentTheme = JSON.parse(
      fs.readFileSync('currentTheme.json', 'utf8')
    );
    const themeName = currentTheme.theme;

    const themePublicDir = path.join('Themes', themeName, 'public');
    const themeBuildDir = path.join(themePublicDir, 'build');

    // === 1. Theme build mappa törlése ===
    if (fs.existsSync(themeBuildDir)) {
      console.log(`🧹 Cleaning theme build directory: ${themeBuildDir}`);
      fs.rmSync(themeBuildDir, { recursive: true, force: true });
    }

    // === 2. Könyvtárstruktúra létrehozása ===
    fs.mkdirSync(path.join(themeBuildDir, 'css'), { recursive: true });
    fs.mkdirSync(path.join(themeBuildDir, 'assets'), { recursive: true });

    // === 3. Teljes manifest másolása ===
    fs.writeFileSync(
      path.join(themeBuildDir, 'manifest.json'),
      JSON.stringify(mainManifest, null, 2)
    );

    // === Segédfüggvény: rekurzív asset-gyűjtés ===
    function collectAllRelatedAssets(manifest, entryKey, visited = new Set()) {
      const queue = [entryKey];
      const allKeys = new Set();

      while (queue.length) {
        const key = queue.pop();
        if (visited.has(key) || !manifest[key]) continue;

        visited.add(key);
        allKeys.add(key);

        for (const importKey of manifest[key].imports || []) {
          if (!visited.has(importKey)) {
            queue.push(importKey);
          }
        }
      }

      return [...allKeys];
    }

    // === 4. CSS másolás (+ importok) ===
    const cssEntryKey = path
      .join('Themes', themeName, 'resources', 'css', 'theme.css')
      .replace(/\\/g, '/');

    if (mainManifest[cssEntryKey]) {
      const cssKeys = collectAllRelatedAssets(mainManifest, cssEntryKey);

      for (const key of cssKeys) {
        const file = mainManifest[key].file;
        const src = path.join(buildDir, file);
        const dest = path.join(themeBuildDir, 'css', path.basename(file));

        fs.copyFileSync(src, dest);
        console.log(`🎨 CSS copied: ${dest}`);
      }
    }

    // === 5. JS másolás (+ importok) ===
    const jsEntryKey = path
      .join('Themes', themeName, 'resources', 'js', 'theme.js')
      .replace(/\\/g, '/');

    if (mainManifest[jsEntryKey]) {
      const jsKeys = collectAllRelatedAssets(mainManifest, jsEntryKey);

      for (const key of jsKeys) {
        const file = mainManifest[key].file;
        const src = path.join(buildDir, file);
        const dest = path.join(themeBuildDir, 'assets', path.basename(file));

        fs.copyFileSync(src, dest);
        console.log(`📦 JS copied: ${dest}`);
      }
    }

    // === 6. Statikus assetek (fonts, images stb.) ===
    const assetsConfigPath = path.join(
      'Themes',
      themeName,
      'vite.assets.js'
    );

    if (fs.existsSync(assetsConfigPath)) {
      const { themeAssets = [] } = await import(
        `file://${path.resolve(assetsConfigPath)}`
      );

      for (const asset of themeAssets) {
        const srcDir = path.resolve(asset.src);
        const destDir = path.join(themeBuildDir, asset.dest);

        if (!fs.existsSync(srcDir)) continue;

        fs.mkdirSync(destDir, { recursive: true });

        for (const file of fs.readdirSync(srcDir)) {
          const srcFile = path.join(srcDir, file);
          const destFile = path.join(destDir, file);

          if (fs.statSync(srcFile).isFile()) {
            fs.copyFileSync(srcFile, destFile);
            console.log(`📁 Asset copied: ${destFile}`);
          }
        }
      }
    }

    console.log(`\n✅ Theme "${themeName}" build finished successfully!\n`);
  } catch (err) {
    console.error('❌ Theme build failed:', err);
    process.exit(1);
  }
}

processThemeAssets();
