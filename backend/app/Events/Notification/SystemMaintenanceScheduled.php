<?php

namespace App\Events\Notification;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SystemMaintenanceScheduled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $window,
        public ?string $description = null
    ) {}
}
