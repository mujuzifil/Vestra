<?php

namespace App\Listeners;

use App\Models\AdminSession;
use App\Models\User;
use Illuminate\Auth\Events\Logout;

class LogAdminLogout
{
    public function handle(Logout $event): void
    {
        $user = $event->user;

        if (! $user instanceof User || ! $user->isAdmin()) {
            return;
        }

        AdminSession::where('session_id', session()->getId())->delete();
        AdminSession::where('user_id', $user->id)
            ->where('last_activity_at', '<', now()->subHours(24))
            ->delete();
    }
}
