<?php

namespace App\Http\Requests\Api\V1\Distributor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'role' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'permissions_json' => ['sometimes', 'array'],
            'permissions_json.*' => ['string', 'in:orders,quotes,invoices,payments,branches,documents'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
