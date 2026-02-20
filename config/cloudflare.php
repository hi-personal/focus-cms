<?php

return [
    'enabled' => env('CLOUDFARE_ENABLED', false), // MIX_ helyett sima CLOUDFARE_ENABLED
    'site_key' => env('CLOUDFARE_SITE_KEY', ''),
    'secret_key' => env('CLOUDFARE_SECRET_KEY', ''),
    'threshold' => (float) env('CLOUDFARE_THRESHOLD', 0.5),
    'skip_env' => explode(',', env('CLOUDFARE_SKIP_ENV', '')), // Környezet alapú kihagyás
];