<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;

use App\Services\CustomMarkdownService;
use App\Services\ShortcodeRegistry;
use App\Models\Option;

use Themes\FocusDefaultTheme\Classes\Layouts\Components\PublicDefault;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Core services
        |--------------------------------------------------------------------------
        */

        $this->app->singleton(CustomMarkdownService::class, function () {
            return new CustomMarkdownService();
        });

        $this->app->singleton(ShortcodeRegistry::class);
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Options table ellenőrzése
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('options')) {

            /*
            |--------------------------------------------------------------------------
            | THEME ServiceProvider betöltése
            |--------------------------------------------------------------------------
            */

            $themeOption = Option::firstWithDefaults('currentThemeName');

            $currentThemeName = $themeOption?->value ?? 'FocusDefaultTheme';

            $themeProviderPath =
                base_path("Themes/{$currentThemeName}/Providers/ThemeServiceProvider.php");

            $themeProviderClass =
                "Themes\\{$currentThemeName}\\Providers\\ThemeServiceProvider";

            if (
                is_string($currentThemeName) &&
                file_exists($themeProviderPath) &&
                class_exists($themeProviderClass)
            ) {
                $this->app->register($themeProviderClass);
            }


            /*
            |--------------------------------------------------------------------------
            | MODULE ServiceProviderek betöltése ActiveModules alapján
            |--------------------------------------------------------------------------
            */

            $modulesOption = Option::firstWithDefaults('ActiveModules');

            $activeModules = [];

            if (!empty($modulesOption->value)) {

                if (is_array($modulesOption->value)) {

                    $activeModules = $modulesOption->value;

                } elseif (is_object($modulesOption->value)) {

                    $activeModules = (array) $modulesOption->value;

                } elseif (is_string($modulesOption->value)) {

                    $activeModules = [$modulesOption->value];
                }
            }

            foreach ($activeModules as $moduleName) {

                if (!is_string($moduleName) || trim($moduleName) === '') {
                    continue;
                }

                $providerPath =
                    base_path("Modules/{$moduleName}/Providers/ModuleServiceProvider.php");

                $providerClass =
                    "Modules\\{$moduleName}\\Providers\\ModuleServiceProvider";

                if (
                    file_exists($providerPath) &&
                    class_exists($providerClass)
                ) {
                    $this->app->register($providerClass);
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Local environment overrides
        |--------------------------------------------------------------------------
        */

        if (app()->environment('local')) {

            $this->app->bind(
                \App\Rules\RecaptchaRule::class,
                function () {

                    return new class implements \Illuminate\Contracts\Validation\Rule {

                        public function passes($attribute, $value): bool
                        {
                            return true;
                        }

                        public function message(): string
                        {
                            return '';
                        }

                    };
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Blade components
        |--------------------------------------------------------------------------
        */

        Blade::component(PublicDefault::class, 'public-default');
    }
}
