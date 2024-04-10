<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('email:account:validity')
    ->dailyAt('01:00');

Schedule::command('moodle:sync')
    ->everyThirtyMinutes();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')
    ->hourly();

if ($this->app->isLocal()) {
    Schedule::command('telescope:prune --hours=48')
        ->daily();
}
