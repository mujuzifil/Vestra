<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateNotificationPreferenceRequest;
use App\Http\Resources\V1\NotificationPreferenceResource;
use App\Services\AuditService;
use App\Services\NotificationPreferenceService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        protected NotificationPreferenceService $preferenceService
    ) {}

    public function show(Request $request): JsonResponse
    {
        $preferences = $this->preferenceService->preferencesFor($request->user());

        return $this->successResponse(
            new NotificationPreferenceResource($preferences)
        );
    }

    public function update(UpdateNotificationPreferenceRequest $request): JsonResponse
    {
        $user = $request->user();
        $preferences = $this->preferenceService->update($user, $request->validated());

        AuditService::log(
            $user,
            'notification_preferences_updated',
            $preferences,
            ['source' => 'api'],
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new NotificationPreferenceResource($preferences),
            'Notification preferences updated successfully.'
        );
    }
}
