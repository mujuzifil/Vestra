<?php

namespace App\Repositories;

use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    public function __construct(private readonly Product $model) {}

    public function paginateActive(int $perPage = 12, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['category', 'images'])
            ->where('status', ProductStatus::ACTIVE);

        if (!empty($filters['category'])) {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->where('slug', $filters['category']);
            });
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['featured'])) {
            $query->where('featured', true);
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', (float) $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', (float) $filters['max_price']);
        }

        $sort = $filters['sort'] ?? null;
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'name_asc') {
            $query->orderBy('name', 'asc');
        } elseif ($sort === 'name_desc') {
            $query->orderBy('name', 'desc');
        } else {
            $query->orderBy('featured', 'desc')
                  ->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
    }

    public function findActiveBySlug(string $slug): ?Product
    {
        return $this->model->newQuery()
            ->with(['category', 'images'])
            ->where('slug', $slug)
            ->where('status', ProductStatus::ACTIVE)
            ->first();
    }

    public function getFeatured(int $limit = 6): Collection
    {
        return $this->model->newQuery()
            ->with(['category', 'images'])
            ->where('status', ProductStatus::ACTIVE)
            ->where('featured', true)
            ->limit($limit)
            ->get();
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    public function delete(Product $product): ?bool
    {
        return $product->delete();
    }
}
