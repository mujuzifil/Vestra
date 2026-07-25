<?php

namespace App\Policies;

use App\Models\DistributorContact;
use App\Models\User;

class DistributorContactPolicy
{
    private function owns(User $user, DistributorContact $contact): bool
    {
        return $user->distributor?->id === $contact->distributor_id;
    }

    public function view(User $user, DistributorContact $contact): bool
    {
        return $user->isAdmin() || $this->owns($user, $contact);
    }

    public function update(User $user, DistributorContact $contact): bool
    {
        return $user->isAdmin() || $this->owns($user, $contact);
    }

    public function delete(User $user, DistributorContact $contact): bool
    {
        return $user->isAdmin() || $this->owns($user, $contact);
    }
}
