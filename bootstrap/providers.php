<?php

return [
    App\Providers\AppServiceProvider::class,
    Mailjet\LaravelMailjet\CampaignDraftServiceProvider::class,
    Mailjet\LaravelMailjet\MailjetMailServiceProvider::class,
    Mailjet\LaravelMailjet\MailjetServiceProvider::class,
    App\Providers\OptionServiceProvider::class,
];
