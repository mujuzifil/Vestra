<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketReplyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $author = $this->staff ?? $this->user;

        return [
            'id' => $this->id,
            'message' => $this->message,
            'author' => $author ? ['name' => $author->name, 'type' => $this->staff_id ? 'staff' : 'customer'] : null,
            'attachments' => $this->attachments,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
