<?php

namespace App\Policies;

use App\Models\LoginActivity;
use App\Models\User;

class LoginActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, LoginActivity $activity): bool
    {
        return $user->isAdmin();
    }
}
