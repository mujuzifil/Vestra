<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CustomerLoginRequest;
use App\Http\Resources\V1\UnifiedLoginResource;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ExchangeToken\ExchangeTokenService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UnifiedLoginController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly ExchangeTokenService $exchangeTokenService)
    {
    }

    public function login(CustomerLoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('email', $request->validated('email'))
            ->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->isActive()) {
            $message = $user->isAdmin()
                ? 'Account is disabled. Contact a Super Administrator.'
                : 'Account is disabled. Contact support.';

            return $this->errorResponse($message, 403);
        }

        $isAdmin = $user->isAdmin();
        $role = $this->resolveRole($user);
        $redirectTo = $isAdmin ? '/admin' : '/account';
        $tokenName = $isAdmin ? 'admin-token' : 'customer-token';
        $abilities = $isAdmin ? ['admin'] : ['customer'];

        AuditService::logAuth($user, 'login', $request->ip(), $request->userAgent());

        if ($isAdmin && $user->mustChangePassword()) {
            AuditService::logAuth($user, 'password_change.required', $request->ip(), $request->userAgent());
        }

        $token = $user->createToken($tokenName, $abilities)->plainTextToken;

        $exchangeToken = null;
        if ($isAdmin) {
            $exchangeToken = $this->exchangeTokenService->create(
                $user,
                $request->ip(),
                $request->userAgent()
            )['plain_text'];

            AuditService::logAuth($user, 'exchange_token.created', $request->ip(), $request->userAgent());
        }

        return $this->successResponse(
            new UnifiedLoginResource([
                'user' => $user,
                'token' => $token,
                'role' => $role,
                'redirect_to' => $redirectTo,
                'exchange_token' => $exchangeToken,
            ]),
            'Login successful.'
        );
    }

    private function resolveRole(User $user): string
    {
        if (! $user->isAdmin()) {
            return 'customer';
        }

        if ($user->hasRole('Super Administrator')) {
            return 'super-administrator';
        }

        return 'administrator';
    }
}
