<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Option;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SetupThemeCommand extends Command
{
    protected $signature = 'theme:setup {theme}';
    protected $description = 'Beállítja a témát és az alapértelmezett konfigurációt';

    public function handle()
    {
        $themeName = $this->argument('theme');
        $themeServiceProviderClassName = "Themes\\{$themeName}\\Providers\\ThemeServiceProvider";

        // Javított provider regisztráció
        $this->laravel->register($themeServiceProviderClassName);

        // Konfiguráció betöltése
        $themeConfig = config('theme.theme');

        // Ellenőrizzük, hogy a config/theme.php fájl létezik-e
        if (empty($themeConfig)) {
            $this->error("A téma konfigurációs fájlja nem található: Themes/{$themeName}/config/theme.php");
            return 1; // Hibakód visszaadása
        }

        $this->info("Beállítjuk a(z) {$themeName} témát a következő konfigurációval: Themes/{$themeName}/config/theme.php");

        // Beállítások alkalmazása
        foreach ($themeConfig as $name => $value) {
            if (empty(Option::find($name))) {
                Option::updateOrCreate(
                    ['name' => $name],
                    ['value' => $value]
                );
            }
        }

        // CSS fájl másolása
        $this->copyThemeAsset(
            "Themes/{$themeName}/sources/css/theme-app.css",
            resource_path("css/theme-{$themeName}-app.css")
        );

        // JS fájl másolása
        $this->copyThemeAsset(
            "Themes/{$themeName}/sources/js/theme-app.js",
            resource_path("js/theme-{$themeName}-app.js")
        );

        $this->clearCaches();
        $this->info("A téma beállítások alkalmazva.");

        return 0; // Sikeres lefutás
    }

    protected function copyThemeAsset($source, $destination)
    {
        $sourcePath = base_path($source);

        if (file_exists($sourcePath)) {
            if (file_exists($destination)) {
                File::delete($destination);
            }

            File::copy($sourcePath, $destination);
            $this->info("Fájl másolva: {$source} → {$destination}");
        }
    }

    protected function clearCaches()
    {
        $this->info('Cache-ek törlése...');

        $commands = [
            'optimize:clear',
            'view:clear',
            'config:clear'
        ];

        foreach ($commands as $command) {
            Artisan::call($command);
            $this->info("Futtatva: {$command}");
        }

        // // Vite manifest törlése
        // $manifestPath = public_path('build/manifest.json');
        // if (File::exists($manifestPath)) {
        //     File::delete($manifestPath);
        //     $this->info('Vite manifest eltávolítva');
        // }
    }
}