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
        'source',
        'ip_address',
        'user_agent',
        'admin_notes',
        'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuoteRequestStatus::class,
            'preferred_delivery_date' => 'date',
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
