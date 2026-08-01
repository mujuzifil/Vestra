<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreDistributorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'min:2', 'max:255'],
            'contact_person' => ['required', 'string', 'min:2', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'min:7', 'max:50'],
            'district' => ['required', 'string', 'min:2', 'max:255'],
            'physical_address' => ['required', 'string', 'min:5', 'max:1000'],
            'years_in_business' => ['nullable', 'integer', 'min:0', 'max:100'],
            'business_type' => ['required', 'string', 'min:2', 'max:255'],
            'regions_covered' => ['required', 'string', 'min:2', 'max:1000'],
            'existing_brands' => ['nullable', 'string', 'max:1000'],
            'warehouse_availability' => ['nullable', 'string', 'max:255'],
            'delivery_capability' => ['nullable', 'string', 'max:255'],
            'additional_information' => ['nullable', 'string', 'max:5000'],
            'documents' => ['nullable', 'array', 'max:5'],
            'documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
