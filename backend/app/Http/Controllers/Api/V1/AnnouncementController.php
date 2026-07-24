<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AnnouncementResource;
use App\Services\AnnouncementService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        protected AnnouncementService $announcementService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $audience = $request->user()->isDistributor() ? 'distributors' : 'customers';
        $announcements = $this->announcementService->activeFor($request->user(), $audience);

        return $this->successResponse(
            AnnouncementResource::collection($announcements)
        );
    }
}
