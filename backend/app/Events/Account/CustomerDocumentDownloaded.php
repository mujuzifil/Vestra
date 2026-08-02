<?php

namespace App\Events\Account;

use App\Models\CustomerDocument;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerDocumentDownloaded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly CustomerDocument $document
    ) {}
}
