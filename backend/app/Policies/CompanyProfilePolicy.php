<?php

namespace App\Policies;

use App\Models\CompanyProfile;
use App\Models\User;

class CompanyProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, CompanyProfile $profile): bool
    {
        return $user->isAdmin() || $profile->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, CompanyProfile $profile): bool
    {
        return $user->isAdmin() || $profile->user_id === $user->id;
    }

    public function delete(User $user, CompanyProfile $profile): bool
    {
        return $user->isAdmin();
    }

    public function export(User $user): bool
    {
        return $user->isAdmin();
    }

    public function import(User $user): bool
    {
        return $user->isAdmin();
    }
}
