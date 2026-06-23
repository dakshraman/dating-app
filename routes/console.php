<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('dating:expire-boosts')->everyMinute();
Schedule::command('dating:expire-matches')->daily();
Schedule::command('swipes:reset')->dailyAt('12:00')->timezone('Asia/Kolkata');
