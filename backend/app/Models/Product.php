<?php

namespace App\Models;

use App\Enums\ProductStatus;
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
        'featured',
        'status',
        'stock_quantity',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'featured' => 'boolean',
            'stock_quantity' => 'integer',
            'status' => ProductStatus::class,
            'features' => 'array',
            'benefits' => 'array',
            'specifications' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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
        return $query->where('stock_quantity', '<=', 10)->where('stock_quantity', '>', 0);
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

    public function stockStatusLabel(): string
    {
        return match (true) {
            $this->stock_quantity === 0 => 'Out of Stock',
            $this->stock_quantity <= 5 => 'Low Stock',
            $this->stock_quantity <= 10 => 'Running Low',
            default => 'In Stock',
        };
    }

    public function stockStatusColor(): string
    {
        return match (true) {
            $this->stock_quantity === 0 => 'danger',
            $this->stock_quantity <= 5 => 'danger',
            $this->stock_quantity <= 10 => 'warning',
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
