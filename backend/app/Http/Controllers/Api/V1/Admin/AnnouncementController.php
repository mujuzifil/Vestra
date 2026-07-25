<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreAnnouncementRequest;
use App\Http\Requests\Api\V1\Admin\UpdateAnnouncementRequest;
use App\Http\Resources\V1\AnnouncementResource;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        protected AnnouncementService $announcementService
    ) {}

    public function index(): JsonResponse
    {
        return $this->successResponse(
            AnnouncementResource::collection($this->announcementService->paginate())
        );
    }

    public function show(Announcement $announcement): JsonResponse
    {
        return $this->successResponse(
            new AnnouncementResource($announcement)
        );
    }

    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        $announcement = $this->announcementService->create(
            array_merge($request->validated(), ['created_by' => $request->user()->id])
        );

        return $this->successResponse(
            new AnnouncementResource($announcement),
            'Announcement created successfully.',
            201
        );
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): JsonResponse
    {
        $announcement = $this->announcementService->update($announcement, $request->validated());

        return $this->successResponse(
            new AnnouncementResource($announcement),
            'Announcement updated successfully.'
        );
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $this->announcementService->delete($announcement);

        return $this->successResponse(null, 'Announcement deleted.');
    }
}
