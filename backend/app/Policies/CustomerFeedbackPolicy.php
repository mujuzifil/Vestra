<?php

namespace App\Policies;

use App\Models\CustomerFeedback;
use App\Models\User;

class CustomerFeedbackPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, CustomerFeedback $feedback): bool
    {
        return $user->isAdmin() || ($feedback->user_id !== null && $user->id === $feedback->user_id);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CustomerFeedback $feedback): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, CustomerFeedback $feedback): bool
    {
        return $user->isAdmin();
    }

    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }
}
