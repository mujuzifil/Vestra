<?php

namespace App\Services;

use App\Models\CustomerDocument;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class CustomerDocumentService
{
    public function listForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return CustomerDocument::where('user_id', $user->id)
            ->with('documentable')
            ->latest()
            ->paginate($perPage);
    }

    public function downloadUrl(CustomerDocument $document): ?string
    {
        if (! $document->is_downloadable) {
            return null;
        }

        return Storage::disk('public')->url($document->file_path);
    }
}
