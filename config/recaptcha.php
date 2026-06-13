<?php

return [
    'enabled' => env('RECAPTCHA_ENABLED', false), // MIX_ helyett sima RECAPTCHA_ENABLED
    'site_key' => env('RECAPTCHA_SITE_KEY', ''),
    'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
    'threshold' => (float) env('RECAPTCHA_THRESHOLD', 0.5),
    'skip_env' => explode(',', env('RECAPTCHA_SKIP_ENV', '')), // Környezet alapú kihagyás
];