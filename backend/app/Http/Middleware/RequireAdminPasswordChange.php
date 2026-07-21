<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use App\Traits\RespondsWithJson;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminPasswordChange
{
    use RespondsWithJson;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isAdmin() && $user->mustChangePassword()) {
            AuditService::log(
                $user,
                'password_change.bypass_attempt',
                $user,
                ['requested_url' => $request->url(), 'method' => $request->method()]
            );

            return $this->errorResponse(
                'Password change required before accessing this resource.',
                403
            );
        }

        return $next($request);
    }
}
