<?php

namespace App\Services;

use App\Models\Option;

class ThemeSetupService
{
    public function setupTheme(string $themeName, array $config)
    {
        // Alapbeállítások mentése
        Option::updateOrCreate(
            ['key' => "ts_{$themeName}_settings"],
            ['value' => json_encode($config)]
        );

        // Sidebárok mentése
        if (!empty($config['sidebars'])) {
            foreach ($config['sidebars'] as $sidebar => $widgets) {
                Option::updateOrCreate(
                    ['key' => "ts_{$themeName}_{$sidebar}_content"],
                    ['value' => json_encode($widgets)]
                );
            }
        }
    }
}
