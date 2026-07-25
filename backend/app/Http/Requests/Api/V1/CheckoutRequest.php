<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\DistributorChannel;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel' => ['sometimes', 'string', Rule::enum(DistributorChannel::class)],
            'distributor_branch_id' => [
                'nullable',
                'integer',
                'exists:distributor_branches,id',
            ],
            'payment_method' => ['required', 'string', Rule::enum(PaymentMethod::class)],
            'shipping_address' => ['required', 'array'],
            'shipping_address.full_name' => ['required', 'string', 'min:2', 'max:255'],
            'shipping_address.phone' => ['required', 'string', 'max:20'],
            'shipping_address.city' => ['required', 'string', 'min:2', 'max:100'],
            'shipping_address.region' => ['nullable', 'string', 'max:100'],
            'shipping_address.district' => ['nullable', 'string', 'max:100'],
            'shipping_address.address_line' => ['required', 'string', 'min:5', 'max:500'],
            'shipping_cost' => ['sometimes', 'numeric', 'min:0', 'max:999999999.99'],
            'tax_amount' => ['sometimes', 'numeric', 'min:0', 'max:999999999.99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
