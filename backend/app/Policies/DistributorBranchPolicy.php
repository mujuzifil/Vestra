<?php

namespace App\Policies;

use App\Models\DistributorBranch;
use App\Models\User;
use App\Support\ChecksDiscoveredPermission;

class DistributorBranchPolicy
{
    use ChecksDiscoveredPermission;

    private function owns(User $user, DistributorBranch $branch): bool
    {
        return $user->distributor?->id === $branch->distributor_id;
    }

    public function viewAny(User $user): bool
    {
        return $this->allowsPermission($user, 'territories.view')
            || $this->allowsPermission($user, 'coverage.view');
    }

    public function view(User $user, DistributorBranch $branch): bool
    {
        return $user->isAdmin()
            || $this->owns($user, $branch)
            || $this->allowsPermission($user, 'territories.view')
            || $this->allowsPermission($user, 'coverage.view');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, DistributorBranch $branch): bool
    {
        return $this->allowsPermission($user, 'territories.edit')
            || $this->allowsPermission($user, 'coverage.edit')
            || $this->owns($user, $branch);
    }

    public function delete(User $user, DistributorBranch $branch): bool
    {
        return false;
    }

    public function export(User $user): bool
    {
        return $this->allowsPermission($user, 'territories.export')
            || $this->allowsPermission($user, 'coverage.export');
    }
}
