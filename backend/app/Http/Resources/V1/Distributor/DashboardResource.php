<?php

namespace App\Http\Resources\V1\Distributor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'distributor' => new DistributorProfileResource($this['distributor']),
            'recent_orders' => OrderResource::collection($this['recent_orders']),
            'recent_quotes' => QuotationResource::collection($this['recent_quotes']),
            'recent_notifications' => $this['recent_notifications'],
            'stats' => $this['stats'],
        ];
    }
}
