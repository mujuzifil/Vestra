<?php

namespace App\Http\Requests\Api\V1\Distributor;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'permissions_json' => ['sometimes', 'array'],
            'permissions_json.*' => ['string', 'in:orders,quotes,invoices,payments,branches,documents'],
            'is_primary' => ['boolean'],
        ];
    }
}
