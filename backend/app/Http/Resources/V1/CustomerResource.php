<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'gender' => $this->gender,
            'avatar_url' => $this->avatarUrl(),
            'preferences' => $this->whenLoaded('preferences', fn () => new PreferenceResource($this->preferences)),
            'is_admin' => $this->isAdmin(),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'must_change_password' => $this->mustChangePassword(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
