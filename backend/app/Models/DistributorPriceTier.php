<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorPriceTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'min_quantity',
        'max_quantity',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function matchesQuantity(int $quantity): bool
    {
        return $quantity >= $this->min_quantity
            && ($this->max_quantity === null || $quantity <= $this->max_quantity);
    }
}
