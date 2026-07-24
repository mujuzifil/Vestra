<?php

namespace App\Http\Requests\Api\V1\Distributor;

use Illuminate\Foundation\Http\FormRequest;

class UploadLogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ];
    }
}
