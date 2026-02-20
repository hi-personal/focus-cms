<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Option;

class Cms extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cms:install
        {--force : Run without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Initializes CMS options based on validation_rules config';

    public function handle(): int
    {
        $this->info('Starting CMS installation...');

        if (! $this->option('force')) {
            if (! $this->confirm('This will initialize CMS options. Continue?', true)) {
                $this->info('Installation cancelled.');
                return Command::SUCCESS;
            }
        }

        $settings = config('validation_rules.options.website_settings');

        if (! is_array($settings) || empty($settings)) {
            $this->error('No website_settings found in config.');
            return Command::FAILURE;
        }

        $created = 0;
        $skipped = 0;

        foreach (array_keys($settings) as $key) {
            $option = Option::firstOrCreate(
                ['name' => $key],
                ['value' => null]
            );

            if ($option->wasRecentlyCreated) {
                $created++;
            } else {
                $skipped++;
            }
        }

        $this->newLine();
        $this->info('CMS options initialized.');
        $this->line("✔ Created: {$created}");
        $this->line("➖ Skipped (already exists): {$skipped}");

        return Command::SUCCESS;
    }
}
