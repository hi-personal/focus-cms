<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Option;

class OptimizeDb extends Command
{
    protected $signature = 'cms:optimize-db
        {--dry-run : Only show what would be deleted}
        {--D|detailed : Show detailed output}';

    protected $description = 'Clean unused options from database based on CMS, theme and module init configs';

    public function handle()
    {
        $this->info("Scanning options table...");

        $protected = [];

        /*
        |--------------------------------------------------------------------------
        | Helper function
        |--------------------------------------------------------------------------
        */

        $collectKeys = function ($items) use (&$protected) {

            foreach ($items as $configPath) {

                if (str_contains($configPath, 'validation_rules.options.')) {

                    $rules = config($configPath, []);

                    $protected = array_merge(
                        $protected,
                        array_keys($rules)
                    );

                    continue;
                }

                $values = config($configPath, []);

                if (!is_array($values)) {
                    continue;
                }

                $protected = array_merge(
                    $protected,
                    array_keys($values)
                );
            }
        };

        /*
        |--------------------------------------------------------------------------
        | 1. CMS init
        |--------------------------------------------------------------------------
        */

        $collectKeys(
            config('init.initialized_options', [])
        );

        /*
        |--------------------------------------------------------------------------
        | 2. Theme init
        |--------------------------------------------------------------------------
        */

        $theme = Option::where('name', 'currentThemeName')->value('value');

        if ($theme) {

            $path = base_path("Themes/{$theme}/config/init.php");

            if (file_exists($path)) {

                $config = require $path;

                $collectKeys(
                    $config['initialized_options'] ?? []
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Module init
        |--------------------------------------------------------------------------
        */

        $modules = Option::where('name', 'ActiveModules')->value('value');

        if (is_array($modules)) {

            foreach ($modules as $module) {

                $path = base_path("Modules/{$module}/config/init.php");

                if (!file_exists($path)) {
                    continue;
                }

                $config = require $path;

                $collectKeys(
                    $config['initialized_options'] ?? []
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 4. System options
        |--------------------------------------------------------------------------
        */

        $protected[] = 'ActiveModules';
        $protected[] = 'currentThemeName';

        $protected = array_unique($protected);

        /*
        |--------------------------------------------------------------------------
        | 5. Unused options keresése
        |--------------------------------------------------------------------------
        */

        $query = Option::whereNotIn('name', $protected);

        $toDelete = $query->get();

        if ($toDelete->isEmpty()) {

            $this->info("No unused options found.");

            return Command::SUCCESS;
        }

        $this->warn("Unused options detected:");

        $this->table(
            ['Option name'],
            $toDelete->map(fn ($o) => [$o->name])
        );

        $this->info($toDelete->count()." records would be deleted.");

        /*
        |--------------------------------------------------------------------------
        | DRY RUN
        |--------------------------------------------------------------------------
        */

        if ($this->option('dry-run')) {

            return Command::SUCCESS;
        }

        if (!$this->confirm("Delete these unused options?", false)) {

            $this->info("Operation cancelled.");

            return Command::SUCCESS;
        }

        $deleted = $query->delete();

        $this->info("Deleted {$deleted} unused options.");

        /*
        |--------------------------------------------------------------------------
        | Detailed
        |--------------------------------------------------------------------------
        */

        if ($this->option('detailed')) {

            $this->line('');
            $this->line('Protected option keys:');

            foreach ($protected as $key) {

                $this->line(" - {$key}");
            }
        }

        return Command::SUCCESS;
    }
}