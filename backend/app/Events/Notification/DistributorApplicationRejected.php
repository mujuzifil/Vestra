<?php

namespace App\Events\Notification;

use App\Models\DistributorRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DistributorApplicationRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public DistributorRequest $distributorRequest,
        public ?string $reason = null
    ) {}
}
