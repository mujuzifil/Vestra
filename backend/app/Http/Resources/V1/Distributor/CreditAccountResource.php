<?php

namespace App\Http\Resources\V1\Distributor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'limit' => (string) $this->limit,
            'balance' => (string) $this->balance,
            'authorized_amount' => (string) $this->authorized_amount,
            'available_credit' => (string) $this->availableCredit(),
            'utilization_percentage' => $this->utilizationPercentage(),
            'status' => $this->status,
        ];
    }
}
