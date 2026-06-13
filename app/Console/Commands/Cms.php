<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Option;

class Cms extends Command
{
    protected $signature = 'cms:install
        {--force : Run without confirmation}';

    protected $description = 'Initializes CMS options based on config/init.php';

    public function handle(): int
    {
        $this->info('Starting CMS installation...');

        if (!$this->option('force')) {
            if (!$this->confirm('This will initialize CMS options. Continue?', true)) {
                $this->info('Installation cancelled.');
                return Command::SUCCESS;
            }
        }

        $items = config('init.initialized_options', []);

        if (!is_array($items) || empty($items)) {
            $this->error('No initialized_options found in config/init.php');
            return Command::FAILURE;
        }

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
                $defaults = config('validation_rules.options.default_values', []);

                foreach (array_keys($rules) as $optionKey) {

                    if (Option::where('name', $optionKey)->exists()) {
                        $skipped++;
                        continue;
                    }

                    $value = $defaults[$optionKey] ?? null;

                    Option::create([
                        'name'  => $optionKey,
                        'value' => $value
                    ]);

                    $created++;
                }

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | normál config kulcs → érték
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

        $this->newLine();
        $this->info('CMS options initialized.');
        $this->line("✔ Created: {$created}");
        $this->line("➖ Skipped (already exists): {$skipped}");

        return Command::SUCCESS;
    }
}