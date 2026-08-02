<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\Account\NotificationRead;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\NotificationResource;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate($request->integer('per_page', 15));

        $resource = NotificationResource::collection($notifications)->response()->getData(true);

        return $this->successResponse([
            'data' => $resource['data'],
            'current_page' => $resource['meta']['current_page'],
            'last_page' => $resource['meta']['last_page'],
            'per_page' => $resource['meta']['per_page'],
            'total' => $resource['meta']['total'],
        ]);
    }

    public function unread(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->unreadNotifications()
            ->latest()
            ->paginate($request->integer('per_page', 15));

        $resource = NotificationResource::collection($notifications)->response()->getData(true);

        return $this->successResponse([
            'data' => $resource['data'],
            'current_page' => $resource['meta']['current_page'],
            'last_page' => $resource['meta']['last_page'],
            'per_page' => $resource['meta']['per_page'],
            'total' => $resource['meta']['total'],
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if (! $notification) {
            return $this->errorResponse('Notification not found.', 404);
        }

        $notification->markAsRead();

        NotificationRead::dispatch($request->user(), $id);

        return $this->successResponse(null, 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        NotificationRead::dispatch($request->user(), '', true);

        return $this->successResponse(null, 'All notifications marked as read.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if (! $notification) {
            return $this->errorResponse('Notification not found.', 404);
        }

        $notification->delete();

        return $this->successResponse(null, 'Notification deleted.');
    }
}
