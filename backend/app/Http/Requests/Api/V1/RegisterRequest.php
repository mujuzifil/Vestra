<?php

namespace App\Http\Requests\Api\V1;

use App\Services\AuditService;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->hasAny(['is_admin', 'role', 'roles', 'status'])) {
            AuditService::log(
                null,
                'privilege_escalation_attempt',
                null,
                [
                    'email' => $this->input('email'),
                    'fields' => $this->only(['is_admin', 'role', 'roles', 'status']),
                    'ip' => $this->ip(),
                ],
                $this->ip(),
                $this->userAgent()
            );
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['prohibited'],
            'role' => ['prohibited'],
            'roles' => ['prohibited'],
            'status' => ['prohibited'],
        ];
    }
}
