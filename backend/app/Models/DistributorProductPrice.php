<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorProductPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'product_id',
        'price',
        'effective_from',
        'effective_until',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isCurrentlyEffective(?\Carbon\Carbon $date = null): bool
    {
        $date = $date ?? now();

        if ($this->effective_from && $date->lt($this->effective_from)) {
            return false;
        }

        if ($this->effective_until && $date->gt($this->effective_until)) {
            return false;
        }

        return true;
    }
}
