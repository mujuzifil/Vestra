<?php

namespace App\Policies;

use App\Models\PaymentUpload;
use App\Models\User;

class PaymentUploadPolicy
{
    private function owns(User $user, PaymentUpload $upload): bool
    {
        return $user->distributor?->id === $upload->distributor_id;
    }

    public function view(User $user, PaymentUpload $upload): bool
    {
        return $user->isAdmin() || $this->owns($user, $upload);
    }

    public function create(User $user): bool
    {
        return $user->isDistributor() || $user->isAdmin();
    }

    public function verify(User $user, PaymentUpload $upload): bool
    {
        return $user->isAdmin();
    }
}
