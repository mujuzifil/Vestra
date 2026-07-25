<?php

namespace App\Http\Resources\V1\Distributor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistributorProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'trading_name' => $this->trading_name,
            'registration_number' => $this->registration_number,
            'tax_identification' => $this->tax_identification,
            'vat_number' => $this->vat_number,
            'business_type' => $this->business_type,
            'industry' => $this->industry,
            'years_in_business' => $this->years_in_business,
            'company_size' => $this->company_size,
            'website' => $this->website,
            'primary_contact_name' => $this->primary_contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country' => $this->country,
            'district' => $this->district,
            'city' => $this->city,
            'address' => $this->address,
            'postal_address' => $this->postal_address,
            'logo_url' => $this->logoUrl(),
            'operating_hours_json' => $this->operating_hours_json,
            'bank_info_json' => $this->bank_info_json,
            'billing_info_json' => $this->billing_info_json,
            'expected_monthly_volume' => $this->expected_monthly_volume,
            'products_of_interest' => $this->products_of_interest,
            'status' => $this->status,
            'approved_at' => $this->approved_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'credit_account' => $this->whenLoaded('creditAccount', fn () => new CreditAccountResource($this->creditAccount)),
        ];
    }
}
