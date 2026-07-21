<?php

namespace App\Policies;

use App\Models\AdminSession;
use App\Models\User;

class AdminSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, AdminSession $session): bool
    {
        return $user->isAdmin();
    }

    public function terminate(User $user, AdminSession $session): bool
    {
        return $user->isAdmin();
    }
}
