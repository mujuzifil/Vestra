<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $url = method_exists($this->resource, 'resolvedUrl')
            ? $this->resource->resolvedUrl()
            : ($this->image ? asset('storage/'.$this->image) : null);

        return [
            'id' => $this->id,
            'image' => $url,
            'media_asset_id' => $this->media_asset_id,
            'alt_text' => $this->alt_text,
            'sort_order' => $this->sort_order,
        ];
    }
}
