<?php

namespace App\Events\Notification;

use App\Models\CreditAccount;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreditLimitUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public CreditAccount $creditAccount,
        public ?string $reason = null
    ) {}
}
