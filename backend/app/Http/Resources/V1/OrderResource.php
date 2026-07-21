<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'shipping_address' => $this->shipping_address,
            'subtotal' => number_format($this->subtotal, 2),
            'shipping_cost' => number_format($this->shipping_cost, 2),
            'tax_amount' => number_format($this->tax_amount, 2),
            'total_amount' => number_format($this->total_amount, 2),
            'notes' => $this->notes,
            'payment_url' => $this->when(isset($this->payment_url), $this->payment_url),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
