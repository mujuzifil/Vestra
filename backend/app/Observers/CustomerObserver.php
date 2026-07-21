<?php

namespace App\Observers;

use App\Models\User;
use App\Services\AdminNotificationService;

class CustomerObserver
{
    public function __construct(private readonly AdminNotificationService $service) {}

    public function created(User $user): void
    {
        // Only notify for non-admin customers
        if (! $user->isAdmin()) {
            $this->service->newCustomer($user->name, $user->email);
        }
    }
}
