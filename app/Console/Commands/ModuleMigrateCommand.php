<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModuleMigrateCommand extends Command
{
    protected $signature =
        'module:migrate
        {module : Module name}
        {--rollback : Rollback instead of migrate}
        {--reset : Reset all migrations}
        {--fresh : Fresh migration}
        {--status : Show migration status}
        {--force : Force run}';

    protected $description =
        'Run migrations for a specific module';


    public function handle(): int
    {
        $module = $this->argument('module');

        $path = base_path("Modules/{$module}/database/migrations");

        if (!File::isDirectory($path)) {

            $this->error("Module not found or has no migrations: {$module}");

            return self::FAILURE;
        }

        $this->info("Running migration command for module: {$module}");

        if ($this->option('rollback')) {

            return $this->rollback($path);

        }

        if ($this->option('reset')) {

            return $this->reset($path);

        }

        if ($this->option('fresh')) {

            return $this->fresh($path);

        }

        if ($this->option('status')) {

            return $this->status($path);

        }

        return $this->migrate($path);
    }


    protected function migrate(string $path): int
    {
        return $this->call('migrate', [
            '--path' => $this->relativePath($path),
            '--force' => $this->option('force'),
        ]);
    }


    protected function rollback(string $path): int
    {
        return $this->call('migrate:rollback', [
            '--path' => $this->relativePath($path),
            '--force' => $this->option('force'),
        ]);
    }


    protected function reset(string $path): int
    {
        return $this->call('migrate:reset', [
            '--path' => $this->relativePath($path),
            '--force' => $this->option('force'),
        ]);
    }


    protected function fresh(string $path): int
    {
        $this->reset($path);

        return $this->migrate($path);
    }


    protected function status(string $path): int
    {
        return $this->call('migrate:status', [
            '--path' => $this->relativePath($path),
        ]);
    }


    protected function relativePath(string $path): string
    {
        return str_replace(base_path() . '/', '', $path);
    }
}
