<?php

namespace App\Http\Requests\Api\V1\Distributor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'manager_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'district' => ['sometimes', 'nullable', 'string', 'max:100'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'latitude' => ['sometimes', 'nullable', 'string', 'max:50'],
            'longitude' => ['sometimes', 'nullable', 'string', 'max:50'],
            'delivery_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'is_default' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
        ];
    }
}
