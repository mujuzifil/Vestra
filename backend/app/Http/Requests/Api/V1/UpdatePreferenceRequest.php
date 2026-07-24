<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notification_preferences' => ['sometimes', 'array'],
            'account_preferences' => ['sometimes', 'array'],
        ];
    }
}
