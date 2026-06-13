<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Option;

class SetThemeCommand extends Command
{
    protected $signature = 'theme:set {theme}';

    protected $description = 'Beállítja az aktuális témát, létrehozza a symlinkeket és inicializálja a theme opciókat';

    public function handle()
    {
        $themeName = trim($this->argument('theme'));

        /*
        |--------------------------------------------------------------------------
        | 1. Validáció
        |--------------------------------------------------------------------------
        */

        if ($themeName === '') {
            $this->error('A téma neve nem lehet üres!');
            return Command::FAILURE;
        }

        $themePublicPath = base_path("Themes/{$themeName}/public");

        if (!is_dir($themePublicPath)) {
            $this->error("A megadott téma nem létezik vagy nincs public mappája: {$themeName}");
            return Command::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Aktuális téma mentése
        |--------------------------------------------------------------------------
        */

        Option::updateOrCreate(
            ['name' => 'currentThemeName'],
            ['value' => $themeName]
        );

        $this->info("Aktív téma beállítva: {$themeName}");

        /*
        |--------------------------------------------------------------------------
        | 3. THEME PUBLIC SYMLINK
        |--------------------------------------------------------------------------
        */

        $themeLinkPath = public_path('themepublic');

        $this->createRelativeSymlink(
            $themePublicPath,
            $themeLinkPath
        );

        $this->info("Theme symlink létrehozva: {$themeLinkPath}");

        /*
        |--------------------------------------------------------------------------
        | 4. STORAGE SYMLINK
        |--------------------------------------------------------------------------
        */

        $storagePublicPath = base_path('storage/app/public');
        $storageLinkPath   = public_path('storage');

        $this->createRelativeSymlink(
            $storagePublicPath,
            $storageLinkPath
        );

        $this->info("Storage symlink létrehozva: {$storageLinkPath}");

        /*
        |--------------------------------------------------------------------------
        | 5. currentTheme.json frissítése
        |--------------------------------------------------------------------------
        */

        $config = [
            'theme' => $themeName,
            'paths' => [
                'css'   => "Themes/{$themeName}/public/css",
                'js'    => "Themes/{$themeName}/public/js",
                'views' => "Themes/{$themeName}/resources/views",
            ],
        ];

        file_put_contents(
            base_path('currentTheme.json'),
            json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->info("currentTheme.json frissítve.");

        /*
        |--------------------------------------------------------------------------
        | 6. Theme opciók inicializálása
        |--------------------------------------------------------------------------
        */

        $this->initializeThemeOptions($themeName);

        $this->info("A(z) '{$themeName}' téma sikeresen beállítva.");

        return Command::SUCCESS;
    }

    /**
     * Theme opciók inicializálása
     */
    private function initializeThemeOptions(string $themeName): void
    {
        $initConfigPath = base_path("Themes/{$themeName}/config/init.php");

        if (!file_exists($initConfigPath)) {
            $this->warn("Theme init config not found.");
            return;
        }

        $initConfig = require $initConfigPath;

        $items = $initConfig['initialized_options'] ?? [];

        $created = 0;
        $skipped = 0;

        foreach ($items as $configPath) {

            /*
            |--------------------------------------------------------------------------
            | validation_rules schema
            |--------------------------------------------------------------------------
            */

            if (str_contains($configPath, 'validation_rules.options.')) {

                $rules = config($configPath, []);

                foreach (array_keys($rules) as $optionKey) {

                    if (Option::where('name', $optionKey)->exists()) {
                        $skipped++;
                        continue;
                    }

                    Option::create([
                        'name'  => $optionKey,
                        'value' => null
                    ]);

                    $created++;
                }

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | normal config values
            |--------------------------------------------------------------------------
            */

            $values = config($configPath, []);

            if (!is_array($values)) {
                continue;
            }

            foreach ($values as $key => $value) {

                if (Option::where('name', $key)->exists()) {
                    $skipped++;
                    continue;
                }

                Option::create([
                    'name'  => $key,
                    'value' => $value
                ]);

                $created++;
            }
        }

        $this->info("Theme options initialized. Created: {$created}, Skipped: {$skipped}");
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIVE SYMLINK HELPERS
    |--------------------------------------------------------------------------
    */

    private function createRelativeSymlink(string $target, string $link): void
    {
        if (is_link($link) || file_exists($link)) {
            unlink($link);
        }

        $relativeTarget = $this->relativePath(
            dirname($link),
            $target
        );

        symlink($relativeTarget, $link);
    }

    /**
     * Abszolút útvonal → relatív útvonal számítása
     */
    private function relativePath(string $from, string $to): string
    {
        $from = str_replace('\\', '/', realpath($from));
        $to   = str_replace('\\', '/', realpath($to));

        $fromParts = explode('/', rtrim($from, '/'));
        $toParts   = explode('/', rtrim($to, '/'));

        while (
            count($fromParts) &&
            count($toParts) &&
            $fromParts[0] === $toParts[0]
        ) {
            array_shift($fromParts);
            array_shift($toParts);
        }

        return str_repeat('../', count($fromParts)) . implode('/', $toParts);
    }
}