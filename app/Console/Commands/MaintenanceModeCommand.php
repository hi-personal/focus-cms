<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MaintenanceModeCommand extends Command
{
    protected $signature = 'maintenance {action}';
    protected $description = 'Manage maintenance mode';

    public function handle()
    {
        $action = $this->argument('action');

        if ($action === 'on') {
            File::put(storage_path('framework/.maintenance'), '');
            $this->info('Maintenance mode is ON');
        } elseif ($action === 'off') {
            File::delete(storage_path('framework/.maintenance'));
            $this->info('Maintenance mode is OFF');
        } else {
            $this->error('Invalid action. Use "on" or "off"');
        }
    }
}