<?php

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Process\Process;

if ($argc < 3) {
    echo "Hiba: Nem megfelelő paraméterek\n";
    exit(1);
}

$action = $argv[1];
$packageName = $argv[2];

// Csomagnév átalakítása témanévvé
$themeName = convertPackageNameToThemeName($packageName);

if ($action === 'post-package-install') {
    handleThemeInstall($themeName);
} elseif ($action === 'post-package-uninstall') {
    handleThemeUninstall($themeName);
}

function convertPackageNameToThemeName($packageName)
{
    // Eltávolítjuk a 'istvan/' előtagot
    $themeName = str_replace('istvan/', '', $packageName);

    // Kötőjelek helyett szóközök
    $themeName = str_replace('-', ' ', $themeName);

    // Minden szó első betűje nagybetű
    $themeName = ucwords($themeName);

    // Szóközök eltávolítása
    return str_replace(' ', '', $themeName);
}

function handleThemeInstall($themeName)
{
    $themePath = __DIR__."/../Themes/{$themeName}";

    if (!file_exists($themePath)) {
        echo "Hiba: A téma könyvtár nem található: {$themePath}\n";
        exit(1);
    }

    if (runArtisanCommand("theme:setup {$themeName}")) {
        runArtisanCommand("optimize:clear", true);
        echo "A(z) {$themeName} téma sikeresen telepítve lett.\n";
    }
}

function handleThemeUninstall($themeName)
{
    if (runArtisanCommand("theme:remove {$themeName}", true)) {
        runArtisanCommand("optimize:clear", true);
        echo "A(z) {$themeName} téma sikeresen eltávolítva.\n";
    }
}

function runArtisanCommand($command, $ignoreErrors = false)
{
    if (!file_exists(__DIR__.'/../artisan')) {
        echo "Hiba: Az artisan fájl nem található!\n";
        return false;
    }

    $process = new Process(['php', 'artisan', ...explode(' ', $command)]);
    $process->setTimeout(300);
    $process->run();

    if (!$process->isSuccessful() && !$ignoreErrors) {
        echo "Hiba a parancs végrehajtásakor: " . $process->getErrorOutput() . "\n";
        return false;
    }

    echo $process->getOutput() . "\n";
    return true;
}