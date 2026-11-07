<?php

return [

    'apps' => [
        'admin' => [
            'entry_points' => [
                0 => 'resources/js/app.js',
                1 => 'resources/css/app.css',
            ],
            'build_directory' => 'build/admin',
            'hot_file' => '/var/www/focuscms/public/build/admin/hot',
        ],
        'front' => [
            'entry_points' => [
                0 => 'Themes/FocusDefaultTheme/resources/js/theme.js',
                1 => 'Themes/FocusDefaultTheme/resources/css/theme.css',
            ],
            'build_directory' => 'build/front',
            'hot_file' => '/var/www/focuscms/public/build/front/hot',
        ],
    ],

];
