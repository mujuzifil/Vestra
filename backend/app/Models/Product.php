<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Models\MediaAssetUsage;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Product extends Model
{
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'features',
        'benefits',
        'specifications',
        'sku',
        'price',
        'distributor_price',
        'cost_price',
        'currency',
        'cost_currency',
        'featured',
        'status',
        'stock_quantity',
        'low_stock_threshold',
        'stock_status',
        'unit',
        'weight',
        'barcode',
        'tax_rate',
        'meta_title',
        'meta_description',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'distributor_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'weight' => 'decimal:3',
            'tax_rate' => 'decimal:2',
            'featured' => 'boolean',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'status' => ProductStatus::class,
            'stock_status' => \App\Enums\ProductStockStatus::class,
            'features' => 'array',
            'benefits' => 'array',
            'specifications' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function distributorPriceTiers(): HasMany
    {
        return $this->hasMany(DistributorPriceTier::class);
    }

    public function distributorProductPrices(): HasMany
    {
        return $this->hasMany(DistributorProductPrice::class);
    }

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(ProductWarehouseStock::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (Product $product): void {
            MediaAssetUsage::query()
                ->where('usable_type', self::class)
                ->where('usable_id', $product->id)
                ->delete();
        });
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function averageRating(): float
    {
        return (float) $this->reviews()
            ->where('status', 'approved')
            ->avg('rating') ?? 0;
    }

    public function reviewCount(): int
    {
        return $this->reviews()
            ->where('status', 'approved')
            ->count();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::ACTIVE->value);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::INACTIVE->value);
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', 0);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->where(function (Builder $inner): void {
                $inner->whereNotNull('low_stock_threshold')
                    ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                    ->where('stock_quantity', '>', 0);
            })->orWhere(function (Builder $inner): void {
                $inner->whereNull('low_stock_threshold')
                    ->where('stock_quantity', '<=', 10)
                    ->where('stock_quantity', '>', 0);
            });
        });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeRecentlyUpdated(Builder $query, int $days = 7): Builder
    {
        return $query->where('updated_at', '>=', now()->subDays($days));
    }

    public function scopePriceBetween(Builder $query, ?float $min, ?float $max): Builder
    {
        return $query
            ->when($min !== null, fn (Builder $q) => $q->where('price', '>=', $min))
            ->when($max !== null, fn (Builder $q) => $q->where('price', '<=', $max));
    }

    public function resolvedStockStatus(): \App\Enums\ProductStockStatus
    {
        if ($this->stock_status instanceof \App\Enums\ProductStockStatus) {
            return $this->stock_status;
        }

        return \App\Enums\ProductStockStatus::fromQuantity(
            (int) $this->stock_quantity,
            $this->low_stock_threshold
        );
    }

    public function stockStatusLabel(): string
    {
        return $this->resolvedStockStatus()->label();
    }

    public function stockStatusColor(): string
    {
        return match ($this->resolvedStockStatus()) {
            \App\Enums\ProductStockStatus::OUT_OF_STOCK => 'danger',
            \App\Enums\ProductStockStatus::LOW_STOCK => 'warning',
            default => 'success',
        };
    }

    public static function lowStockCount(): int
    {
        return cache()->remember('admin.products.low_stock_count', 300, function (): int {
            return static::lowStock()->count();
        });
    }
}
