<?php

namespace App\Http\Requests\Api\V1\Distributor;

use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'string', 'max:50'],
            'longitude' => ['nullable', 'string', 'max:50'],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
            'is_default' => ['boolean'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
        ];
    }
}
