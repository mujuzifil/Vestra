<?php

namespace App\Listeners;

use App\Models\AdminSession;
use App\Models\LoginActivity;
use App\Models\User;
use App\Support\UserAgentParser;
use Illuminate\Auth\Events\Login;

class LogAdminLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User || ! $user->isAdmin()) {
            return;
        }

        $ip = request()?->ip();
        $userAgent = request()?->userAgent();
        $parsed = UserAgentParser::parse($userAgent);

        $user->update(['last_login_at' => now()]);

        LoginActivity::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'successful' => true,
            'device' => $parsed['device'],
            'os' => $parsed['os'],
            'browser' => $parsed['browser'],
        ]);

        AdminSession::updateOrCreate(
            ['session_id' => session()->getId()],
            [
                'user_id' => $user->id,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'device' => $parsed['device'],
                'os' => $parsed['os'],
                'browser' => $parsed['browser'],
                'last_activity_at' => now(),
            ]
        );
    }
}
