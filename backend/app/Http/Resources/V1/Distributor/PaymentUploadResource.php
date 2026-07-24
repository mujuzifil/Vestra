<?php

namespace App\Http\Resources\V1\Distributor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentUploadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => number_format((float) $this->amount, 2),
            'currency' => $this->currency,
            'reference_number' => $this->reference_number,
            'file_url' => $this->fileUrl(),
            'notes' => $this->notes,
            'status' => $this->mapStatusForFrontend($this->status),
            'status_label' => $this->statusLabel(),
            'status_color' => $this->statusColor(),
            'verified_at' => $this->verified_at,
            'verification_notes' => $this->verification_notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function mapStatusForFrontend(?\App\Enums\PaymentUploadStatus $status): string
    {
        return match ($status) {
            \App\Enums\PaymentUploadStatus::VERIFIED => 'verified',
            \App\Enums\PaymentUploadStatus::REJECTED => 'rejected',
            default => 'pending',
        };
    }
}
