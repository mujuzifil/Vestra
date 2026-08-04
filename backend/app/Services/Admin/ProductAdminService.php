<?php

namespace App\Services\Admin;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductWarehouseStock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductAdminService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateProducts(array $filters = [], string $sort = 'created_at', string $direction = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryProducts($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function queryProducts(array $filters = [], string $sort = 'created_at', string $direction = 'desc'): Builder
    {
        $query = Product::query()
            ->with(['category', 'images'])
            ->when($filters['search'] ?? null, function (Builder $q, string $term): void {
                $q->where(function (Builder $inner) use ($term): void {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%")
                        ->orWhere('short_description', 'like', "%{$term}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, array $statuses) => $q->whereIn('status', $statuses))
            ->when($filters['category'] ?? null, fn (Builder $q, array $categories) => $q->whereIn('category_id', $categories))
            ->when(array_key_exists('featured', $filters) && $filters['featured'] !== null && $filters['featured'] !== '', function (Builder $q) use ($filters): void {
                $q->where('featured', filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN));
            })
            ->when($filters['stock'] ?? null, function (Builder $q, string $stock): void {
                match ($stock) {
                    'low' => $q->lowStock(),
                    'out' => $q->outOfStock(),
                    'in' => $q->where('stock_quantity', '>', 10),
                    default => null,
                };
            });

        return $this->applySorting($query, $sort, $direction);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(): array
    {
        return [
            $this->buildCard('Total', Product::query()->count(), 'heroicon-o-cube', 'primary'),
            $this->buildCard('Active', Product::query()->active()->count(), 'heroicon-o-check-circle', 'success'),
            $this->buildCard('Inactive', Product::query()->inactive()->count(), 'heroicon-o-pause-circle', 'warning'),
            $this->buildCard('Out of Stock', Product::query()->where('status', ProductStatus::OUT_OF_STOCK)->count(), 'heroicon-o-x-circle', 'danger'),
            $this->buildCard('Low Stock', Product::query()->lowStock()->count(), 'heroicon-o-exclamation-triangle', 'warning'),
            $this->buildCard('Categories', Category::query()->count(), 'heroicon-o-tag', 'info'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(Product $product): array
    {
        $product->load(['category', 'images', 'warehouseStocks.warehouse']);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'slug' => $product->slug,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'status' => $product->status instanceof ProductStatus ? $product->status->value : (string) $product->status,
            'status_label' => $product->status instanceof ProductStatus ? $product->status->label() : ucfirst(str_replace('_', ' ', (string) $product->status)),
            'featured' => (bool) $product->featured,
            'price' => $product->price,
            'distributor_price' => $product->distributor_price,
            'stock_quantity' => $product->stock_quantity,
            'stock_status_label' => $product->stockStatusLabel(),
            'stock_status_color' => $product->stockStatusColor(),
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
            'images' => $product->images->map(fn (ProductImage $image) => [
                'id' => $image->id,
                'url' => $this->imageUrl($image->image),
                'alt_text' => $image->alt_text,
                'sort_order' => $image->sort_order,
            ])->values()->toArray(),
            'warehouse_stocks' => $product->warehouseStocks->map(fn (ProductWarehouseStock $stock) => [
                'id' => $stock->id,
                'warehouse_name' => $stock->warehouse?->name ?? '—',
                'quantity' => $stock->quantity,
                'reserved_quantity' => $stock->reserved_quantity,
                'available' => $stock->availableQuantity(),
                'reorder_level' => $stock->reorder_level,
                'is_low' => $stock->isLowStock(),
            ])->values()->toArray(),
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
            'edit_url' => \App\Filament\Resources\ProductResource::getUrl('edit', ['record' => $product]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        return [
            'statuses' => array_map(
                fn (ProductStatus $status) => ['value' => $status->value, 'label' => $status->label()],
                ProductStatus::cases()
            ),
            'categories' => Category::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category) => ['id' => $category->id, 'name' => $category->name])
                ->values()
                ->toArray(),
            'stock_options' => [
                ['value' => 'in', 'label' => 'In Stock'],
                ['value' => 'low', 'label' => 'Low Stock'],
                ['value' => 'out', 'label' => 'Out of Stock'],
            ],
            'featured_options' => [
                ['value' => '1', 'label' => 'Featured'],
                ['value' => '0', 'label' => 'Not Featured'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function exportRows(array $filters = []): array
    {
        return $this->queryProducts($filters, 'name', 'asc')
            ->get()
            ->map(fn (Product $product) => [
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category?->name,
                'status' => $product->status instanceof ProductStatus ? $product->status->label() : (string) $product->status,
                'featured' => $product->featured ? 'Yes' : 'No',
                'price' => $product->price,
                'distributor_price' => $product->distributor_price,
                'stock_quantity' => $product->stock_quantity,
                'stock_status' => $product->stockStatusLabel(),
                'created_at' => $product->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $product->updated_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'name' => $query->orderBy('name', $direction),
            'sku' => $query->orderBy('sku', $direction),
            'status' => $query->orderBy('status', $direction),
            'price' => $query->orderBy('price', $direction),
            'distributor_price' => $query->orderBy('distributor_price', $direction),
            'stock_quantity' => $query->orderBy('stock_quantity', $direction),
            'featured' => $query->orderBy('featured', $direction),
            'category' => $query->orderBy(
                Category::select('name')
                    ->whereColumn('categories.id', 'products.category_id')
                    ->limit(1),
                $direction
            ),
            'updated_at' => $query->orderBy('updated_at', $direction),
            default => $query->orderBy('created_at', $direction),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCard(string $label, float $current, string $icon, string $color): array
    {
        return [
            'label' => $label,
            'value' => number_format($current),
            'icon' => $icon,
            'color' => $color,
            'trend' => '—',
            'trend_label' => 'Live count',
            'trend_positive' => true,
            'trend_available' => false,
        ];
    }

    private function imageUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}
