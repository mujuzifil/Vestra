<?php

namespace App\Observers;

use App\Models\CustomerFeedback;
use App\Services\AdminNotificationService;

class CustomerFeedbackObserver
{
    public function __construct(private readonly AdminNotificationService $service) {}

    public function created(CustomerFeedback $feedback): void
    {
        $this->service->newFeedback($feedback->category, $feedback->subject);
    }
}
