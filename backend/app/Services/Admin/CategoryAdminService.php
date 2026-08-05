<?php

namespace App\Services\Admin;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Services\Catalog\CatalogSyncService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryAdminService
{
    public function __construct(private readonly CatalogSyncService $catalogSync) {}

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
            ->with('parent:id,name,slug')
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
    public function getFormOptions(?int $excludeId = null): array
    {
        $parents = Category::query()
            ->when($excludeId, fn (Builder $q) => $q->whereKeyNot($excludeId))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        if ($excludeId) {
            $descendantIds = $this->descendantIds($excludeId);
            $parents = $parents->reject(fn (Category $c) => in_array($c->id, $descendantIds, true));
        }

        return [
            'parents' => $parents->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
            ])->values()->toArray(),
            'statuses' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(Category $category): array
    {
        $category->load(['parent:id,name,slug', 'products' => fn ($q) => $q->orderBy('name')]);
        $category->loadCount('products');

        $publicBase = rtrim((string) config('services.frontend.public_url'), '/');
        $publicPath = '/products?category='.$category->slug;

        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'sort_order' => $category->sort_order,
            'status' => $category->status,
            'status_label' => ucfirst((string) $category->status),
            'parent' => $category->parent ? [
                'id' => $category->parent->id,
                'name' => $category->parent->name,
                'slug' => $category->parent->slug,
            ] : null,
            'parent_id' => $category->parent_id,
            'breadcrumb' => $category->breadcrumbPath(),
            'products_count' => (int) $category->products_count,
            'public_visible' => $category->isActive(),
            'public_visibility_label' => $category->isActive() ? 'Visible on public website' : 'Hidden from public website',
            'public_url' => $publicBase.$publicPath,
            'public_path' => $publicPath,
            'seo' => null,
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
            })->values()->toArray(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createCategory(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $payload = $this->preparePayload($data);
            $this->assertValidParent(null, $payload['parent_id'] ?? null);

            $category = Category::create($payload);
            $this->catalogSync->syncCategories($category->id);

            return $category->fresh(['parent']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateCategory(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            $payload = $this->preparePayload($data, $category);
            $this->assertValidParent($category->id, $payload['parent_id'] ?? null);

            $category->update($payload);
            $this->catalogSync->syncCategories($category->id);

            return $category->fresh(['parent']);
        });
    }

    public function deleteCategory(Category $category): void
    {
        if ($category->products()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'This category has products assigned and cannot be deleted.',
            ]);
        }

        if ($category->children()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'This category has subcategories and cannot be deleted.',
            ]);
        }

        DB::transaction(function () use ($category) {
            $category->delete();
            $this->catalogSync->syncCategories();
        });
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
                'parent' => $category->parent?->name,
                'status' => ucfirst((string) $category->status),
                'sort_order' => $category->sort_order,
                'products_count' => (int) $category->products_count,
                'description' => $category->description ?? '',
                'created_at' => $category->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $category->updated_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function preparePayload(array $data, ?Category $existing = null): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));

        if ($slug === '') {
            $slug = Str::slug($name);
        } else {
            $slug = Str::slug($slug);
        }

        if ($slug === '') {
            $slug = 'category';
        }

        $slug = $this->uniqueSlug($slug, $existing?->id);

        $parentId = $data['parent_id'] ?? null;
        if ($parentId === '' || $parentId === null) {
            $parentId = null;
        } else {
            $parentId = (int) $parentId;
        }

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
            'parent_id' => $parentId,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'status' => (string) ($data['status'] ?? 'active'),
        ];
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $i = 1;

        while (
            Category::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    private function assertValidParent(?int $categoryId, ?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        if ($categoryId !== null && $parentId === $categoryId) {
            throw ValidationException::withMessages([
                'form.parent_id' => 'A category cannot be its own parent.',
            ]);
        }

        $parent = Category::query()->find($parentId);
        if ($parent === null) {
            throw ValidationException::withMessages([
                'form.parent_id' => 'The selected parent category is invalid.',
            ]);
        }

        if ($categoryId !== null && in_array($parentId, $this->descendantIds($categoryId), true)) {
            throw ValidationException::withMessages([
                'form.parent_id' => 'Cannot assign a descendant as the parent category.',
            ]);
        }
    }

    /**
     * @return array<int, int>
     */
    private function descendantIds(int $categoryId): array
    {
        $ids = [];
        $frontier = Category::query()->where('parent_id', $categoryId)->pluck('id')->all();

        while ($frontier !== []) {
            $ids = array_merge($ids, $frontier);
            $frontier = Category::query()->whereIn('parent_id', $frontier)->pluck('id')->all();
        }

        return array_map('intval', $ids);
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
