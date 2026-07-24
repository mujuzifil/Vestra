<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var DatabaseNotification $notification */
        $notification = $this->resource;

        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->data['title'] ?? null,
            'message' => $notification->data['message'] ?? null,
            'template_key' => $notification->data['template_key'] ?? null,
            'priority' => $notification->data['priority'] ?? 'normal',
            'action_url' => $notification->data['action_url'] ?? null,
            'data' => $notification->data['variables'] ?? new \stdClass(),
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString(),
        ];
    }
}
