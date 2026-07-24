<?php

namespace App\Jobs\Notifications;

use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastAnnouncementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Announcement $announcement) {}

    public function handle(AnnouncementService $service): void
    {
        $service->broadcast($this->announcement);
    }
}
