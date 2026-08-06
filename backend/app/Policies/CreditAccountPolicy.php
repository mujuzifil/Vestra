<?php

namespace App\Policies;

use App\Models\CreditAccount;
use App\Models\User;
use App\Support\ChecksDiscoveredPermission;

class CreditAccountPolicy
{
    use ChecksDiscoveredPermission;

    public function viewAny(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->allowsPermission($user, 'credit.view');
    }

    public function view(User $user, CreditAccount $account): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->allowsPermission($user, 'credit.view');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function updateLimit(User $user, CreditAccount $account): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->allowsPermission($user, 'credit.edit')
            || $this->allowsPermission($user, 'credit.manage');
    }

    public function export(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->allowsPermission($user, 'credit.export');
    }
}
