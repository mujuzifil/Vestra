<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Events\Notification\PasswordChanged;
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

        if (Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Choose a new password that is different from your temporary password.'],
            ]);
        }

        $user->password = $request->validated('password');
        $user->save();

        // Invalidate all other personal access tokens so old credentials cannot
        // continue to be used after a password change.
        $currentTokenId = $user->currentAccessToken()?->id;
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        $hadRequiredChange = $user->mustChangePassword();
        $user->clearPasswordChangeRequired();

        event(new PasswordChanged($user));

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
