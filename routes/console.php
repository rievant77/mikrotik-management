<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Background Collector daemon runs every minute to capture user logins, logouts, usage deltas, and voucher sales 24/7
Schedule::command('mikrotik:collect')->everyMinute()->withoutOverlapping();
