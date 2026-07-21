<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    use RespondsWithJson;

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $user?->currentAccessToken()?->delete();

        if ($user) {
            AuditService::logAuth($user, 'logout', $request->ip(), $request->userAgent());
        }

        return $this->successResponse(
            null,
            'Logged out successfully.'
        );
    }
}
