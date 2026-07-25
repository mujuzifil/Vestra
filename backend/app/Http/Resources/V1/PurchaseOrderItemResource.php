<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product?->name,
            'quantity' => $this->quantity,
            'received_quantity' => $this->received_quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'notes' => $this->notes,
        ];
    }
}
