<?php

namespace App\Events\Notification;

use App\Models\ContactMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactMessageSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}
}
