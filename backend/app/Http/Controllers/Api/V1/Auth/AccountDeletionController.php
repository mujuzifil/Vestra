<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDeletionRequest;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;

class AccountDeletionController extends Controller
{
    use RespondsWithJson;

    public function store(StoreDeletionRequest $request): JsonResponse
    {
        $user = $request->user();

        $existing = $user->deletionRequests()->pending()->first();

        if ($existing) {
            return $this->successResponse(
                [
                    'id' => $existing->id,
                    'status' => $existing->status,
                    'requested_at' => $existing->requested_at,
                ],
                'You already have a pending account deletion request.'
            );
        }

        $deletionRequest = $user->deletionRequests()->create([
            'reason' => $request->validated('reason'),
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        return $this->successResponse(
            [
                'id' => $deletionRequest->id,
                'status' => $deletionRequest->status,
                'requested_at' => $deletionRequest->requested_at,
            ],
            'Account deletion request submitted successfully.',
            201
        );
    }
}
