<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Option;

class SetThemeCommand extends Command
{
    protected $signature = 'theme:set {theme}';
    protected $description = 'Beállítja az aktuális témát és létrehozza a szükséges symlinkeket';

    public function handle()
    {
        $themeName = trim($this->argument('theme'));

        // 1️⃣ Validáció
        if ($themeName === '') {
            $this->error('A téma neve nem lehet üres!');
            return Command::FAILURE;
        }

        $themePublicPath = base_path("Themes/{$themeName}/public");

        if (!is_dir($themePublicPath)) {
            $this->error("A megadott téma nem létezik vagy nincs public mappája: {$themeName}");
            return Command::FAILURE;
        }

        // 2️⃣ Theme beállítása DB-ben
        Option::updateOrCreate(
            ['name' => 'currentThemeName'],
            ['value' => $themeName]
        );

        // 3️⃣ THEME PUBLIC SYMLINK (relatív)
        $themeLinkPath = public_path('themepublic');
        $this->createRelativeSymlink($themePublicPath, $themeLinkPath);

        $this->info("Theme symlink létrehozva: {$themeLinkPath}");

        // 4️⃣ STORAGE SYMLINK (relatív)
        $storagePublicPath = base_path('storage/app/public');
        $storageLinkPath   = public_path('storage');

        $this->createRelativeSymlink($storagePublicPath, $storageLinkPath);

        $this->info("Storage symlink létrehozva: {$storageLinkPath}");

        // 5️⃣ currentTheme.json frissítése
        $config = [
            'theme' => $themeName,
            'paths' => [
                'css'   => "Themes/{$themeName}/public/css",
                'js'    => "Themes/{$themeName}/public/js",
                'views' => "Themes/{$themeName}/resources/views",
            ],
        ];

        file_put_contents(
            base_path('currentTheme.json'),
            json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->info("A(z) '{$themeName}' téma sikeresen beállítva.");

        return Command::SUCCESS;
    }

    /**
     * Relatív symlink létrehozása
     */
    private function createRelativeSymlink(string $target, string $link): void
    {
        // Ha már létezik (link vagy fájl), töröljük
        if (is_link($link) || file_exists($link)) {
            unlink($link);
        }

        $relativeTarget = $this->relativePath(
            dirname($link),
            $target
        );

        symlink($relativeTarget, $link);
    }

    /**
     * Abszolút útvonal → relatív útvonal számítása
     */
    private function relativePath(string $from, string $to): string
    {
        $from = str_replace('\\', '/', realpath($from));
        $to   = str_replace('\\', '/', realpath($to));

        $fromParts = explode('/', rtrim($from, '/'));
        $toParts   = explode('/', rtrim($to, '/'));

        while (
            count($fromParts) &&
            count($toParts) &&
            $fromParts[0] === $toParts[0]
        ) {
            array_shift($fromParts);
            array_shift($toParts);
        }

        return str_repeat('../', count($fromParts)) . implode('/', $toParts);
    }
}
