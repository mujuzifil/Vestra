<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Enums\ProductStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CategoryAdminService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateCategories(array $filters = [], string $sort = 'sort_order', string $direction = 'asc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryCategories($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function queryCategories(array $filters = [], string $sort = 'sort_order', string $direction = 'asc'): Builder
    {
        $query = Category::query()
            ->withCount('products')
            ->when($filters['search'] ?? null, function (Builder $q, string $term): Builder {
                return $q->where(function (Builder $inner) use ($term): void {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, array $statuses) => $q->whereIn('status', $statuses))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['date_until'] ?? null, fn (Builder $q, string $until) => $q->whereDate('created_at', '<=', $until));

        return $this->applySorting($query, $sort, $direction);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(): array
    {
        $total = Category::query()->count();
        $active = Category::query()->where('status', 'active')->count();
        $withProducts = Category::query()->has('products')->count();
        $empty = Category::query()->doesntHave('products')->count();

        return [
            $this->buildCard('Total', $total, 'heroicon-o-tag', 'primary'),
            $this->buildCard('Active', $active, 'heroicon-o-check-circle', 'success'),
            $this->buildCard('With products', $withProducts, 'heroicon-o-shopping-bag', 'info'),
            $this->buildCard('Empty', $empty, 'heroicon-o-inbox', 'warning'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(Category $category): array
    {
        $category->load(['products' => fn ($q) => $q->orderBy('name')]);
        $category->loadCount('products');

        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'sort_order' => $category->sort_order,
            'status' => $category->status,
            'status_label' => ucfirst((string) $category->status),
            'products_count' => (int) $category->products_count,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
            'products' => $category->products->map(function (Product $product) {
                $status = $product->status;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'status' => $status instanceof \BackedEnum ? $status->value : (string) $status,
                    'status_label' => $status instanceof ProductStatus
                        ? $status->label()
                        : ucfirst(str_replace('_', ' ', (string) $status)),
                    'stock_quantity' => $product->stock_quantity,
                    'price' => $product->price,
                ];
            })->toArray(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function exportRows(array $filters = []): array
    {
        return $this->queryCategories($filters, 'sort_order', 'asc')
            ->get()
            ->map(fn (Category $category) => [
                'name' => $category->name,
                'slug' => $category->slug,
                'status' => ucfirst((string) $category->status),
                'sort_order' => $category->sort_order,
                'products_count' => (int) $category->products_count,
                'description' => $category->description ?? '',
                'created_at' => $category->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $category->updated_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'name' => $query->orderBy('name', $direction),
            'slug' => $query->orderBy('slug', $direction),
            'status' => $query->orderBy('status', $direction),
            'sort_order' => $query->orderBy('sort_order', $direction)->orderBy('name', 'asc'),
            'products_count' => $query->orderBy('products_count', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            'updated_at' => $query->orderBy('updated_at', $direction),
            default => $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc'),
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
            'trend_label' => 'No comparison available',
            'trend_positive' => true,
            'trend_available' => false,
        ];
    }
}
