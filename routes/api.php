<?php

/**
 * focus-cms-front-module/config/page-cache.php
 */

return [
    'enabled' => true,
    'storage_path' => storage_path('page-cache'),
    'cache_routes' => [
        'front.home.*',
        'post.show.*',
        'taxonomy.*',
    ],
    'ignored_routes' => [
        'admin.*',
        'login',
        'register',
        'password.*',
        'verification.*',
        'logout',
        'sessions.*',
        '2fa.*',
        'maintenance',
    ],
    'ignored_query_params' => [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'fbclid',
        'gclid',
        'yclid',
        'msclkid',
    ],
];