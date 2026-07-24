<?php

namespace App\Policies;

use App\Models\QuotationRequest;
use App\Models\User;

class QuotationRequestPolicy
{
    private function owns(User $user, QuotationRequest $quotation): bool
    {
        return $user->distributor?->id === $quotation->distributor_id;
    }

    public function view(User $user, QuotationRequest $quotation): bool
    {
        return $user->isAdmin() || $this->owns($user, $quotation);
    }

    public function create(User $user): bool
    {
        return $user->isDistributor() || $user->isAdmin();
    }

    public function update(User $user, QuotationRequest $quotation): bool
    {
        return $user->isAdmin() || ($this->owns($user, $quotation) && $quotation->isEditable());
    }

    public function delete(User $user, QuotationRequest $quotation): bool
    {
        return $user->isAdmin() || ($this->owns($user, $quotation) && $quotation->status->value === 'draft');
    }

    public function accept(User $user, QuotationRequest $quotation): bool
    {
        return $user->isAdmin() || ($this->owns($user, $quotation) && $quotation->status->value === 'quoted');
    }
}
