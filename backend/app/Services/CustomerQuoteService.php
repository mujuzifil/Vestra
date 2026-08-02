<?php

namespace App\Services;

use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class CustomerQuoteService
{
    public function listForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return QuoteRequest::where('user_id', $user->id)
            ->with(['items', 'assignedUser'])
            ->latest()
            ->paginate($perPage);
    }

    public function findForUser(User $user, int $id): ?QuoteRequest
    {
        return QuoteRequest::where('user_id', $user->id)
            ->with(['items', 'assignedUser'])
            ->find($id);
    }

    public function attachmentUrl(QuoteRequest $quote, int $index): ?string
    {
        $attachments = $quote->attachments ?? [];
        $path = $attachments[$index] ?? null;
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
