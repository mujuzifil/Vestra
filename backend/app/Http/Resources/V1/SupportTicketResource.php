<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'subject' => $this->subject,
            'enquiry_type' => $this->enquiry_type,
            'message' => $this->message,
            'status' => $this->status,
            'priority' => $this->priority,
            'assigned_to' => $this->whenLoaded('assignedStaff', fn () => [
                'name' => $this->assignedStaff->name,
                'email' => $this->assignedStaff->email,
            ]),
            'replies' => SupportTicketReplyResource::collection($this->whenLoaded('replies')),
            'attachments' => $this->attachments,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
