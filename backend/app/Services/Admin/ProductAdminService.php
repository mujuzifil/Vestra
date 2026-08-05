<?php

namespace App\Services\Admin;

use App\Enums\ProductStatus;
use App\Enums\ProductStockStatus;
use App\Models\Category;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductWarehouseStock;
use App\Models\Setting;
use App\Models\User;
use App\Services\Catalog\CatalogSyncService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductAdminService
{
    public function __construct(private readonly CatalogSyncService $catalogSync) {}

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
        $product->load(['category', 'images', 'warehouseStocks.warehouse', 'creator', 'updater']);

        $stockStatus = $product->resolvedStockStatus();

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
            'cost_price' => $product->cost_price,
            'currency' => $product->currency,
            'cost_currency' => $product->cost_currency,
            'distributor_price' => $product->distributor_price,
            'tax_rate' => $product->tax_rate,
            'stock_quantity' => $product->stock_quantity,
            'low_stock_threshold' => $product->low_stock_threshold,
            'stock_status' => $stockStatus->value,
            'stock_status_label' => $stockStatus->label(),
            'stock_status_color' => $product->stockStatusColor(),
            'unit' => $product->unit,
            'weight' => $product->weight,
            'barcode' => $product->barcode,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
            'images' => $product->images->map(fn (ProductImage $image) => [
                'id' => $image->id,
                'url' => $this->imageUrl($image->image),
                'path' => $image->image,
                'alt_text' => $image->alt_text,
                'sort_order' => $image->sort_order,
            ])->values()->toArray(),
            'warehouse_stocks' => $product->warehouseStocks->map(fn (ProductWarehouseStock $stock) => [
                'id' => $stock->id,
                'warehouse_name' => $stock->warehouse?->name,
                'quantity' => $stock->quantity,
                'reserved_quantity' => $stock->reserved_quantity,
                'available' => $stock->availableQuantity(),
                'reorder_level' => $stock->reorder_level,
                'is_low' => $stock->isLowStock(),
            ])->values()->toArray(),
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
            'created_by' => $product->creator?->name,
            'updated_by' => $product->updater?->name,
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
     * @return array<string, mixed>
     */
    public function getFormOptions(): array
    {
        $defaultCurrency = Setting::query()->where('key', 'currency')->value('value')
            ?? Setting::query()->where('key', 'currency_code')->value('value');

        $currencies = PaymentTransaction::query()
            ->whereNotNull('currency')
            ->where('currency', '!=', '')
            ->distinct()
            ->orderBy('currency')
            ->pluck('currency')
            ->map(fn ($code) => strtoupper((string) $code))
            ->unique()
            ->values()
            ->all();

        if (filled($defaultCurrency)) {
            array_unshift($currencies, strtoupper((string) $defaultCurrency));
            $currencies = array_values(array_unique($currencies));
        }

        $units = Product::query()
            ->whereNotNull('unit')
            ->where('unit', '!=', '')
            ->distinct()
            ->orderBy('unit')
            ->pluck('unit')
            ->values()
            ->all();

        return [
            'categories' => Category::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category) => ['id' => $category->id, 'name' => $category->name])
                ->values()
                ->toArray(),
            'statuses' => array_map(
                fn (ProductStatus $status) => ['value' => $status->value, 'label' => $status->label()],
                ProductStatus::cases()
            ),
            'stock_statuses' => ProductStockStatus::options(),
            'currencies' => array_map(fn (string $code) => ['value' => $code, 'label' => $code], $currencies),
            'units' => array_map(fn (string $unit) => ['value' => $unit, 'label' => $unit], $units),
            'default_currency' => filled($defaultCurrency) ? strtoupper((string) $defaultCurrency) : ($currencies[0] ?? null),
            'default_tax_rate' => Setting::query()->where('key', 'tax_rate')->value('value'),
            'default_low_stock_threshold' => Setting::query()->where('key', 'low_stock_threshold')->value('value') ?? 10,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     */
    public function createProduct(array $data, array $images = [], ?User $actor = null): Product
    {
        return DB::transaction(function () use ($data, $images, $actor) {
            $product = Product::create($this->preparePayload($data, $actor, creating: true));
            $this->storeImages($product, $images);
            $product = $product->fresh(['category', 'images']);
            $this->catalogSync->syncProducts($product->id, $product->category_id);

            return $product;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     */
    public function updateProduct(Product $product, array $data, array $images = [], ?User $actor = null): Product
    {
        return DB::transaction(function () use ($product, $data, $images, $actor) {
            $product->update($this->preparePayload($data, $actor, creating: false));
            $this->storeImages($product, $images);
            $product = $product->fresh(['category', 'images']);
            $this->catalogSync->syncProducts($product->id, $product->category_id);

            return $product;
        });
    }

    public function removeImage(Product $product, int $imageId): void
    {
        $image = $product->images()->whereKey($imageId)->firstOrFail();

        if (filled($image->image) && Storage::disk('public')->exists($image->image)) {
            Storage::disk('public')->delete($image->image);
        }

        $image->delete();
        $this->catalogSync->syncProducts($product->id, $product->category_id);
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
                'cost_price' => $product->cost_price,
                'distributor_price' => $product->distributor_price,
                'stock_quantity' => $product->stock_quantity,
                'stock_status' => $product->stockStatusLabel(),
                'created_at' => $product->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $product->updated_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function preparePayload(array $data, ?User $actor, bool $creating): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $stockQuantity = (int) ($data['stock_quantity'] ?? 0);
        $threshold = $data['low_stock_threshold'] !== null && $data['low_stock_threshold'] !== ''
            ? (int) $data['low_stock_threshold']
            : null;

        $stockStatus = filled($data['stock_status'] ?? null)
            ? (string) $data['stock_status']
            : ProductStockStatus::fromQuantity($stockQuantity, $threshold)->value;

        $payload = [
            'name' => $name,
            'sku' => trim((string) ($data['sku'] ?? '')),
            'short_description' => $data['short_description'] ?: null,
            'description' => $data['description'] ?: null,
            'category_id' => (int) $data['category_id'],
            'price' => $data['price'],
            'cost_price' => $data['cost_price'] !== null && $data['cost_price'] !== '' ? $data['cost_price'] : null,
            'currency' => filled($data['currency'] ?? null) ? strtoupper((string) $data['currency']) : null,
            'cost_currency' => filled($data['cost_currency'] ?? null) ? strtoupper((string) $data['cost_currency']) : null,
            'stock_quantity' => $stockQuantity,
            'low_stock_threshold' => $threshold,
            'stock_status' => $stockStatus,
            'unit' => $data['unit'] ?: null,
            'weight' => $data['weight'] !== null && $data['weight'] !== '' ? $data['weight'] : null,
            'barcode' => $data['barcode'] ?: null,
            'featured' => (bool) ($data['featured'] ?? false),
            'status' => $data['status'],
            'tax_rate' => $data['tax_rate'] !== null && $data['tax_rate'] !== '' ? $data['tax_rate'] : null,
            'updated_by' => $actor?->id,
        ];

        if ($creating) {
            $payload['slug'] = $this->uniqueSlug($name);
            $payload['created_by'] = $actor?->id;
        }

        return $payload;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $i = 1;

        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    /**
     * @param  array<int, UploadedFile>  $images
     */
    private function storeImages(Product $product, array $images): void
    {
        if ($images === []) {
            return;
        }

        $sort = (int) $product->images()->max('sort_order');

        foreach ($images as $upload) {
            if (! $upload instanceof UploadedFile) {
                continue;
            }

            $path = $upload->store('products', 'public');
            $sort++;

            ProductImage::create([
                'product_id' => $product->id,
                'image' => $path,
                'alt_text' => $product->name,
                'sort_order' => $sort,
            ]);
        }
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
