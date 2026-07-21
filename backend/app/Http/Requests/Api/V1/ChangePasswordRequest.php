<?php

namespace App\Http\Requests\Api\V1;

use App\Services\AuditService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $user = $this->user();

        if ($user) {
            AuditService::log(
                $user,
                'password_policy_violation',
                $user,
                [
                    'errors' => $validator->errors()->toArray(),
                    'source' => 'api_change_password',
                ],
                $this->ip(),
                $this->userAgent()
            );
        }

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
