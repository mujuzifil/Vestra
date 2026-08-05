<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryRepository
{
    public function __construct(private readonly Category $model) {}

    public function allActive(): Collection
    {
        return Cache::remember('catalog.categories.active', 60, function () {
            return $this->model->newQuery()
                ->with('parent:id,name,slug')
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->model->newQuery()
            ->where('slug', $slug)
            ->first();
    }

    public function create(array $data): Category
    {
        $category = $this->model->create($data);
        $this->forgetCatalogCache();

        return $category;
    }

    public function update(Category $category, array $data): bool
    {
        $updated = $category->update($data);
        $this->forgetCatalogCache();

        return $updated;
    }

    public function delete(Category $category): ?bool
    {
        $deleted = $category->delete();
        $this->forgetCatalogCache();

        return $deleted;
    }

    public function forgetCatalogCache(): void
    {
        Cache::forget('catalog.categories.active');
    }
}
