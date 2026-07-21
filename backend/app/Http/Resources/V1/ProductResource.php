<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'features' => $this->features,
            'benefits' => $this->benefits,
            'specifications' => $this->specifications,
            'sku' => $this->sku,
            'price' => $this->price,
            'featured' => $this->featured,
            'status' => $this->status,
            'stock_quantity' => $this->stock_quantity,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'images' => $this->whenLoaded('images', fn () => ProductImageResource::collection($this->images)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
