<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\ExportContacts;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command(ExportContacts::class)->everyMinute();

Artisan::command('opt', function () {
    $this->call('optimize:clear');
    $this->call('optimize');
})->purpose('Run optimize:clear and optimize');

Artisan::command('dev', function () {
    $this->call('serve:dev');
})->purpose('Run serve:dev');