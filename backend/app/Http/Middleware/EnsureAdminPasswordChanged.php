<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->isAdmin() && $user->mustChangePassword()) {
            $forceChangePath = route('filament.admin.pages.force-password-change');
            $currentPath = $request->url();

            if ($currentPath === $forceChangePath) {
                return $next($request);
            }

            AuditService::log(
                $user,
                'password_change.bypass_attempt',
                $user,
                ['requested_url' => $currentPath],
                $request->ip(),
                $request->userAgent()
            );

            return redirect($forceChangePath);
        }

        return $next($request);
    }
}
