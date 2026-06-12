<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Jobs\LgpdDataRetentionJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new LgpdDataRetentionJob)->daily();
Schedule::command('subscriptions:process-grace-period')->dailyAt('00:30');
Schedule::command('subscriptions:warn-expiring')->dailyAt('01:00');
Schedule::command('subscriptions:expire-overdue')->dailyAt('02:00');
Schedule::command('backup:run')->dailyAt('03:00');
Schedule::command('backup:clean')->dailyAt('04:00');
Schedule::command('backup:monitor')->dailyAt('04:30');
Schedule::command('system:health-check --alert')->dailyAt('05:00');
Schedule::command('saas:check-trials')->dailyAt('08:00');
Schedule::command('system:clean-logs --days=30')->dailyAt('03:30');
Schedule::command('saas:check-ai-consumption')->everyFourHours();
