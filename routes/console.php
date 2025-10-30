<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule SPADA sync every 6 hours
Schedule::command('spada:sync')
    ->everySixHours()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Log::info('SPADA sync completed successfully');
    })
    ->onFailure(function () {
        \Log::error('SPADA sync failed');
    });

// Schedule deadline reminders every hour
Schedule::command('reminders:deadline')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Log::info('Deadline reminders sent successfully');
    })
    ->onFailure(function () {
        \Log::error('Deadline reminders failed');
    });

// Schedule deadline reminders every hour
Schedule::command('reminders:deadline')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Log::info('Deadline reminders sent successfully');
    })
    ->onFailure(function () {
        \Log::error('Deadline reminders failed');
    });
