<?php

namespace App\Models;

use App\Enums\PaymentUploadStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'amount',
        'currency',
        'reference_number',
        'file_path',
        'notes',
        'status',
        'verified_by',
        'verified_at',
        'verification_notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => PaymentUploadStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function fileUrl(): string
    {
        return asset($this->file_path);
    }

    public function statusLabel(): string
    {
        return $this->status->label();
    }

    public function statusColor(): string
    {
        return $this->status->color();
    }
}
