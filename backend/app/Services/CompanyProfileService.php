<?php

namespace App\Services;

use App\Models\CompanyProfile;
use App\Models\User;

class CompanyProfileService
{
    public function getOrCreateForUser(User $user): CompanyProfile
    {
        return CompanyProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['primary_contact_email' => $user->email, 'primary_contact_name' => $user->name, 'primary_contact_phone' => $user->phone]
        );
    }

    public function updateForUser(User $user, array $data): CompanyProfile
    {
        $profile = $this->getOrCreateForUser($user);
        $profile->update($data);

        return $profile;
    }
}
