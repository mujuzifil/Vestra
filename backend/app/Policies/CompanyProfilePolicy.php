<?php

namespace App\Policies;

use App\Models\CompanyProfile;
use App\Models\User;

class CompanyProfilePolicy
{
    public function view(User $user, CompanyProfile $profile): bool
    {
        return $profile->user_id === $user->id;
    }

    public function update(User $user, CompanyProfile $profile): bool
    {
        return $profile->user_id === $user->id;
    }
}
