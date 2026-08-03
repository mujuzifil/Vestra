<?php

namespace App\Policies;

use App\Models\QuoteRequest;
use App\Models\User;

class QuoteRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, QuoteRequest $quoteRequest): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, QuoteRequest $quoteRequest): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, QuoteRequest $quoteRequest): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, QuoteRequest $quoteRequest): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, QuoteRequest $quoteRequest): bool
    {
        return $user->isAdmin();
    }

    public function export(User $user): bool
    {
        return $user->isAdmin();
    }

    public function viewAsCustomer(User $user, QuoteRequest $quoteRequest): bool
    {
        return $quoteRequest->user_id === $user->id;
    }

    public function downloadAsCustomer(User $user, QuoteRequest $quoteRequest): bool
    {
        return $quoteRequest->user_id === $user->id;
    }
}
