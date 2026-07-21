<?php

namespace App\Listeners;

use App\Models\AdminSession;
use App\Models\User;

class UpdateAdminSessionActivity
{
    public function handle($event): void
    {
        $user = auth()->user();

        if (! $user instanceof User || ! $user->isAdmin()) {
            return;
        }

        AdminSession::where('session_id', session()->getId())->update([
            'last_activity_at' => now(),
        ]);
    }
}
