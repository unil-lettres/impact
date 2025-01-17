<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('email:account:validity')
    ->dailyAt('01:00')
    ->onOneServer();

Schedule::command('moodle:sync')
    ->everyThirtyMinutes()
    ->onOneServer();

if ($this->app->isLocal()) {
    Schedule::command('telescope:prune --hours=48')
        ->daily()
        ->onOneServer();
}
