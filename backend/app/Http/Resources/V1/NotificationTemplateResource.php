<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_key' => $this->event_key,
            'name' => $this->name,
            'category' => $this->category,
            'description' => $this->description,
            'subject' => $this->subject,
            'email_body' => $this->email_body,
            'sms_body' => $this->sms_body,
            'in_app_body' => $this->in_app_body,
            'channels' => $this->channels_json,
            'variables' => $this->variables_json,
            'priority' => $this->priority,
            'is_active' => $this->is_active,
            'version' => $this->version,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
