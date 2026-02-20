<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ModuleMigrationCommand extends Command
{
    protected $signature =
        'module:migration
        {module : Module name}
        {action : install|uninstall|migrate|rollback|fresh|status}';

    protected $description =
        'Manage module migrations using isolated migration tables (Laravel 12 compatible)';


    public function handle(): int
    {
        $module = trim($this->argument('module'));
        $action = trim($this->argument('action'));

        $migrationPath =
            base_path("Modules/{$module}/database/migrations");

        if (!File::isDirectory($migrationPath)) {

            $this->error("Module migrations not found: {$module}");

            return self::FAILURE;
        }

        $migrationTable =
            'migrations_' . strtolower($module);

        // Átállítjuk a Laravel migration table-t runtime
        config(['database.migrations' => $migrationTable]);

        return match ($action) {

            'install' =>
                $this->install($module, $migrationTable, $migrationPath),

            'uninstall' =>
                $this->uninstall($migrationTable, $migrationPath),

            'migrate' =>
                $this->migrate($migrationTable, $migrationPath),

            'rollback' =>
                $this->rollback($migrationTable, $migrationPath),

            'fresh' =>
                $this->fresh($migrationTable, $migrationPath),

            'status' =>
                $this->status($migrationTable, $migrationPath),

            default =>
                $this->invalidAction($action),
        };
    }


    /*
    |--------------------------------------------------------------------------
    | INSTALL
    |--------------------------------------------------------------------------
    */

    protected function install(
        string $module,
        string $table,
        string $path
    ): int {

        $this->info("Installing module migrations: {$module}");

        $this->createMigrationTable($table);

        return $this->migrate($table, $path);
    }


    /*
    |--------------------------------------------------------------------------
    | UNINSTALL
    |--------------------------------------------------------------------------
    */

    protected function uninstall(
        string $table,
        string $path
    ): int {

        $this->info("Rolling back module migrations...");

        $this->rollbackAll($table, $path);

        if (Schema::hasTable($table)) {

            Schema::drop($table);

            $this->info("Migration table dropped: {$table}");
        }

        return self::SUCCESS;
    }


    /*
    |--------------------------------------------------------------------------
    | MIGRATE
    |--------------------------------------------------------------------------
    */

    protected function migrate(
        string $table,
        string $path
    ): int {

        config(['database.migrations' => $table]);

        $this->createMigrationTable($table);

        return $this->call('migrate', [
            '--path' => $this->relativePath($path),
            '--force' => true,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    protected function rollback(
        string $table,
        string $path
    ): int {

        config(['database.migrations' => $table]);

        return $this->call('migrate:rollback', [
            '--path' => $this->relativePath($path),
            '--force' => true,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ROLLBACK ALL
    |--------------------------------------------------------------------------
    */

    protected function rollbackAll(
        string $table,
        string $path
    ): void {

        config(['database.migrations' => $table]);

        while (true) {

            $result = $this->call('migrate:rollback', [
                '--path' => $this->relativePath($path),
                '--force' => true,
            ]);

            if ($result !== self::SUCCESS) {
                break;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | FRESH
    |--------------------------------------------------------------------------
    */

    protected function fresh(
        string $table,
        string $path
    ): int {

        config(['database.migrations' => $table]);

        $this->rollbackAll($table, $path);

        return $this->migrate($table, $path);
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */

    protected function status(
        string $table,
        string $path
    ): int {

        config(['database.migrations' => $table]);

        return $this->call('migrate:status', [
            '--path' => $this->relativePath($path),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE MIGRATION TABLE
    |--------------------------------------------------------------------------
    */

    protected function createMigrationTable(string $table): void
    {
        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function ($table) {

            $table->id();

            $table->string('migration');

            $table->integer('batch');
        });

        $this->info("Migration table created: {$table}");
    }


    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    protected function relativePath(string $path): string
    {
        return str_replace(base_path() . '/', '', $path);
    }


    protected function invalidAction(string $action): int
    {
        $this->error("Invalid action: {$action}");

        $this->line("Allowed actions:");

        $this->line(" install");
        $this->line(" uninstall");
        $this->line(" migrate");
        $this->line(" rollback");
        $this->line(" fresh");
        $this->line(" status");

        return self::FAILURE;
    }
}
