<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\V1\CustomerResource;
use App\Services\AuditService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use RespondsWithJson;

    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'preferences']);

        return $this->successResponse(
            new CustomerResource($user)
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $oldValues = [
            'name' => $user->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'date_of_birth' => $user->date_of_birth?->toDateString(),
            'gender' => $user->gender,
        ];

        if (isset($validated['name'])) {
            $validated['first_name'] = $validated['first_name'] ?? $this->extractFirstName($validated['name']);
            $validated['last_name'] = $validated['last_name'] ?? $this->extractLastName($validated['name']);
        }

        $user->update($validated);

        AuditService::log(
            $user,
            'profile_updated',
            $user,
            [
                'old' => $oldValues,
                'new' => array_intersect_key($user->fresh()->toArray(), $oldValues),
                'source' => 'api',
            ],
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new CustomerResource($user->fresh()->load('preferences')),
            'Profile updated successfully.'
        );
    }

    private function extractFirstName(string $name): string
    {
        $parts = explode(' ', trim($name), 2);

        return $parts[0];
    }

    private function extractLastName(string $name): ?string
    {
        $parts = explode(' ', trim($name), 2);

        return $parts[1] ?? null;
    }
}
