<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Option;

class ModuleRemoveCommand extends Command
{
    protected $signature = 'module:remove {module}';

    protected $description = 'Deactivate module and remove initialized options';

    public function handle(): int
    {
        $module = trim($this->argument('module'));

        $this->warn("⚠ FIGYELEM!");
        $this->warn("Ez a parancs csak az adatbázis rekordokat és symlinkeket törli.");
        $this->warn("A composer által kezelt Modules/modules.json fájlt manuálisan kell módosítani.");
        $this->newLine();

        /*
        |--------------------------------------------------------------------------
        | init.php feldolgozás
        |--------------------------------------------------------------------------
        */

        $initPath = base_path("Modules/{$module}/config/init.php");

        $deleted = 0;

        if (file_exists($initPath)) {

            $config = require $initPath;

            $items = $config['initialized_options'] ?? [];

            foreach ($items as $configPath) {

                /*
                |--------------------------------------------------------------------------
                | validation_rules
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
                | normál config
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

        $this->info("Deleted module options: {$deleted}");

        /*
        |--------------------------------------------------------------------------
        | ActiveModules frissítése
        |--------------------------------------------------------------------------
        */

        $option = Option::firstWithDefaults('ActiveModules');

        $activeModules = [];

        if (!empty($option->value)) {

            if (is_array($option->value)) {

                $activeModules = $option->value;

            } elseif (is_object($option->value)) {

                $activeModules = (array) $option->value;

            } elseif (is_string($option->value)) {

                $activeModules = [$option->value];

            }

        }

        $activeModules = array_values(
            array_filter(
                $activeModules,
                fn ($m) => $m !== $module
            )
        );

        Option::updateOrCreate(
            ['name' => 'ActiveModules'],
            ['value' => $activeModules]
        );

        $this->writeActiveModulesJson($activeModules);

        $this->info("Module removed from ActiveModules.");

        /*
        |--------------------------------------------------------------------------
        | Symlink törlés
        |--------------------------------------------------------------------------
        */

        $link = public_path("modulepublic/{$module}");

        if (is_link($link) || file_exists($link)) {

            unlink($link);

            $this->info("Module symlink removed.");
        }

        /*
        |--------------------------------------------------------------------------
        | Cache clear
        |--------------------------------------------------------------------------
        */

        $this->call('optimize:clear');

        $this->info("Module {$module} removal completed.");

        return Command::SUCCESS;
    }

    /**
     * activeModules.json frissítése
     */
    private function writeActiveModulesJson(array $modules): void
    {
        sort($modules);

        file_put_contents(
            base_path('activeModules.json'),
            json_encode(
                [
                    'modules' => array_values($modules)
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            ) . PHP_EOL
        );
    }
}