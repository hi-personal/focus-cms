<?php

return [
    'options' => [
        'website_settings' => [
            'website_setting_current_theme_name'            => 'nullable|string',
            'website_setting_start_page_id'                 => 'nullable|string',
            'website_setting_mailjet_apikey'                => 'nullable|string',
            'website_setting_mailjet_apisecret'             => 'nullable|string',
            'website_setting_mailjet_from_address'          => 'nullable|string',
            'website_setting_mailjet_from_name'             => 'nullable|string',
            'website_setting_smtp_encryption'               => 'nullable|string',
            'website_setting_smtp_from_address'             => 'nullable|string',
            'website_setting_smtp_from_name'                => 'nullable|string',
            'website_setting_smtp_host'                     => 'nullable|string',
            'website_setting_smtp_local_domain'             => 'nullable|string',
            'website_setting_smtp_password'                 => 'nullable|string',
            'website_setting_smtp_port'                     => 'nullable|integer',
            'website_setting_smtp_timeout'                  => 'nullable|integer',
            'website_setting_smtp_url'                      => 'nullable|string',
            'website_setting_smtp_username'                 => 'nullable|string',
            'website_setting_categories_image_container_id' => 'nullable|integer',
            'website_setting_public_registration_status'    => 'boolean',
            'website_setting_posts_per_page'                => 'integer',
        ],
        'default_values' => [
            'website_setting_current_theme_name'         => 'FocusDefaultTheme',
            'website_setting_start_page_id'              => 0,
            'website_setting_public_registration_status' => true,
            'website_setting_posts_per_page'             => 10,
        ]
    ],
    'user_metas' => [
        'profile_metas' => [
            'bio'                  => 'nullable|string',
            'website'              => 'nullable|url',
            'phone'                => 'nullable|phone:HU,US,DE',
            'auth_2fa_status'      => 'nullable|boolean',
            'auth_2fa_mode'        => 'nullable|string',
            'auth_2fa_app_secret'  => 'nullable|string',
            'auth_2fa_temp_secret' => 'nullable|string',
        ],
        'default_values' => [
            'auth_2fa_mode'        => 'email',
            'auth_2fa_status'      => false,
            'auth_2fa_temp_secret' => null

        ]
    ]
];