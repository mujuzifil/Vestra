<?php

namespace App\Policies;

use App\Models\Distributor;
use App\Models\User;

class DistributorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Distributor $distributor): bool
    {
        return $user->isAdmin() || $user->id === $distributor->user_id;
    }

    public function update(User $user, Distributor $distributor): bool
    {
        return $user->id === $distributor->user_id || $user->isAdmin();
    }

    public function manage(User $user, Distributor $distributor): bool
    {
        return $user->id === $distributor->user_id || $user->isAdmin();
    }

    public function export(User $user): bool
    {
        return $user->isAdmin();
    }
}
