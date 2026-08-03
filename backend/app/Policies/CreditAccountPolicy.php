<?php

namespace App\Policies;

use App\Models\CreditAccount;
use App\Models\User;

class CreditAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, CreditAccount $account): bool
    {
        return $user->isAdmin();
    }

    public function updateLimit(User $user, CreditAccount $account): bool
    {
        return $user->isAdmin();
    }

    public function export(User $user): bool
    {
        return $user->isAdmin();
    }
}
