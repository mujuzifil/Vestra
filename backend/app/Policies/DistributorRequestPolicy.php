<?php

namespace App\Policies;

use App\Models\DistributorRequest;
use App\Models\User;

class DistributorRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, DistributorRequest $distributorRequest): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, DistributorRequest $distributorRequest): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, DistributorRequest $distributorRequest): bool
    {
        return $user->isAdmin();
    }
}
