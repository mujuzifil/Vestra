<?php

namespace App\Listeners;

use App\Models\LoginActivity;
use App\Models\User;
use App\Support\UserAgentParser;
use Illuminate\Auth\Events\Failed;

class LogAdminFailedLogin
{
    public function handle(Failed $event): void
    {
        $email = $event->credentials['email'] ?? null;

        if (! $email) {
            return;
        }

        $user = User::where('email', $email)->first();

        // Only log failed attempts for admin emails to avoid customer noise.
        if ($user && ! $user->isAdmin()) {
            return;
        }

        $ip = request()?->ip();
        $userAgent = request()?->userAgent();
        $parsed = UserAgentParser::parse($userAgent);

        LoginActivity::create([
            'user_id' => $user?->id,
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'successful' => false,
            'failed_reason' => 'Invalid credentials',
            'device' => $parsed['device'],
            'os' => $parsed['os'],
            'browser' => $parsed['browser'],
        ]);
    }
}
