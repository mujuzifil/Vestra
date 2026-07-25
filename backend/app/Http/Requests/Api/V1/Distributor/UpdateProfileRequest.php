<?php

namespace App\Http\Requests\Api\V1\Distributor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'trading_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'registration_number' => ['sometimes', 'nullable', 'string', 'max:100'],
            'tax_identification' => ['sometimes', 'nullable', 'string', 'max:100'],
            'vat_number' => ['sometimes', 'nullable', 'string', 'max:100'],
            'business_type' => ['sometimes', 'nullable', 'string', 'max:100'],
            'industry' => ['sometimes', 'nullable', 'string', 'max:100'],
            'years_in_business' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'company_size' => ['sometimes', 'nullable', 'string', 'max:100'],
            'website' => ['sometimes', 'nullable', 'string', 'url', 'max:255'],
            'primary_contact_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:50'],
            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'district' => ['sometimes', 'nullable', 'string', 'max:100'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'postal_address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'operating_hours_json' => ['sometimes', 'nullable', 'array'],
            'bank_info_json' => ['sometimes', 'nullable', 'array'],
            'billing_info_json' => ['sometimes', 'nullable', 'array'],
            'expected_monthly_volume' => ['sometimes', 'nullable', 'string', 'max:100'],
            'products_of_interest' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
