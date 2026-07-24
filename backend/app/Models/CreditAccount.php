<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'limit',
        'balance',
        'authorized_amount',
        'status',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'limit' => 'decimal:2',
            'balance' => 'decimal:2',
            'authorized_amount' => 'decimal:2',
        ];
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function availableCredit(): float
    {
        return max(0, (float) $this->limit - (float) $this->balance - (float) $this->authorized_amount);
    }

    public function utilizationPercentage(): float
    {
        if ((float) $this->limit <= 0) {
            return 0.0;
        }

        return min(100, ((float) $this->balance + (float) $this->authorized_amount) / (float) $this->limit * 100);
    }
}
