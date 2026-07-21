<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ChangePasswordRequest;
use App\Services\AuditService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ChangePasswordController extends Controller
{
    use RespondsWithJson;

    public function store(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->validated('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        $hadRequiredChange = $user->mustChangePassword();
        $user->clearPasswordChangeRequired();

        AuditService::log(
            $user,
            'password_changed',
            $user,
            ['source' => 'api', 'had_required_change' => $hadRequiredChange],
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            null,
            'Password changed successfully.'
        );
    }
}
