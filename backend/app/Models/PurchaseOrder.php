<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'warehouse_id',
        'user_id',
        'status',
        'total',
        'notes',
        'ordered_at',
        'expected_at',
        'received_at',
    ];

    protected $casts = [
        'status' => PurchaseOrderStatus::class,
        'total' => 'decimal:2',
        'ordered_at' => 'date',
        'expected_at' => 'date',
        'received_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', [
            PurchaseOrderStatus::ORDERED->value,
            PurchaseOrderStatus::PARTIAL->value,
        ]);
    }

    public function isFullyReceived(): bool
    {
        return $this->items->every(fn ($item) => $item->received_quantity >= $item->quantity);
    }
}
