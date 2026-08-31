<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Background Collector daemon runs every minute to capture user logins, logouts, usage deltas, and voucher sales 24/7
Schedule::command('mikrotik:collect')->everyMinute()->withoutOverlapping();

// Daily FUP Reset at midnight (00:00)
Schedule::command('mikrotik:fup-reset --cycle=daily')->dailyAt('00:00')->withoutOverlapping();

// Monthly FUP Reset on the 1st day of every month at midnight (00:00)
Schedule::command('mikrotik:fup-reset --cycle=monthly')->monthlyOn(1, '00:00')->withoutOverlapping();
