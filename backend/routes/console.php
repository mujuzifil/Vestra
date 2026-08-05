<?php

use App\Jobs\Analytics\CleanupApiRequestLogsJob;
use App\Jobs\Analytics\GenerateDailyReportSnapshotJob;
use App\Jobs\Analytics\GenerateForecastSnapshotJob;
use App\Jobs\Notifications\ProcessScheduledAnnouncementsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('auth:cleanup-exchange-tokens')->hourly();
Schedule::command('sanctum:cleanup-expired')->hourly();
Schedule::command('blog:publish-scheduled')->everyMinute();
Schedule::job(new ProcessScheduledAnnouncementsJob)->everyFiveMinutes();

Schedule::job(new GenerateDailyReportSnapshotJob)->dailyAt('02:00');
Schedule::job(new GenerateForecastSnapshotJob)->dailyAt('03:00');
Schedule::job(new CleanupApiRequestLogsJob)->weekly();

Schedule::call(function () {
    Cache::put('schedule:last_run', now()->toDateTimeString(), 3600);
})->everyMinute();
