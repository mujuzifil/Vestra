<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'distributor' => new DistributorResource($this->whenLoaded('distributor')),
            'credit_limit' => $this->limit,
            'outstanding_balance' => $this->balance,
            'available_credit' => $this->availableCredit(),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
