<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Services\Catalog\CatalogSyncService;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepository $repository,
        private readonly CatalogSyncService $catalogSync,
    ) {}

    public function listActive(): Collection
    {
        return $this->repository->allActive();
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->repository->findBySlug($slug);
    }

    public function create(array $data): Category
    {
        $category = $this->repository->create($data);
        $this->catalogSync->syncCategories($category->id);

        return $category;
    }

    public function update(Category $category, array $data): bool
    {
        $updated = $this->repository->update($category, $data);
        $this->catalogSync->syncCategories($category->id);

        return $updated;
    }

    public function delete(Category $category): ?bool
    {
        $deleted = $this->repository->delete($category);
        $this->catalogSync->syncCategories();

        return $deleted;
    }
}
