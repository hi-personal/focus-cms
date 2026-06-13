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

    $this->app->singleton(CustomMarkdownService::class, fn () =>
        new CustomMarkdownService()
    );

    $this->app->singleton(ShortcodeRegistry::class);


    /*
    |--------------------------------------------------------------------------
    | Theme és Module providerek késleltetett regisztrálása
    |--------------------------------------------------------------------------
    */

    $this->app->booted(function () {

        try {

            if (!$this->app->bound('db')) {
                return;
            }

            if (!Schema::hasTable('options')) {
                return;
            }

            $this->registerThemeProvider();

            $this->registerModuleProviders();

        } catch (\Throwable $e) {

            // Composer bootstrap alatt vagy DB nincs még kész
            // Biztonságosan ignoráljuk

        }

    });
}


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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


    /*
    |--------------------------------------------------------------------------
    | THEME PROVIDER REGISZTRÁLÁSA
    |--------------------------------------------------------------------------
    */

    protected function registerThemeProvider(): void
    {
        if (!Schema::hasTable('options')) {
            return;
        }

        $themeOption = Option::firstWithDefaults('currentThemeName');

        $currentThemeName = $themeOption?->value ?? 'FocusDefaultTheme';

        if (!is_string($currentThemeName) || trim($currentThemeName) === '') {
            return;
        }

        $providerPath =
            base_path("Themes/{$currentThemeName}/Providers/ThemeServiceProvider.php");

        $providerClass =
            "Themes\\{$currentThemeName}\\Providers\\ThemeServiceProvider";

        if (
            file_exists($providerPath) &&
            class_exists($providerClass)
        ) {
            $this->app->register($providerClass);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | MODULE PROVIDEREK REGISZTRÁLÁSA
    |--------------------------------------------------------------------------
    */

    protected function registerModuleProviders(): void
    {
        if (!Schema::hasTable('options')) {
            return;
        }

        $modulesOption = Option::firstWithDefaults('ActiveModules');

        $activeModules = (array) ($modulesOption->value ?? []);

        foreach ($activeModules as $moduleName) {

            if (!is_string($moduleName) || trim($moduleName) === '') {
                continue;
            }

            $providerClass =
                "Modules\\{$moduleName}\\Providers\\ModuleServiceProvider";

            if (class_exists($providerClass)) {

                $this->app->register($providerClass);

            }
        }
    }
}
