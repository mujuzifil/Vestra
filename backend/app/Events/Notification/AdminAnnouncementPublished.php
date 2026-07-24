<?php

namespace App\Events\Notification;

use App\Models\Announcement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminAnnouncementPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(public Announcement $announcement) {}
}
