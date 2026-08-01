<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicDistributorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'trading_name' => $this->trading_name,
            'primary_contact_name' => $this->primary_contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'business_type' => $this->business_type,
            'district' => $this->district,
            'city' => $this->city,
            'address' => $this->address,
            'operating_hours' => $this->operating_hours_json,
            'logo_url' => $this->logoUrl(),
            'branch' => new PublicDistributorBranchResource($this->whenLoaded('defaultBranch')),
            'service_areas' => $this->whenLoaded('serviceAreas', function () {
                return $this->serviceAreas->map(fn ($area) => [
                    'region' => $area->region,
                    'district' => $area->district,
                    'status' => $area->status,
                ]);
            }),
        ];
    }
}
