<?php

namespace App\Policies;

use App\Models\CustomerDocument;
use App\Models\User;

class CustomerDocumentPolicy
{
    public function view(User $user, CustomerDocument $document): bool
    {
        return $document->user_id === $user->id;
    }

    public function download(User $user, CustomerDocument $document): bool
    {
        return $document->user_id === $user->id && $document->is_downloadable;
    }
}
