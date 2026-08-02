<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'company_name' => $this->company_name,
            'industry' => $this->industry,
            'business_type' => $this->business_type,
            'tax_identification' => $this->tax_identification,
            'registration_number' => $this->registration_number,
            'website' => $this->website,
            'district' => $this->district,
            'city' => $this->city,
            'country' => $this->country,
            'address' => $this->address,
            'primary_contact_name' => $this->primary_contact_name,
            'primary_contact_phone' => $this->primary_contact_phone,
            'primary_contact_email' => $this->primary_contact_email,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
