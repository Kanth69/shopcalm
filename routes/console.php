<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::command('marketing:run-automations --type=cart')
    ->everyThirtyMinutes()
    ->withoutOverlapping();

\Illuminate\Support\Facades\Schedule::command('marketing:run-automations --type=birthday')
    ->dailyAt('09:00')
    ->withoutOverlapping();

\Illuminate\Support\Facades\Schedule::command('shopcalm:send-wallet-nudges')
    ->dailyAt('11:30')
    ->withoutOverlapping();
