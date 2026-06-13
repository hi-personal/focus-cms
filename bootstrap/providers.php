<?php

/**
 * bootstrap/providers.php
 */

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\OptionServiceProvider::class,
    App\Providers\FocusCmsServiceProvider::class,

    Mailjet\LaravelMailjet\CampaignDraftServiceProvider::class,
    Mailjet\LaravelMailjet\MailjetMailServiceProvider::class,
    Mailjet\LaravelMailjet\MailjetServiceProvider::class,
];