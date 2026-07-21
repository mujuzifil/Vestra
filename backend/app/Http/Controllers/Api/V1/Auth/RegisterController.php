<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\V1\CustomerResource;
use App\Models\User;
use App\Services\AuditService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    use RespondsWithJson;

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Block any attempt to elevate privileges during public registration.
        if ($request->hasAny(['is_admin', 'role', 'roles', 'status'])) {
            AuditService::log(
                null,
                'privilege_escalation_attempt',
                null,
                [
                    'email' => $data['email'] ?? null,
                    'fields' => $request->only(['is_admin', 'role', 'roles', 'status']),
                    'ip' => $request->ip(),
                ],
                $request->ip(),
                $request->userAgent()
            );

            return $this->errorResponse('Invalid registration data.', 422);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        $user->forceFill([
            'is_admin' => false,
            'status' => 'active',
        ])->save();

        // Assign customer role
        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        $user->assignRole($customerRole);

        $token = $user->createToken('customer-token', ['customer'])->plainTextToken;

        return $this->successResponse([
            'user' => new CustomerResource($user),
            'token' => $token,
        ], 'Registration successful.', 201);
    }
}
