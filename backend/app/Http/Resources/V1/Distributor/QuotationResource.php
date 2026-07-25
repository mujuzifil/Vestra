<?php

namespace App\Http\Resources\V1\Distributor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'status' => $this->status?->value,
            'status_label' => $this->statusLabel(),
            'status_color' => $this->statusColor(),
            'notes' => $this->notes,
            'admin_notes' => $this->admin_notes,
            'submitted_at' => $this->submitted_at,
            'quoted_at' => $this->quoted_at,
            'expires_at' => $this->expires_at,
            'subtotal' => number_format((float) $this->subtotal, 2),
            'tax_amount' => number_format((float) $this->tax_amount, 2),
            'total_amount' => number_format((float) $this->total_amount, 2),
            'items' => QuotationItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
