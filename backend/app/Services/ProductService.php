<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    public function __construct(private readonly ProductRepository $repository) {}

    public function listActive(int $perPage = 12, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginateActive($perPage, $filters);
    }

    public function findActiveBySlug(string $slug): ?Product
    {
        return $this->repository->findActiveBySlug($slug);
    }

    public function featured(int $limit = 6): Collection
    {
        return $this->repository->getFeatured($limit);
    }

    public function create(array $data): Product
    {
        return $this->repository->create($data);
    }

    public function update(Product $product, array $data): bool
    {
        return $this->repository->update($product, $data);
    }

    public function delete(Product $product): ?bool
    {
        return $this->repository->delete($product);
    }
}
