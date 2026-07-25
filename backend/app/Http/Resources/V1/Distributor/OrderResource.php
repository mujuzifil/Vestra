<?php

namespace App\Http\Resources\V1\Distributor;

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
            'channel' => $this->channel?->value,
            'shipping_address' => $this->shipping_address,
            'subtotal' => number_format((float) $this->subtotal, 2),
            'shipping_cost' => number_format((float) $this->shipping_cost, 2),
            'tax_amount' => number_format((float) $this->tax_amount, 2),
            'distributor_discount_amount' => number_format((float) $this->distributor_discount_amount, 2),
            'total_amount' => number_format((float) $this->total_amount, 2),
            'notes' => $this->notes,
            'payment_url' => $this->when(isset($this->payment_url), $this->payment_url),
            'payment_required' => $this->when(isset($this->payment_required), $this->payment_required),
            'timeline' => $this->timeline(),
            'courier' => $this->courier,
            'tracking_number' => $this->tracking_number,
            'dispatched_at' => $this->dispatched_at,
            'delivered_at' => $this->delivered_at,
            'estimated_delivery' => $this->estimatedDelivery()?->toISOString(),
            'items' => \App\Http\Resources\V1\OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
