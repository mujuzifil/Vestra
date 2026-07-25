<?php

namespace App\Policies;

use App\Models\DistributorBranch;
use App\Models\User;

class DistributorBranchPolicy
{
    private function owns(User $user, DistributorBranch $branch): bool
    {
        return $user->distributor?->id === $branch->distributor_id;
    }

    public function view(User $user, DistributorBranch $branch): bool
    {
        return $user->isAdmin() || $this->owns($user, $branch);
    }

    public function update(User $user, DistributorBranch $branch): bool
    {
        return $user->isAdmin() || $this->owns($user, $branch);
    }

    public function delete(User $user, DistributorBranch $branch): bool
    {
        return $user->isAdmin() || $this->owns($user, $branch);
    }
}
