<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerQuoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'company_name' => $this->company_name,
            'status' => $this->status?->value ?? $this->status,
            'status_label' => $this->statusLabel(),
            'priority' => $this->priority,
            'estimated_value' => $this->estimated_value,
            'items' => CustomerQuoteItemResource::collection($this->whenLoaded('items')),
            'requirements' => $this->requirements,
            'attachments' => $this->attachments,
            'sales_representative' => $this->whenLoaded('assignedUser', fn () => [
                'name' => $this->assignedUser->name,
                'email' => $this->assignedUser->email,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
