<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Az alkalmazás egyedi Artisan parancsai.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\SetupThemeCommand::class,
        \App\Console\Commands\RemoveThemeCommand::class,
        \App\Console\Commands\SetThemeCommand::class,
        \App\Console\Commands\ModuleSetupCommand::class,
        \App\Console\Commands\ModuleMigrateCommand::class,
        \App\Console\Commands\ModuleMigrationCommand::class,
        \App\Console\Commands\ModuleMakeMigrationCommand::class,
    ];

    /**
     * Az Artisan parancsok időzítése.
     */
    protected function schedule(Schedule $schedule)
    {
        // Példa: $schedule->command('inspire')->hourly();
    }

    /**
     * Az alkalmazás parancsainak regisztrálása.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
