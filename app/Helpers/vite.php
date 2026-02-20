<?php

function check_theme_vite_is_active() {
    return (app()->environment('local') && Illuminate\Support\Facades\Vite::isRunningHot());
}

function theme_vite_assets($themeName) {
    return [
        "resources/js/theme-{$themeName}-app.js",
        "Themes/{$themeName}/public/js/theme.js",
        "resources/css/theme-{$themeName}-app.css"
    ];
}

function theme_vite_assets_builded($themeName) {
    // Először próbáljuk a téma saját manifestjét
    $themeManifestPath = public_path("themepublic/build/manifest.json");

    //$manifest = json_decode(file_get_contents($manifestPath), true);
    $manifest = json_decode(file_get_contents($themeManifestPath), true);

    return [
        "js_1" => "themepublic/".$manifest["resources/js/theme-{$themeName}-app.js"]['file'] ?? '',
        "js_2" => "themepublic/".$manifest["Themes/{$themeName}/public/js/theme.js"]['file'] ?? '',
        "css" => "themepublic/".$manifest["resources/css/theme-{$themeName}-app.css"]['file'] ?? ''
    ];
}



if (!function_exists('load_theme_assets')) {
    function load_theme_assets($entry = 'resources/js/app.js')
    {
        $viteServerRunning = false;
        $vitePort = 5174; // A téma Vite szerver portja

        // Ellenőrizzük, hogy fut-e a Vite szerver
        if (app()->environment('local')) {
            try {
                $client = new \GuzzleHttp\Client();
                $response = $client->get("http://localhost:{$vitePort}", ['timeout' => 0.1]);
                $viteServerRunning = $response->getStatusCode() === 200;
            } catch (\Exception $e) {
                $viteServerRunning = false;
            }
        }

        if ($viteServerRunning) {
            // Vite szerverről töltjük be
            return "<script type=\"module\" src=\"http://localhost:{$vitePort}/{$entry}\"></script>";
        } else {
            // Buildelt fájlokból töltjük be
            $manifestPath = public_path('themes/focus-default-theme/build/manifest.json');

            if (!file_exists($manifestPath)) {
                throw new \Exception("Vite manifest not found at: {$manifestPath}");
            }

            $manifest = json_decode(file_get_contents($manifestPath), true);

            if (!isset($manifest[$entry])) {
                throw new \Exception("Unable to locate theme asset in manifest: {$entry}");
            }

            $assetPath = asset("themes/focus-default-theme/build/{$manifest[$entry]['file']}");

            $tags = [];

            // CSS fájlok hozzáadása ha vannak
            if (!empty($manifest[$entry]['css'])) {
                foreach ($manifest[$entry]['css'] as $cssFile) {
                    $tags[] = sprintf(
                        '<link rel="stylesheet" href="%s">',
                        asset("themes/focus-default-theme/build/{$cssFile}")
                    );
                }
            }

            // JS fájl hozzáadása
            $tags[] = sprintf(
                '<script type="module" src="%s"></script>',
                $assetPath
            );

            return implode('', $tags);
        }
    }
}