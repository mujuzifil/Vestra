<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\V1\CustomerResource;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use RespondsWithJson;

    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles');

        return $this->successResponse(
            new CustomerResource($user)
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $request->user()->update($request->validated());

        return $this->successResponse(
            new CustomerResource($request->user()->fresh()),
            'Profile updated successfully.'
        );
    }
}
