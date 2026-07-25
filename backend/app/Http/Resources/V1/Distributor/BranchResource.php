<?php

namespace App\Http\Resources\V1\Distributor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'manager_name' => $this->manager_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'country' => $this->country,
            'district' => $this->district,
            'city' => $this->city,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'delivery_notes' => $this->delivery_notes,
            'is_default' => $this->is_default,
            'status' => $this->status,
            'formatted_address' => $this->formattedAddress(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
