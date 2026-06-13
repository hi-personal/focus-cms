<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MaintenanceModeCommand extends Command
{
    protected $signature = 'cms:maintenance {action : on|off}
                            {--retry=60 : The number of seconds after which the request may be retried}
                            {--secret= : The secret phrase that may be used to bypass maintenance mode}
                            {--refresh=15 : The number of seconds to keep the maintenance mode bypass cookie}';

    protected $description = 'Enable or disable CMS maintenance mode (Laravel built-in system)';

    public function handle(): int
    {
        $action = $this->argument('action');

        if ($action === 'on') {
            // Összegyűjtjük az opciókat
            $options = [];

            if ($this->option('retry')) {
                $options['--retry'] = $this->option('retry');
            }

            if ($this->option('secret')) {
                $options['--secret'] = $this->option('secret');
            } else {
                // Alapértelmezett secret token generálása
                $options['--secret'] = 'cms-' . substr(md5(config('app.key')), 0, 8);
            }

            if ($this->option('refresh')) {
                $options['--refresh'] = $this->option('refresh');
            }

            // Laravel gyári maintenance mód bekapcsolása
            $this->call('down', $options);

            $this->info('✓ Maintenance mode is ON (Laravel built-in system)');
            $this->info('  Retry after: ' . $options['--retry'] . ' seconds');
            $this->info('  Bypass secret: ' . $options['--secret']);
            $this->info('  Access website: /' . $options['--secret']);

            return Command::SUCCESS;
        }

        if ($action === 'off') {
            // Laravel gyári maintenance mód kikapcsolása
            $this->call('up');

            $this->info('✓ Maintenance mode is OFF (Laravel built-in system)');
            $this->info('  Website is now accessible to everyone');

            return Command::SUCCESS;
        }

        $this->error('✗ Invalid action. Use "on" or "off".');
        return Command::FAILURE;
    }
}