<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PreferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource === null) {
            return [
                'notification_preferences' => null,
                'account_preferences' => null,
            ];
        }

        return [
            'notification_preferences' => $this->notification_preferences ?: new \stdClass(),
            'account_preferences' => $this->account_preferences ?: new \stdClass(),
            'system_alerts' => $this->system_alerts,
            'emergency_alerts' => $this->emergency_alerts,
            'updated_at' => $this->updated_at,
        ];
    }
}
