<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Option;

class RemoveThemeCommand extends Command
{
    protected $signature = 'theme:remove {theme}';
    protected $description = 'Törli a témához tartozó beállításokat';

    public function handle()
    {
        $themeName = $this->argument('theme');

        if (!$themeName) {
            $this->info('Nem található téma név.');
            return;
        }

        Option::where('name', 'LIKE', "ts_{$themeName}_%")->delete();

        $this->info("A(z) {$themeName} téma beállításai törölve lettek.");
    }
}
