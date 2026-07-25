<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'reference_number',
        'status',
        'notes',
        'admin_notes',
        'submitted_at',
        'quoted_at',
        'expires_at',
        'subtotal',
        'tax_amount',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuotationStatus::class,
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'quoted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [QuotationStatus::DRAFT, QuotationStatus::SUBMITTED], true);
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
