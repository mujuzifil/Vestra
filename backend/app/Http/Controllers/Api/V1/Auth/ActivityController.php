<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ActivityResource;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $activities = $request->user()
            ->auditLogs()
            ->latest()
            ->paginate($request->input('per_page', 15));

        return $this->successResponse(
            ActivityResource::collection($activities)
        );
    }
}
