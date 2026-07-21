<?php

namespace App\Policies;

use App\Models\PaymentTransaction;
use App\Models\User;

class PaymentTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, PaymentTransaction $transaction): bool
    {
        return $user->isAdmin() || $transaction->order?->user_id === $user->id;
    }
}
