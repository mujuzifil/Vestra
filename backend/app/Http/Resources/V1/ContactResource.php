<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company' => $this->company,
            'email' => $this->email,
            'phone' => $this->phone,
            'subject' => $this->subject,
            'enquiry_type' => $this->enquiry_type?->value,
            'enquiry_type_label' => $this->enquiryTypeLabel(),
            'message' => $this->message,
            'attachments' => $this->attachments,
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'priority' => $this->priority,
            'assigned_to' => $this->assigned_to,
            'internal_notes' => $this->internal_notes,
            'source' => $this->source,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
