<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->items ?? collect();
        $subtotal = $items->sum(fn ($item) => $item->quantity * $item->product->price);

        return [
            'id' => $this->id,
            'items' => CartItemResource::collection($items),
            'item_count' => $items->sum('quantity'),
            'subtotal' => number_format($subtotal, 2),
        ];
    }
}
