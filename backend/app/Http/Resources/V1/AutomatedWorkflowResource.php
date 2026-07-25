<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AutomatedWorkflowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'event' => $this->event,
            'conditions' => $this->conditions,
            'action' => $this->action,
            'action_config' => $this->action_config,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'run_count' => $this->run_count,
            'last_run_at' => $this->last_run_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
