<?php

namespace App\Services;

use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class CustomerQuoteService
{
    public function listForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryForUser($user)
            ->with(['items', 'assignedUser'])
            ->latest()
            ->paginate($perPage);
    }

    public function findForUser(User $user, int $id): ?QuoteRequest
    {
        return $this->queryForUser($user)
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

    private function queryForUser(User $user): Builder
    {
        return QuoteRequest::query()->where(function (Builder $query) use ($user): void {
            $query->where('user_id', $user->id)
                ->orWhereHas('companyProfile', fn (Builder $profile) => $profile->where('user_id', $user->id));
        });
    }
}
