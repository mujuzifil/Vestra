<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'full_name' => $this->full_name,
            'company_name' => $this->company_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'district' => $this->district,
            'city' => $this->city,
            'address' => $this->address,
            'preferred_delivery_date' => $this->preferred_delivery_date?->toDateString(),
            'delivery_location' => $this->delivery_location,
            'status' => $this->status?->value,
            'status_label' => $this->statusLabel(),
            'source' => $this->source,
            'requirements' => $this->requirements,
            'admin_notes' => $this->admin_notes,
            'priority' => $this->priority,
            'estimated_value' => $this->estimated_value,
            'expected_close_date' => $this->expected_close_date?->toDateString(),
            'attachments' => $this->attachments,
            'crm_metadata' => $this->crm_metadata,
            'items' => QuoteRequestItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
