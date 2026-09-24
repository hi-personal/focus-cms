<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;

class FocusSyncCommand extends Command
{
    protected $signature = 'focus:build-assets
                            {--admin-port=5173 : Admin Vite development server port}
                            {--front-port=5174 : Front Vite development server port}';

    protected $description = 'Generate Focus CMS Vite and npm configuration';

    protected array $adminConfig = [];

    protected array $frontConfig = [];

    protected array $activeModules = [];

    protected array $adminEntries = [];

    protected array $frontEntries = [];

    protected array $adminWatch = [];

    protected array $frontWatch = [];

    protected array $adminNpmDependencies = [];

    protected array $frontNpmDependencies = [];

    protected string $projectRoot;

    protected string $adminPort;

    protected string $frontPort;

    public function handle(): int
    {
        $this->projectRoot = base_path();

        $this->adminPort = (string) $this->option('admin-port');
        $this->frontPort = (string) $this->option('front-port');

        $this->components->info(
            'Focus CMS asset configuration'
        );

        try {
            $this->loadConfigurations();

            $this->generateViteManagerConfig();

            $this->generateRootPackageJson();
            $this->generateAdminPackageJson();
            $this->generateFrontPackageJson();

            $this->generateAdminViteConfig();
            $this->generateFrontViteConfig();

            $this->printSummary();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->components->error(
                $e->getMessage()
            );

            if ($this->getOutput()->isVerbose()) {
                $this->line('');
                $this->line(
                    $e->getTraceAsString()
                );
            }

            return self::FAILURE;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Configuration loading
    |--------------------------------------------------------------------------
    */

    protected function loadConfigurations(): void
    {
        $this->loadActiveTheme();

        $this->loadActiveModules();

        $this->loadAdminRoot();

        $this->loadFrontTheme();

        $this->loadModuleConfigurations();
    }

    protected function loadActiveTheme(): void
    {
        $path = base_path('currentTheme.json');

        if (!File::exists($path)) {
            throw new RuntimeException(
                'currentTheme.json not found.'
            );
        }

        $config = $this->readJson($path);

        $themeName = $config['theme'] ?? null;

        if (!$themeName) {
            throw new RuntimeException(
                'currentTheme.json does not contain a "theme" value.'
            );
        }

        $themePath = base_path(
            'Themes/' . $themeName
        );

        if (!is_dir($themePath)) {
            throw new RuntimeException(
                "Active theme '{$themeName}' does not exist: {$themePath}"
            );
        }

        $themeBuild = $themePath . '/focus-build.json';

        if (!File::exists($themeBuild)) {
            throw new RuntimeException(
                "Active theme '{$themeName}' does not contain focus-build.json."
            );
        }

        $themeConfig = $this->readJson($themeBuild);

        if (($themeConfig['type'] ?? null) !== 'theme') {
            throw new RuntimeException(
                "Invalid theme focus-build.json in '{$themeName}': type must be 'theme'."
            );
        }

        if (!isset($themeConfig['front'])) {
            throw new RuntimeException(
                "Active theme '{$themeName}' does not define a front configuration."
            );
        }

        $this->frontConfig = $themeConfig['front'];

        $this->addFrontEntries(
            $this->frontConfig['build']['js'] ?? []
        );

        $this->addFrontEntries(
            $this->frontConfig['build']['css'] ?? []
        );

        $this->addFrontWatch(
            $this->frontConfig['watch']['views'] ?? []
        );

        $this->addNpmPackages(
            $this->frontConfig['plugins']['npm'] ?? [],
            'front'
        );
    }

    protected function loadActiveModules(): void
    {
        $path = base_path('activeModules.json');

        if (!File::exists($path)) {
            $this->components->warn(
                'activeModules.json not found. No modules will be loaded.'
            );

            return;
        }

        $config = $this->readJson($path);

        $this->activeModules = $config['modules'] ?? [];

        if (!is_array($this->activeModules)) {
            throw new RuntimeException(
                'activeModules.json: "modules" must be an array.'
            );
        }
    }

    protected function loadAdminRoot(): void
    {
        $path = base_path('focus-build.json');

        if (!File::exists($path)) {
            throw new RuntimeException(
                'Root focus-build.json not found.'
            );
        }

        $config = $this->readJson($path);

        if (($config['type'] ?? null) !== 'app') {
            throw new RuntimeException(
                'Root focus-build.json must contain type "app".'
            );
        }

        if (!isset($config['admin'])) {
            throw new RuntimeException(
                'Root focus-build.json does not contain an admin configuration.'
            );
        }

        $this->adminConfig = $config['admin'];

        $this->addAdminEntries(
            $this->adminConfig['build']['js'] ?? []
        );

        $this->addAdminEntries(
            $this->adminConfig['build']['css'] ?? []
        );

        $this->addAdminWatch(
            $this->adminConfig['watch']['views'] ?? []
        );

        $this->addNpmPackages(
            $this->adminConfig['plugins']['npm'] ?? [],
            'admin'
        );
    }

    protected function loadFrontTheme(): void
    {
        /*
         * The active theme was already loaded by loadActiveTheme().
         *
         * This method intentionally exists as a separate logical stage
         * because the Focus CMS architecture treats the active theme as
         * the front application.
         */
    }

    protected function loadModuleConfigurations(): void
    {
        foreach ($this->activeModules as $moduleName) {
            $modulePath = base_path(
                'Modules/' . $moduleName
            );

            if (!is_dir($modulePath)) {
                $this->components->warn(
                    "Active module '{$moduleName}' does not exist. Skipping."
                );

                continue;
            }

            /*
             * Modules may be symlinks. realpath() resolves the actual
             * project directory while the public/project-relative path
             * remains Modules/<ModuleName>.
             */
            $realModulePath = realpath($modulePath)
                ?: $modulePath;

            $buildPath = $realModulePath . '/focus-build.json';

            if (!File::exists($buildPath)) {
                $this->components->info(
                    "Module '{$moduleName}' has no focus-build.json. Skipping assets."
                );

                continue;
            }

            $config = $this->readJson($buildPath);

            if (($config['type'] ?? null) !== 'module') {
                $this->components->warn(
                    "Module '{$moduleName}' has invalid focus-build.json type. Skipping."
                );

                continue;
            }

            /*
             * A module can contribute to ADMIN, FRONT, or both.
             */

            if (isset($config['admin'])) {
                $admin = $config['admin'];

                $this->addModuleEntries(
                    $moduleName,
                    $admin['build']['js'] ?? [],
                    'admin'
                );

                $this->addModuleEntries(
                    $moduleName,
                    $admin['build']['css'] ?? [],
                    'admin'
                );

                $this->addModuleWatch(
                    $moduleName,
                    $admin['watch']['views'] ?? [],
                    'admin'
                );

                $this->addNpmPackages(
                    $admin['plugins']['npm'] ?? [],
                    'admin'
                );
            }

            if (isset($config['front'])) {
                $front = $config['front'];

                $this->addModuleEntries(
                    $moduleName,
                    $front['build']['js'] ?? [],
                    'front'
                );

                $this->addModuleEntries(
                    $moduleName,
                    $front['build']['css'] ?? [],
                    'front'
                );

                $this->addModuleWatch(
                    $moduleName,
                    $front['watch']['views'] ?? [],
                    'front'
                );

                $this->addNpmPackages(
                    $front['plugins']['npm'] ?? [],
                    'front'
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Entry points
    |--------------------------------------------------------------------------
    */

    protected function addAdminEntries(array $entries): void
    {
        foreach ($entries as $entry) {
            $entry = $this->normalizeProjectPath(
                $entry
            );

            if ($entry !== '') {
                $this->adminEntries[] = $entry;
            }
        }

        $this->adminEntries = array_values(
            array_unique($this->adminEntries)
        );
    }

    protected function addFrontEntries(array $entries): void
    {
        foreach ($entries as $entry) {
            $entry = $this->normalizeProjectPath(
                $entry
            );

            if ($entry !== '') {
                $this->frontEntries[] = $entry;
            }
        }

        $this->frontEntries = array_values(
            array_unique($this->frontEntries)
        );
    }

    protected function addModuleEntries(
        string $moduleName,
        array $entries,
        string $target
    ): void {
        foreach ($entries as $entry) {
            $entry = $this->normalizeModulePath(
                $moduleName,
                $entry
            );

            if ($entry === '') {
                continue;
            }

            if ($target === 'admin') {
                $this->adminEntries[] = $entry;
            } else {
                $this->frontEntries[] = $entry;
            }
        }

        if ($target === 'admin') {
            $this->adminEntries = array_values(
                array_unique($this->adminEntries)
            );
        } else {
            $this->frontEntries = array_values(
                array_unique($this->frontEntries)
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Watch paths
    |--------------------------------------------------------------------------
    */

    protected function addAdminWatch(array $paths): void
    {
        foreach ($paths as $path) {
            $path = $this->normalizeProjectPath(
                $path
            );

            if ($path !== '') {
                $this->adminWatch[] = $path;
            }
        }

        $this->adminWatch = array_values(
            array_unique($this->adminWatch)
        );
    }

    protected function addFrontWatch(array $paths): void
    {
        foreach ($paths as $path) {
            $path = $this->normalizeProjectPath(
                $path
            );

            if ($path !== '') {
                $this->frontWatch[] = $path;
            }
        }

        $this->frontWatch = array_values(
            array_unique($this->frontWatch)
        );
    }

    protected function addModuleWatch(
        string $moduleName,
        array $paths,
        string $target
    ): void {
        foreach ($paths as $path) {
            $path = $this->normalizeModulePath(
                $moduleName,
                $path
            );

            if ($path === '') {
                continue;
            }

            if ($target === 'admin') {
                $this->adminWatch[] = $path;
            } else {
                $this->frontWatch[] = $path;
            }
        }

        $this->adminWatch = array_values(
            array_unique($this->adminWatch)
        );

        $this->frontWatch = array_values(
            array_unique($this->frontWatch)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | npm packages
    |--------------------------------------------------------------------------
    */

    protected function addNpmPackages(
        array $packages,
        string $target
    ): void {
        if (!in_array($target, ['admin', 'front'], true)) {
            throw new RuntimeException(
                "Invalid npm target '{$target}'. Expected 'admin' or 'front'."
            );
        }

        if ($target === 'admin') {
            $dependencies =& $this->adminNpmDependencies;
        } else {
            $dependencies =& $this->frontNpmDependencies;
        }

        foreach ($packages as $specification) {
            if (!is_string($specification)) {
                continue;
            }

            $specification = trim($specification);

            if ($specification === '') {
                continue;
            }

            [$name, $version] = $this->parseNpmPackage(
                $specification
            );

            /*
             * The same package can be requested multiple times
             * by components belonging to the same target.
             *
             * If the same target specifies conflicting versions,
             * report an actual conflict instead of silently
             * overwriting the existing version.
             *
             * ADMIN and FRONT are intentionally isolated, so the
             * same package may have different versions in the two
             * environments.
             */
            if (isset($dependencies[$name])) {
                $existing = $dependencies[$name];

                if (
                    $existing !== $version &&
                    $existing !== '*' &&
                    $version !== '*'
                ) {
                    throw new RuntimeException(
                        "npm package version conflict for '{$name}' " .
                        "in {$target}: " .
                        "'{$existing}' vs '{$version}'."
                    );
                }

                /*
                 * If one request is wildcard and the other specifies
                 * a version, keep the explicit version.
                 */
                if (
                    $existing === '*' &&
                    $version !== '*'
                ) {
                    $dependencies[$name] = $version;
                }

                continue;
            }

            $dependencies[$name] = $version;
        }

        ksort($dependencies);

        unset($dependencies);
    }

    protected function parseNpmPackage(
        string $specification
    ): array {
        /*
         * IMPORTANT:
         *
         * Never use explode('@', ...) here.
         *
         * Scoped packages such as:
         *
         *     @uppy/xhr-upload@6.0.0
         *     @mdi/font@7.4.47
         *
         * contain @ in their package name.
         *
         * The version separator is the LAST @.
         */

        if (str_starts_with($specification, '@')) {
            $separator = strrpos(
                $specification,
                '@'
            );

            if (
                $separator !== false &&
                $separator > strpos(
                    $specification,
                    '/'
                )
            ) {
                $name = substr(
                    $specification,
                    0,
                    $separator
                );

                $version = substr(
                    $specification,
                    $separator + 1
                );

                return [
                    $name,
                    $version !== ''
                        ? $version
                        : '*',
                ];
            }

            return [
                $specification,
                '*',
            ];
        }

        $separator = strrpos(
            $specification,
            '@'
        );

        if (
            $separator !== false &&
            $separator > 0
        ) {
            return [
                substr(
                    $specification,
                    0,
                    $separator
                ),
                substr(
                    $specification,
                    $separator + 1
                ) ?: '*',
            ];
        }

        return [
            $specification,
            '*',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Path normalization
    |--------------------------------------------------------------------------
    */

    protected function normalizeProjectPath(
        string $path
    ): string {
        $path = trim(
            str_replace(
                '\\',
                '/',
                $path
            )
        );

        return ltrim(
            $path,
            '/'
        );
    }

    protected function normalizeModulePath(
        string $moduleName,
        string $path
    ): string {
        $path = trim(
            str_replace(
                '\\',
                '/',
                $path
            )
        );

        if ($path === '') {
            return '';
        }

        /*
         * focus-build.json paths inside a module are relative to
         * the module root.
         */
        if (str_starts_with($path, 'Modules/')) {
            return ltrim(
                $path,
                '/'
            );
        }

        return 'Modules/' .
            trim($moduleName, '/') .
            '/' .
            ltrim($path, '/');
    }

    /*
    |--------------------------------------------------------------------------
    | ttbooking/vite-manager configuration
    |--------------------------------------------------------------------------
    */

    protected function generateViteManagerConfig(): void
    {
        $configPath = config_path(
            'vite.php'
        );

        $apps = [
            'admin' => [
                'entry_points' => $this->adminEntries,
                'build_directory' => 'build/admin',
                'hot_file' => 'build/admin/hot',
            ],

            'front' => [
                'entry_points' => $this->frontEntries,
                'build_directory' => 'build/front',
                'hot_file' => 'build/front/hot',
            ],
        ];

        $content = "<?php\n\n";

        $content .= "return [\n\n";

        /*
         * This is the Laravel-side default application used by
         * ttbooking/vite-manager.
         *
         * It is intentionally kept separate from the Node/Vite
         * configuration selection, which now happens explicitly
         * through --config in package.json.
         */
        $content .= "    'apps' => " .
            $this->exportPhpArray(
                $apps,
                1
            ) .
            ",\n\n";

        $content .= "];\n";

        File::ensureDirectoryExists(
            dirname($configPath)
        );

        File::put(
            $configPath,
            $content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Root package.json
    |--------------------------------------------------------------------------
    */

    protected function generateRootPackageJson(): void
    {
        /*
         * The root package.json is only the orchestration layer.
         *
         * ADMIN and FRONT have their own package.json files and
         * their own node_modules directories.
         *
         * Therefore the root package must NOT contain the actual
         * application npm dependencies.
         */

        $package = [
            'name' => 'focus-cms-assets',
            'version' => '1.0.0',
            'private' => true,
            'type' => 'module',

            'scripts' => [
                'install:admin' =>
                    'npm install --prefix tools/admin/generated',

                'install:front' =>
                    'npm install --prefix tools/front/generated',

                'install:all' =>
                    'npm run install:admin && npm run install:front',

                'dev:admin' =>
                    'npm --prefix tools/admin/generated run dev',

                'dev:front' =>
                    'npm --prefix tools/front/generated run dev',

                'dev:all' =>
                    'concurrently --kill-others-on-fail ' .
                    '--names "ADMIN,FRONT" ' .
                    '--prefix-colors "blue,green" ' .
                    '"npm run dev:admin" ' .
                    '"npm run dev:front"',

                'build:admin' =>
                    'npm --prefix tools/admin/generated run build',

                'build:front' =>
                    'npm --prefix tools/front/generated run build',

                'build:all' =>
                    'npm run build:admin && npm run build:front',
            ],

            'devDependencies' => [
                'concurrently' => '^9.0.0',
            ],
        ];

        $path = base_path(
            'package.json'
        );

        File::ensureDirectoryExists(
            dirname($path)
        );

        File::put(
            $path,
            json_encode(
                $package,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE
            ) . PHP_EOL
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Admin package.json
    |--------------------------------------------------------------------------
    */

    protected function generateAdminPackageJson(): void
    {
        $package = [
            'name' => 'focus-admin',
            'version' => '1.0.0',
            'private' => true,
            'type' => 'module',

            'scripts' => [
                'dev' => 'vite',
                'build' => 'vite build',
                'watch' => 'vite',
            ],

            'dependencies' =>
                $this->adminNpmDependencies,

            'devDependencies' => [
                '@tailwindcss/vite' => '4.3.3',
                'laravel-vite-plugin' => '^3.2.0',
                'vite' => '^8.2.2',
            ],
        ];

        $path = base_path(
            'tools/admin/generated/package.json'
        );

        File::ensureDirectoryExists(
            dirname($path)
        );

        File::put(
            $path,
            json_encode(
                $package,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE
            ) . PHP_EOL
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Front package.json
    |--------------------------------------------------------------------------
    */

    protected function generateFrontPackageJson(): void
    {
        $package = [
            'name' => 'focus-front',
            'version' => '1.0.0',
            'private' => true,
            'type' => 'module',

            'scripts' => [
                'dev' => 'vite',
                'build' => 'vite build',
                'watch' => 'vite',
            ],

            'dependencies' =>
                $this->frontNpmDependencies,

            'devDependencies' => [
                '@tailwindcss/vite' => '4.3.3',
                'laravel-vite-plugin' => '^3.2.0',
                'vite' => '^8.2.2',
            ],
        ];

        $path = base_path(
            'tools/front/generated/package.json'
        );

        File::ensureDirectoryExists(
            dirname($path)
        );

        File::put(
            $path,
            json_encode(
                $package,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE
            ) . PHP_EOL
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Vite config
    |--------------------------------------------------------------------------
    */

    protected function generateAdminViteConfig(): void
    {
        $path = base_path(
            'tools/admin/generated/vite.config.js'
        );

        $content = <<<'JS'
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
            input: __ADMIN_INPUT__,

            refresh: __ADMIN_REFRESH__,

            hotFile: path.resolve(
                projectRoot,
                'public/build/admin/hot'
            ),
            buildDirectory: 'build/admin',
        }),
    ],

    server: {
        host: '0.0.0.0',
        port: __ADMIN_PORT__,
        strictPort: true,

        origin: 'http://localhost:__ADMIN_PORT__',

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
JS;

        $content = str_replace(
            '__ADMIN_PORT__',
            $this->adminPort,
            $content
        );

        $content = str_replace(
            '__ADMIN_INPUT__',
            $this->exportJsArray(
                $this->adminEntries
            ),
            $content
        );

        $content = str_replace(
            '__ADMIN_REFRESH__',
            $this->exportJsArray(
                $this->adminWatch
            ),
            $content
        );

        File::ensureDirectoryExists(
            dirname($path)
        );

        File::put(
            $path,
            $content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Front Vite config
    |--------------------------------------------------------------------------
    */

    protected function generateFrontViteConfig(): void
    {
        $path = base_path(
            'tools/front/generated/vite.config.js'
        );

        $content = <<<'JS'
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
            input: __FRONT_INPUT__,

            refresh: __FRONT_REFRESH__,

            hotFile: path.resolve(
                projectRoot,
                'public/build/front/hot'
            ),
            buildDirectory: 'build/front',
        }),
    ],

    server: {
        host: '0.0.0.0',
        port: __FRONT_PORT__,
        strictPort: true,

        origin: 'http://localhost:__FRONT_PORT__',

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
JS;

        $content = str_replace(
            '__FRONT_PORT__',
            $this->frontPort,
            $content
        );

        $content = str_replace(
            '__FRONT_INPUT__',
            $this->exportJsArray(
                $this->frontEntries
            ),
            $content
        );

        $content = str_replace(
            '__FRONT_REFRESH__',
            $this->exportJsArray(
                $this->frontWatch
            ),
            $content
        );

        File::ensureDirectoryExists(
            dirname($path)
        );

        File::put(
            $path,
            $content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Output
    |--------------------------------------------------------------------------
    */

    protected function printSummary(): void
    {
        $this->newLine();

        $this->components->info(
            'Focus CMS asset configuration generated.'
        );

        $this->newLine();

        $this->line(
            '   Laravel Vite Manager:'
        );

        $this->line(
            '   config/vite.php'
        );

        $this->newLine();

        $this->line(
            '   Vite:'
        );

        $this->line(
            '   tools/admin/generated/vite.config.js'
        );

        $this->line(
            '   tools/front/generated/vite.config.js'
        );

        $this->newLine();

        $this->line(
            '   npm:'
        );

        $this->line(
            '   tools/admin/generated/package.json'
        );

        $this->line(
            '   tools/front/generated/package.json'
        );

        $this->line(
            '   package.json'
        );

        $this->newLine();

        $this->line(
            '   Admin port: ' .
            $this->adminPort
        );

        $this->line(
            '   Front port: ' .
            $this->frontPort
        );

        $this->newLine();

        $this->line(
            '   Admin entries:'
        );

        foreach ($this->adminEntries as $entry) {
            $this->line(
                '      ' . $entry
            );
        }

        $this->newLine();

        $this->line(
            '   Front entries:'
        );

        foreach ($this->frontEntries as $entry) {
            $this->line(
                '      ' . $entry
            );
        }

        $this->newLine();

        $this->line(
            '   Admin npm packages: ' .
            count($this->adminNpmDependencies)
        );

        $this->line(
            '   Front npm packages: ' .
            count($this->frontNpmDependencies)
        );

        $this->newLine();

        $this->line(
            '   Install:'
        );

        $this->line(
            '   npm run install:all'
        );

        $this->newLine();

        $this->line(
            '   Dev:'
        );

        $this->line(
            '   npm run dev:all'
        );

        $this->newLine();

        $this->line(
            '   Build:'
        );

        $this->line(
            '   npm run build:all'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | JSON helpers
    |--------------------------------------------------------------------------
    */

    protected function readJson(
        string $path
    ): array {
        $content = File::get($path);

        try {
            $data = json_decode(
                $content,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            throw new RuntimeException(
                "Invalid JSON in {$path}: {$e->getMessage()}",
                previous: $e
            );
        }

        if (!is_array($data)) {
            throw new RuntimeException(
                "JSON root must be an object: {$path}"
            );
        }

        return $data;
    }

    protected function exportJsArray(
        array $values
    ): string {
        return json_encode(
            array_values($values),
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE
        );
    }

    protected function exportPhpArray(
        array $array,
        int $indent = 0
    ): string {
        $pad = str_repeat(
            '    ',
            $indent
        );

        $childPad = str_repeat(
            '    ',
            $indent + 1
        );

        $lines = [
            '['
        ];

        foreach ($array as $key => $value) {
            $keyString = var_export(
                $key,
                true
            );

            if (is_array($value)) {
                $nested = $this->exportPhpArray(
                    $value,
                    $indent + 1
                );

                $nestedLines = explode(
                    "\n",
                    $nested
                );

                $lines[] =
                    $childPad .
                    $keyString .
                    ' => ' .
                    $nestedLines[0];

                foreach (
                    array_slice(
                        $nestedLines,
                        1
                    ) as $line
                ) {
                    $lines[] = $line;
                }

                $lines[count($lines) - 1] .= ',';

                continue;
            }

            $lines[] =
                $childPad .
                $keyString .
                ' => ' .
                var_export(
                    $value,
                    true
                ) .
                ',';
        }

        $lines[] = $pad . ']';

        return implode(
            "\n",
            $lines
        );
    }
}