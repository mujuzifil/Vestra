<?php

namespace App\Models;

use App\Enums\QuoteRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'full_name',
        'company_name',
        'email',
        'phone',
        'district',
        'city',
        'address',
        'preferred_delivery_date',
        'delivery_location',
        'status',
        'priority',
        'estimated_value',
        'expected_close_date',
        'source',
        'ip_address',
        'user_agent',
        'admin_notes',
        'assigned_to',
        'attachments',
        'crm_metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuoteRequestStatus::class,
            'preferred_delivery_date' => 'date',
            'expected_close_date' => 'date',
            'estimated_value' => 'decimal:2',
            'attachments' => 'array',
            'crm_metadata' => 'array',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteRequestItem::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function statusLabel(): string
    {
        return $this->status?->label() ?? ucfirst($this->status);
    }

    public function statusColor(): string
    {
        return $this->status?->color() ?? 'gray';
    }
}
