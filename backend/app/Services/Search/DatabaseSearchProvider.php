<?php

namespace App\Services\Search;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\SearchAnalytic;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class DatabaseSearchProvider implements SearchProviderInterface
{
    public function searchProducts(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['category', 'images'])
            ->where('status', ProductStatus::ACTIVE);

        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters['sort'] ?? null);

        return $query->paginate($perPage)->appends($filters);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['category'])) {
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', $filters['category']));
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn (Builder $q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
            );
        }

        if (! empty($filters['featured'])) {
            $query->where('featured', true);
        }

        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', (float) $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', (float) $filters['max_price']);
        }

        if (! empty($filters['in_stock'])) {
            $query->where('stock_quantity', '>', 0);
        }
    }

    protected function applySorting(Builder $query, ?string $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('featured', 'desc')->orderBy('created_at', 'desc'),
        };
    }

    public function suggestions(string $term, int $limit = 8): array
    {
        $like = "%{$term}%";

        $products = Product::query()
            ->where('status', ProductStatus::ACTIVE)
            ->where(fn (Builder $q) => $q
                ->where('name', 'like', $like)
                ->orWhere('sku', 'like', $like)
            )
            ->limit($limit)
            ->get(['id', 'name', 'slug']);

        return $products->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'type' => 'product',
        ])->toArray();
    }

    public function recordSearch(string $term, int $resultsCount, ?int $userId = null, ?string $sessionId = null): void
    {
        SearchAnalytic::create([
            'term' => mb_strtolower(trim($term)),
            'user_id' => $userId,
            'session_id' => $sessionId,
            'results_count' => $resultsCount,
            'searched_at' => now(),
        ]);
    }
}
