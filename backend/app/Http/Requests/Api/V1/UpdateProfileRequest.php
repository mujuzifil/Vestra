<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();

        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'first_name' => ['sometimes', 'nullable', 'string', 'min:2', 'max:255'],
            'last_name' => ['sometimes', 'nullable', 'string', 'min:2', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before_or_equal:today'],
            'gender' => ['sometimes', 'nullable', 'string', 'in:male,female,other,prefer_not_to_say'],
            'status' => ['exclude'],
            'is_admin' => ['exclude'],
            'role' => ['exclude'],
            'roles' => ['exclude'],
        ];
    }
}
