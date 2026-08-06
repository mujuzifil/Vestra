<?php

namespace App\Policies;

use App\Models\DistributorRequest;
use App\Models\User;
use App\Support\ChecksDiscoveredPermission;

class DistributorRequestPolicy
{
    use ChecksDiscoveredPermission;

    public function viewAny(User $user): bool
    {
        return $this->allowsPermission($user, 'applications.view');
    }

    public function view(User $user, DistributorRequest $distributorRequest): bool
    {
        return $this->allowsPermission($user, 'applications.view');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, DistributorRequest $distributorRequest): bool
    {
        return $this->allowsPermission($user, 'applications.edit')
            || $this->allowsPermission($user, 'applications.approve')
            || $this->allowsPermission($user, 'applications.reject');
    }

    public function approve(User $user, DistributorRequest $distributorRequest): bool
    {
        return $this->allowsPermission($user, 'applications.approve')
            || $this->allowsPermission($user, 'applications.edit');
    }

    public function reject(User $user, DistributorRequest $distributorRequest): bool
    {
        return $this->allowsPermission($user, 'applications.reject')
            || $this->allowsPermission($user, 'applications.edit');
    }

    public function delete(User $user, DistributorRequest $distributorRequest): bool
    {
        return $this->allowsPermission($user, 'applications.delete');
    }

    public function export(User $user): bool
    {
        return $this->allowsPermission($user, 'applications.export');
    }
}
