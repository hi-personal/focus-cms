<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModuleMakeMigrationCommand extends Command
{
    protected $signature =
        'module:make-migration
        {module : Module name}
        {name : Migration name}
        {--create= : Table name to create}
        {--table= : Table name to modify}';

    protected $description =
        'Create a new migration inside a module';


    public function handle(): int
    {
        $module = trim($this->argument('module'));
        $name   = trim($this->argument('name'));

        $modulePath =
            base_path("Modules/{$module}");

        if (!File::isDirectory($modulePath)) {

            $this->error("Module not found: {$module}");

            return self::FAILURE;
        }

        $migrationPath =
            "Modules/{$module}/database/migrations";

        if (!File::isDirectory(base_path($migrationPath))) {

            File::makeDirectory(
                base_path($migrationPath),
                0755,
                true
            );
        }

        $params = [
            'name' => $name,
            '--path' => $migrationPath,
        ];

        if ($this->option('create')) {

            $params['--create'] = $this->option('create');

        } elseif ($this->option('table')) {

            $params['--table'] = $this->option('table');
        }

        $this->info("Creating migration for module: {$module}");

        return $this->call('make:migration', $params);
    }
}
