<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'city' => $this->city,
            'region' => $this->region,
            'district' => $this->district,
            'address_line' => $this->address_line,
            'address_line_2' => $this->address_line_2,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'delivery_notes' => $this->delivery_notes,
            'is_default' => $this->is_default,
            'is_default_shipping' => $this->is_default_shipping,
            'is_default_billing' => $this->is_default_billing,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
