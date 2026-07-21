<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnifiedLoginResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'id' => $this->resource['user']->id,
                'name' => $this->resource['user']->name,
                'email' => $this->resource['user']->email,
                'phone' => $this->resource['user']->phone,
                'is_admin' => $this->resource['user']->isAdmin(),
                'roles' => $this->resource['user']->roles->pluck('name'),
                'must_change_password' => $this->resource['user']->mustChangePassword(),
                'created_at' => $this->resource['user']->created_at,
                'updated_at' => $this->resource['user']->updated_at,
            ],
            'token' => $this->resource['token'],
            'exchange_token' => $this->resource['exchange_token'] ?? null,
            'role' => $this->resource['role'],
            'redirect_to' => $this->resource['redirect_to'],
            'must_change_password' => $this->resource['user']->mustChangePassword(),
        ];
    }
}
