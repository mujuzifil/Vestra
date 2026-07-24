<?php

namespace App\Jobs\Notifications;

use App\Services\AnnouncementService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessScheduledAnnouncementsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(AnnouncementService $service): void
    {
        $count = $service->publishScheduled();

        Log::info('Processed scheduled announcements', ['published' => $count]);
    }
}
