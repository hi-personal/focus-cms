<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Option;

class ThemeRemoveCommand extends Command
{
    protected $signature = 'theme:remove {theme}';

    protected $description = 'Deactivate theme and remove initialized options';

    public function handle(): int
    {
        $theme = trim($this->argument('theme'));

        $this->warn("⚠ FIGYELEM!");
        $this->warn("Ez a parancs csak az adatbázis rekordokat és symlinkeket törli.");
        $this->warn("A composer által kezelt Themes/themes.json fájlt manuálisan kell módosítani.");
        $this->newLine();

        $initPath = base_path("Themes/{$theme}/config/init.php");

        $deleted = 0;

        if (!file_exists($initPath)) {

            $this->warn("No init.php found for theme.");

        } else {

            $config = require $initPath;

            $items = $config['initialized_options'] ?? [];

            foreach ($items as $configPath) {

                /*
                |--------------------------------------------------------------------------
                | validation_rules schema
                |--------------------------------------------------------------------------
                */

                if (str_contains($configPath, 'validation_rules.options.')) {

                    $rules = config($configPath, []);

                    $keys = array_keys($rules);

                    $deleted += Option::whereIn('name', $keys)->delete();

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | normál config kulcsok
                |--------------------------------------------------------------------------
                */

                $values = config($configPath, []);

                if (!is_array($values)) {
                    continue;
                }

                $keys = array_keys($values);

                $deleted += Option::whereIn('name', $keys)->delete();
            }
        }

        $this->info("Deleted theme options: {$deleted}");

        /*
        |--------------------------------------------------------------------------
        | Symlink törlés
        |--------------------------------------------------------------------------
        */

        $link = public_path('themepublic');

        if (is_link($link) || file_exists($link)) {

            unlink($link);

            $this->info("Theme symlink removed.");
        }

        /*
        |--------------------------------------------------------------------------
        | currentThemeName reset
        |--------------------------------------------------------------------------
        */

        Option::where('name', 'currentThemeName')->delete();

        $this->info("Theme removed from options.");

        $this->call('optimize:clear');

        $this->info("Theme {$theme} removal completed.");

        return Command::SUCCESS;
    }
}