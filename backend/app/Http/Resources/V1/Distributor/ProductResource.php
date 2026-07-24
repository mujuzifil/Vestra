<?php

namespace App\Http\Resources\V1\Distributor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => new \App\Http\Resources\V1\CategoryResource($this->category)),
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'features' => $this->features,
            'benefits' => $this->benefits,
            'specifications' => $this->specifications,
            'sku' => $this->sku,
            'price' => (string) $this->price,
            'distributor_price' => $this->distributor_price !== null ? (string) $this->distributor_price : null,
            'wholesale_price' => isset($this->wholesale_price) ? (string) $this->wholesale_price : null,
            'negotiated_price' => null,
            'tier_price' => null,
            'moq' => null,
            'price_tiers' => $this->whenLoaded('distributorPriceTiers', fn () => $this->distributorPriceTiers),
            'stock_quantity' => $this->stock_quantity,
            'stock_status_label' => $this->stockStatusLabel(),
            'stock_status_color' => $this->stockStatusColor(),
            'status' => $this->status,
            'images' => $this->whenLoaded('images', fn () => \App\Http\Resources\V1\ProductImageResource::collection($this->images)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
