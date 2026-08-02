<?php

namespace App\Events\Account;

use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompanyProfileUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly CompanyProfile $profile
    ) {}
}
