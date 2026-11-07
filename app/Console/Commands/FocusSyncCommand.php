<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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

    protected array $rootNpmDependencies = [];

    protected array $adminVitePlugins = [];

    protected array $frontVitePlugins = [];

    protected array $adminViteOptions = [];

    protected array $frontViteOptions = [];

    protected array $adminViteManagerOptions = [];

    protected array $frontViteManagerOptions = [];

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

        $this->collectTargetConfiguration(
            $this->frontConfig,
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

        $this->addRootNpmPackages(
            $config['orchestration']['npm'] ?? []
        );

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

        $this->collectTargetConfiguration(
            $this->adminConfig,
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

                $this->collectTargetConfiguration(
                    $admin,
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

                $this->collectTargetConfiguration(
                    $front,
                    'front'
                );
            }
        }
    }

    /**
     * Collect target-level npm packages, Vite plugins and Vite options.
     * Each target remains fully isolated from the other one.
     */
    protected function collectTargetConfiguration(
        array $config,
        string $target
    ): void {
        if (!in_array($target, ['admin', 'front'], true)) {
            throw new RuntimeException(
                "Invalid build target '{$target}'. Expected 'admin' or 'front'."
            );
        }

        $this->addNpmPackages(
            $config['plugins']['npm'] ?? [],
            $target
        );

        $plugins = $config['plugins']['vite'] ?? [];

        if (!is_array($plugins)) {
            throw new RuntimeException(
                "{$target}.plugins.vite must be an array."
            );
        }

        foreach ($plugins as $plugin) {
            if (!is_array($plugin)) {
                throw new RuntimeException(
                    "Each {$target}.plugins.vite entry must be an object."
                );
            }

            $package = trim((string) ($plugin['package'] ?? ''));
            $import = trim((string) ($plugin['import'] ?? ''));

            if ($package === '' || $import === '') {
                throw new RuntimeException(
                    "Each {$target}.plugins.vite entry requires 'package' and 'import'."
                );
            }

            $normalized = [
                'package' => $package,
                'import' => $import,
                'call' => (bool) ($plugin['call'] ?? true),
            ];

            if (array_key_exists('options', $plugin)) {
                if (!is_array($plugin['options'])) {
                    throw new RuntimeException(
                        "Plugin options for '{$package}' must be an object."
                    );
                }

                $normalized['options'] = $plugin['options'];
            }

            $collection = $target === 'admin'
                ? $this->adminVitePlugins
                : $this->frontVitePlugins;

            $existing = null;
            foreach ($collection as $registered) {
                if ($registered['import'] === $import) {
                    $existing = $registered;
                    break;
                }
            }

            if ($existing !== null && $existing !== $normalized) {
                throw new RuntimeException(
                    "Conflicting Vite plugin declaration for import '{$import}' in {$target}."
                );
            }

            if ($existing === null) {
                $collection[] = $normalized;
            }

            if ($target === 'admin') {
                $this->adminVitePlugins = $collection;
            } else {
                $this->frontVitePlugins = $collection;
            }

            unset($collection);
        }

        $managerOptions = $config['viteManager'] ?? [];

        if (!is_array($managerOptions)) {
            throw new RuntimeException(
                "{$target}.viteManager must be an object."
            );
        }

        if ($target === 'admin') {
            $this->adminViteManagerOptions = array_replace(
                $this->adminViteManagerOptions,
                $managerOptions
            );
        } else {
            $this->frontViteManagerOptions = array_replace(
                $this->frontViteManagerOptions,
                $managerOptions
            );
        }

        $viteOptions = $config['vite'] ?? [];

        if (!is_array($viteOptions)) {
            throw new RuntimeException(
                "{$target}.vite must be an object."
            );
        }

        if ($target === 'admin') {
            $this->adminViteOptions = $this->mergeViteOptions(
                $this->adminViteOptions,
                $viteOptions
            );
        } else {
            $this->frontViteOptions = $this->mergeViteOptions(
                $this->frontViteOptions,
                $viteOptions
            );
        }
    }

    /**
     * Merge object-like Vite options. Numeric/list arrays are appended;
     * associative objects are recursively merged, with later declarations
     * overriding scalar values.
     */
    protected function mergeViteOptions(
        array $base,
        array $incoming
    ): array {
        foreach ($incoming as $key => $value) {
            if (
                isset($base[$key]) &&
                is_array($base[$key]) &&
                is_array($value)
            ) {
                if (array_is_list($base[$key]) && array_is_list($value)) {
                    $base[$key] = array_values(array_merge($base[$key], $value));
                } else {
                    $base[$key] = $this->mergeViteOptions(
                        $base[$key],
                        $value
                    );
                }

                continue;
            }

            $base[$key] = $value;
        }

        return $base;
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

    /** Collect npm packages used only by the root orchestration scripts. */
    protected function addRootNpmPackages(array $packages): void
    {
        foreach ($packages as $specification) {
            if (!is_string($specification) || trim($specification) === '') {
                continue;
            }

            [$name, $version] = $this->parseNpmPackage(trim($specification));

            if (
                isset($this->rootNpmDependencies[$name]) &&
                $this->rootNpmDependencies[$name] !== $version &&
                $this->rootNpmDependencies[$name] !== '*' &&
                $version !== '*'
            ) {
                throw new RuntimeException(
                    "Root orchestration npm version conflict for '{$name}'."
                );
            }

            if (
                !isset($this->rootNpmDependencies[$name]) ||
                $this->rootNpmDependencies[$name] === '*'
            ) {
                $this->rootNpmDependencies[$name] = $version;
            }
        }

        ksort($this->rootNpmDependencies);
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
                'build_directory' => $this->adminViteManagerOptions['buildDirectory']
                    ?? 'build/admin',
                'hot_file' => public_path($this->adminViteManagerOptions['hotFile']
                    ?? 'build/admin/hot'),
            ],

            'front' => [
                'entry_points' => $this->frontEntries,
                'build_directory' => $this->frontViteManagerOptions['buildDirectory']
                    ?? 'build/front',
                'hot_file' => public_path($this->frontViteManagerOptions['hotFile']
                    ?? 'build/front/hot'),
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
        if (!isset($this->rootNpmDependencies['concurrently'])) {
            throw new RuntimeException(
                'Root focus-build.json must declare concurrently under orchestration.npm ' .
                '(for example: concurrently@^9.0.0), because dev:all uses it.'
            );
        }

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

            'devDependencies' => $this->rootNpmDependencies,
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
                'dev' => 'NODE_PATH=$(pwd)/node_modules vite',
                'build' => 'NODE_PATH=$(pwd)/node_modules vite build',
                'watch' => 'NODE_PATH=$(pwd)/node_modules vite',
            ],

            'dependencies' =>
                $this->adminNpmDependencies,

            'devDependencies' => new \stdClass(),
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
                'dev' => 'NODE_PATH=$(pwd)/node_modules vite',
                'build' => 'NODE_PATH=$(pwd)/node_modules vite build',
                'watch' => 'NODE_PATH=$(pwd)/node_modules vite',
            ],

            'dependencies' =>
                $this->frontNpmDependencies,

            'devDependencies' => new \stdClass(),
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
        $this->generateTargetViteConfig(
            'admin',
            $this->adminPort,
            $this->adminEntries,
            $this->adminWatch,
            $this->adminVitePlugins,
            $this->adminViteOptions
        );
    }

    protected function generateFrontViteConfig(): void
    {
        $this->generateTargetViteConfig(
            'front',
            $this->frontPort,
            $this->frontEntries,
            $this->frontWatch,
            $this->frontVitePlugins,
            $this->frontViteOptions
        );
    }

    /**
     * Generate an isolated Vite config for one FocusCMS target.
     * Laravel Vite integration is not injected here: it is declared in
     * focus-build.json like every other Vite plugin.
     */
    protected function generateTargetViteConfig(
        string $target,
        string $port,
        array $entries,
        array $watch,
        array $plugins,
        array $viteOptions
    ): void {
        $path = base_path(
            "tools/{$target}/generated/vite.config.js"
        );

        $imports = ["import { defineConfig } from 'vite';"];
        $pluginExpressions = [];
        $laravelPlugin = null;

        foreach ($plugins as $plugin) {
            $package = $plugin['package'];
            $import = $plugin['import'];
            $call = $plugin['call'];
            $pluginOptions = $plugin['options'] ?? [];

            if (!preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*$/', $import)) {
                throw new RuntimeException(
                    "Invalid Vite plugin import identifier '{$import}' in {$target}."
                );
            }

            $imports[] = "import {$import} from " .
                json_encode(
                    $package,
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                ) . ';';

            if ($package === 'laravel-vite-plugin') {
                $laravelPlugin = [
                    'import' => $import,
                    'options' => $pluginOptions,
                ];
                continue;
            }

            if ($call) {
                $expression = $import . '(';

                if ($pluginOptions !== []) {
                    $expression .= $this->exportJsConfigValue($pluginOptions);
                }

                $expression .= ')';
            } else {
                $expression = $import;
            }

            $pluginExpressions[] = $expression;
        }

        if ($laravelPlugin === null) {
            throw new RuntimeException(
                "The {$target} target must declare laravel-vite-plugin in plugins.vite " .
                "(package: laravel-vite-plugin, import: laravel)."
            );
        }

        $imports[] = "import path from 'node:path';";
        $imports[] = "import { fileURLToPath } from 'node:url';";

        // FocusCMS defaults; target focus-build.json declarations may override them.
        $defaults = [
            'root' => '__FOCUS_PROJECT_ROOT__',
            'cacheDir' => "__FOCUS_PROJECT_ROOT__/tools/{$target}/generated/.vite",
            'server' => [
                'host' => '0.0.0.0',
                'port' => (int) $port,
                'strictPort' => true,
                'origin' => "http://localhost:{$port}",
                'cors' => [
                    'origin' => [
                        'http://localhost:90',
                        'http://0.0.0.0:90',
                    ],
                ],
                'hmr' => [
                    'host' => 'localhost',
                ],
                'fs' => [
                    'strict' => false,
                ],
                'watch' => [
                    'ignored' => [
                        '**/public/page-cache/**',
                    ],
                ],
            ],
            'build' => [
                'outDir' => "__FOCUS_PROJECT_ROOT__/public/build/{$target}",
                'assetsDir' => 'assets',
                'manifest' => 'manifest.json',
                'emptyOutDir' => false,
            ],
        ];

        $options = $this->mergeViteOptions(
            $defaults,
            $viteOptions
        );

        // The Vite config's plugins list is generated only from plugins.vite.
        unset($options['plugins']);

        $laravelOptions = $laravelPlugin['options'];
        $managerOptions = $target === 'admin'
            ? $this->adminViteManagerOptions
            : $this->frontViteManagerOptions;

        $managerBuildDirectory = $managerOptions['buildDirectory']
            ?? "build/{$target}";
        $managerHotFile = $managerOptions['hotFile']
            ?? "build/{$target}/hot";

        $laravelOptions = array_merge(
            $laravelOptions,
            [
                'input' => $entries,
                'refresh' => $watch,
                'hotFile' => "__FOCUS_PROJECT_ROOT__/public/{$managerHotFile}",
                'buildDirectory' => $managerBuildDirectory,
            ]
        );

        $pluginExpressions[] = $laravelPlugin['import'] . '(' .
            $this->exportJsConfigValue($laravelOptions) .
            ')';

        $content = implode("\n", $imports) . "\n\n";
        $content .= "const __filename = fileURLToPath(import.meta.url);\n";
        $content .= "const __dirname = path.dirname(__filename);\n";
        $content .= "const projectRoot = path.resolve(__dirname, '../../..');\n";
        $content .= "const isolatedNodeModules = path.resolve(__dirname, 'node_modules');\n";
        $content .= "const isolatedTargetName = " .
            json_encode($target, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) .
            ";\n";

        $content .= <<<'JS'
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

JS;
        $content .= "\n";
        $content .= "export default defineConfig({\n";
        $optionLines = explode(
            "\n",
            $this->exportJsConfigObject($options, 0)
        );
        array_shift($optionLines); // opening brace
        array_pop($optionLines);    // closing brace
        $content .= implode("\n", $optionLines) . "\n";
        $content .= "    plugins: [\n";
        $content .= "        focusIsolatedPackageResolver,\n";

        foreach ($pluginExpressions as $expression) {
            $content .= '        ' . $expression . ",\n";
        }

        $content .= "    ],\n";
        $content .= "});\n";

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
    }

    /** Export a JSON-compatible value to a JS expression, resolving project-root tokens. */
    protected function exportJsConfigValue(
        mixed $value,
        int $indent = 0
    ): string {
        if (is_array($value)) {
            return $this->exportJsConfigObject($value, $indent);
        }

        if (is_string($value)) {
            if ($value === '__FOCUS_PROJECT_ROOT__') {
                return 'projectRoot';
            }

            $prefix = '__FOCUS_PROJECT_ROOT__/';
            if (str_starts_with($value, $prefix)) {
                $relative = substr($value, strlen($prefix));

                return 'path.resolve(projectRoot, ' .
                    json_encode($relative, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) .
                    ')';
            }
        }

        return json_encode(
            $value,
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE |
            JSON_THROW_ON_ERROR
        );
    }

    /** Export an array/object as a JS object/array with stable indentation. */
    protected function exportJsConfigObject(
        array $value,
        int $indent = 0
    ): string {
        $isList = array_is_list($value);
        $open = $isList ? '[' : '{';
        $close = $isList ? ']' : '}';
        $pad = str_repeat('    ', $indent);
        $childPad = str_repeat('    ', $indent + 1);
        $lines = [$open];

        foreach ($value as $key => $item) {
            $keyText = $isList
                ? ''
                : json_encode((string) $key, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ': ';

            $lines[] = $childPad . $keyText .
                $this->exportJsConfigValue($item, $indent + 1) . ',';
        }

        if (count($lines) === 1) {
            return $open . $close;
        }

        $lines[] = $pad . $close;

        return implode("\n", $lines);
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

        $this->line(
            '   Admin Vite plugins: ' .
            count($this->adminVitePlugins)
        );

        $this->line(
            '   Front Vite plugins: ' .
            count($this->frontVitePlugins)
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