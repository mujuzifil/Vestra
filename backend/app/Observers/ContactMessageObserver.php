<?php

namespace App\Observers;

use App\Models\ContactMessage;
use App\Services\AdminNotificationService;

class ContactMessageObserver
{
    public function __construct(private readonly AdminNotificationService $service) {}

    public function created(ContactMessage $message): void
    {
        $this->service->newContactMessage($message->name, $message->subject);
    }
}
