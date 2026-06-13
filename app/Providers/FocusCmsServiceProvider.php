<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class FocusCmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeFocusCmsConfigs();

        require_once app_path('Helpers/locale.php');
        require_once app_path('Helpers/MarkdownHelper.php');
        require_once app_path('Helpers/UserAgentHelper.php');
        require_once app_path('Helpers/vite.php');
    }

    protected function mergeFocusCmsConfigs(): void
    {
        if ($this->app->configurationIsCached()) {
            return;
        }

        $path = config_path('focuscms');

        if (!is_dir($path)) {
            return;
        }

        foreach (glob($path . '/*.php') as $file) {

            $configName = basename($file, '.php');

            $override = require $file;

            config()->set(
                $configName,
                array_replace_recursive(
                    config($configName, []),
                    $override
                )
            );
        }
    }
}