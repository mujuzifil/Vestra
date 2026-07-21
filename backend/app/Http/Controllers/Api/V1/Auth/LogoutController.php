<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    use RespondsWithJson;

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $user?->currentAccessToken()?->delete();

        // If a web session exists (admin exchange flow), invalidate it as well.
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
            $request->session()?->invalidate();
            $request->session()?->regenerateToken();
        }

        if ($user) {
            AuditService::logAuth($user, 'logout', $request->ip(), $request->userAgent());
        }

        return $this->successResponse(
            null,
            'Logged out successfully.'
        );
    }
}
