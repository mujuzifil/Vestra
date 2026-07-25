<?php

namespace App\Policies;

use App\Models\DistributorDocument;
use App\Models\User;

class DistributorDocumentPolicy
{
    private function owns(User $user, DistributorDocument $document): bool
    {
        return $user->distributor?->id === $document->distributor_id;
    }

    public function view(User $user, DistributorDocument $document): bool
    {
        return $user->isAdmin() || $this->owns($user, $document);
    }

    public function create(User $user): bool
    {
        return $user->isDistributor() || $user->isAdmin();
    }

    public function delete(User $user, DistributorDocument $document): bool
    {
        return $user->isAdmin() || $this->owns($user, $document);
    }
}
