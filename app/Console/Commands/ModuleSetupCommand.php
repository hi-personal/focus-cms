<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\Option;
use Throwable;

class ModuleSetupCommand extends Command
{
    protected $signature = 'module:setup {module}';

    protected $description = 'Setup and activate a module, and create public symlink';

    public function handle(): int
    {
        $module = trim($this->argument('module'));

        /*
        |--------------------------------------------------------------------------
        | 1. Validáció
        |--------------------------------------------------------------------------
        */

        if ($module === '') {
            $this->error('Module name cannot be empty.');
            return Command::FAILURE;
        }

        $modulePath = base_path("Modules/{$module}");

        if (!is_dir($modulePath)) {
            $this->error("Module not found: {$module}");
            return Command::FAILURE;
        }

        $this->info("Setting up module: {$module}");

        /*
        |--------------------------------------------------------------------------
        | 2. ActiveModules Option kezelése
        |--------------------------------------------------------------------------
        */

        $option = Option::firstWithDefaults('ActiveModules');

        $activeModules = [];

        if (!empty($option->value)) {

            if (is_array($option->value)) {
                $activeModules = $option->value;

            } elseif (is_object($option->value)) {
                $activeModules = (array) $option->value;

            } elseif (is_string($option->value)) {
                $activeModules = [$option->value];
            }
        }

        if (!in_array($module, $activeModules, true)) {

            $activeModules[] = $module;

            Option::updateOrCreate(
                ['name' => 'ActiveModules'],
                ['value' => array_values(array_unique($activeModules))]
            );

            $this->info("Module registered in ActiveModules.");

        } else {

            $this->info("Module already registered.");

        }

        /*
        |--------------------------------------------------------------------------
        | 3. MODULE PUBLIC SYMLINK
        |--------------------------------------------------------------------------
        */

        $modulePublicPath = base_path("Modules/{$module}/public");

        if (is_dir($modulePublicPath)) {

            $linkBasePath = public_path('modulepublic');

            if (!is_dir($linkBasePath)) {
                mkdir($linkBasePath, 0755, true);
            }

            $linkPath = public_path("modulepublic/{$module}");

            $this->createRelativeSymlink(
                $modulePublicPath,
                $linkPath
            );

            $this->info("Module public symlink created: {$linkPath}");

        } else {

            $this->warn("Module has no public directory.");
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Modul migrációk (FIXED)
        |--------------------------------------------------------------------------
        */

        $migrationPath = base_path("Modules/{$module}/database/migrations");

        if (is_dir($migrationPath)) {

            $files = glob($migrationPath . '/*.php');

            if (!empty($files)) {

                $this->info("Running module migrations...");

                try {

                    $this->call('migrate', [
                        '--path' => $migrationPath,
                        '--realpath' => true,
                        '--force' => true
                    ]);

                } catch (Throwable $e) {

                    // ha már létezik a tábla, nem kritikus hiba
                    if (str_contains($e->getMessage(), 'already exists')) {

                        $this->warn("Migration skipped (already exists).");

                    } else {

                        $this->error("Migration error: " . $e->getMessage());
                        return Command::FAILURE;
                    }
                }

            } else {

                $this->info("No migrations found.");

            }

        }

        /*
        |--------------------------------------------------------------------------
        | 5. Cache clear
        |--------------------------------------------------------------------------
        */

        $this->call('optimize:clear');

        $this->info("Module {$module} setup completed.");

        return Command::SUCCESS;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIVE SYMLINK HELPERS
    |--------------------------------------------------------------------------
    */

    private function createRelativeSymlink(string $target, string $link): void
    {
        if (is_link($link) || file_exists($link)) {
            @unlink($link);
        }

        $relativeTarget = $this->relativePath(
            dirname($link),
            $target
        );

        symlink($relativeTarget, $link);
    }

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