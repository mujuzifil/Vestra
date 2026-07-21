<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function __construct(private readonly CategoryRepository $repository) {}

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
        return $this->repository->create($data);
    }

    public function update(Category $category, array $data): bool
    {
        return $this->repository->update($category, $data);
    }

    public function delete(Category $category): ?bool
    {
        return $this->repository->delete($category);
    }
}
