<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductWarehouseStock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class InventoryAdminService
{
    public const STOCK_STATUSES = ['in', 'low', 'out'];

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateStock(array $filters = [], string $sort = 'updated_at', string $direction = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryStock($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function queryStock(array $filters = [], string $sort = 'updated_at', string $direction = 'desc'): Builder
    {
        $query = ProductWarehouseStock::query()
            ->with([
                'product.category',
                'product.images',
                'warehouse',
            ])
            ->when($filters['search'] ?? null, function (Builder $q, string $term): void {
                $q->whereHas('product', function (Builder $product) use ($term): void {
                    $product->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%");
                });
            })
            ->when($filters['warehouse'] ?? null, function (Builder $q, array $warehouseIds): void {
                $q->whereIn('warehouse_id', array_map('intval', $warehouseIds));
            })
            ->when($filters['category'] ?? null, function (Builder $q, array $categoryIds): void {
                $q->whereHas('product', fn (Builder $product) => $product->whereIn('category_id', array_map('intval', $categoryIds)));
            })
            ->when($filters['stock_status'] ?? null, function (Builder $q, array $statuses): void {
                $statuses = array_values(array_intersect($statuses, self::STOCK_STATUSES));

                if ($statuses === []) {
                    return;
                }

                $q->where(function (Builder $inner) use ($statuses): void {
                    foreach ($statuses as $status) {
                        match ($status) {
                            'out' => $inner->orWhereRaw('(quantity - reserved_quantity) <= 0'),
                            'low' => $inner->orWhereRaw('(quantity - reserved_quantity) > 0 AND (quantity - reserved_quantity) <= reorder_level'),
                            'in' => $inner->orWhereRaw('(quantity - reserved_quantity) > reorder_level'),
                            default => null,
                        };
                    }
                });
            })
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('updated_at', '>=', $from))
            ->when($filters['date_until'] ?? null, fn (Builder $q, string $until) => $q->whereDate('updated_at', '<=', $until));

        return $this->applySorting($query, $sort, $direction);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(): array
    {
        /** @var Collection<int, ProductWarehouseStock> $stocks */
        $stocks = ProductWarehouseStock::query()
            ->with(['product:id,price'])
            ->get();

        $inventoryValue = (float) $stocks->sum(function (ProductWarehouseStock $stock): float {
            return (float) $stock->quantity * (float) ($stock->product?->price ?? 0);
        });

        $totalUnits = (int) $stocks->sum(fn (ProductWarehouseStock $stock): int => (int) $stock->quantity);

        $lowStock = $stocks->filter(function (ProductWarehouseStock $stock): bool {
            $available = $stock->availableQuantity();

            return $available > 0 && $available <= (int) $stock->reorder_level;
        })->count();

        $outOfStock = $stocks->filter(
            fn (ProductWarehouseStock $stock): bool => $stock->availableQuantity() <= 0
        )->count();

        $movementCount = StockMovement::query()->count();

        return [
            $this->buildValueCard('Inventory Value', $inventoryValue, 'heroicon-o-banknotes', 'primary'),
            $this->buildCountCard('Total Units', $totalUnits, 'heroicon-o-cube', 'info'),
            $this->buildCountCard('Low Stock', $lowStock, 'heroicon-o-exclamation-triangle', 'warning'),
            $this->buildCountCard('Out of Stock', $outOfStock, 'heroicon-o-x-circle', 'danger'),
            $this->buildCountCard('Movements', $movementCount, 'heroicon-o-arrows-right-left', 'gray'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(ProductWarehouseStock $stock): array
    {
        $stock->load(['product.category', 'product.images', 'warehouse']);

        $status = $this->resolveStockStatus($stock);
        $value = (float) $stock->quantity * (float) ($stock->product?->price ?? 0);

        $movements = StockMovement::query()
            ->where('product_id', $stock->product_id)
            ->where('warehouse_id', $stock->warehouse_id)
            ->with('user')
            ->latest()
            ->limit(25)
            ->get()
            ->map(fn (StockMovement $movement) => [
                'id' => $movement->id,
                'type' => $movement->type?->value,
                'type_label' => $movement->type?->label() ?? '—',
                'type_color' => $movement->type?->color() ?? 'gray',
                'quantity' => (int) $movement->quantity,
                'balance_after' => (int) $movement->balance_after,
                'reason' => $movement->reason,
                'notes' => $movement->notes,
                'user_name' => $movement->user?->name,
                'created_at' => $movement->created_at,
            ])
            ->toArray();

        $primaryImage = $stock->product?->images?->first();

        return [
            'id' => $stock->id,
            'quantity' => (int) $stock->quantity,
            'reserved_quantity' => (int) $stock->reserved_quantity,
            'reorder_level' => (int) $stock->reorder_level,
            'available_quantity' => $stock->availableQuantity(),
            'value' => $value,
            'value_formatted' => $this->formatCurrency($value),
            'stock_status' => $status['key'],
            'stock_status_label' => $status['label'],
            'stock_status_color' => $status['color'],
            'updated_at' => $stock->updated_at,
            'created_at' => $stock->created_at,
            'product' => $stock->product ? [
                'id' => $stock->product->id,
                'name' => $stock->product->name,
                'sku' => $stock->product->sku,
                'price' => (float) $stock->product->price,
                'price_formatted' => $this->formatCurrency((float) $stock->product->price),
                'category' => $stock->product->category?->name,
                'image' => $primaryImage?->image
                    ? asset('storage/'.$primaryImage->image)
                    : null,
            ] : null,
            'warehouse' => $stock->warehouse ? [
                'id' => $stock->warehouse->id,
                'name' => $stock->warehouse->name,
                'code' => $stock->warehouse->code,
                'address' => $stock->warehouse->address,
                'is_active' => (bool) $stock->warehouse->is_active,
            ] : null,
            'movements' => $movements,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function exportRows(array $filters = []): array
    {
        return $this->queryStock($filters, 'updated_at', 'desc')
            ->get()
            ->map(function (ProductWarehouseStock $stock) {
                $status = $this->resolveStockStatus($stock);
                $value = (float) $stock->quantity * (float) ($stock->product?->price ?? 0);

                return [
                    'product' => $stock->product?->name ?? '—',
                    'sku' => $stock->product?->sku ?? '—',
                    'category' => $stock->product?->category?->name ?? '—',
                    'warehouse' => $stock->warehouse?->name ?? '—',
                    'warehouse_code' => $stock->warehouse?->code ?? '—',
                    'quantity' => (int) $stock->quantity,
                    'reserved' => (int) $stock->reserved_quantity,
                    'available' => $stock->availableQuantity(),
                    'reorder_level' => (int) $stock->reorder_level,
                    'value' => round($value, 2),
                    'status' => $status['label'],
                    'updated_at' => $stock->updated_at?->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        return [
            'warehouses' => Warehouse::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'code'])
                ->map(fn (Warehouse $warehouse) => [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'code' => $warehouse->code,
                ])
                ->toArray(),
            'categories' => Category::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ])
                ->toArray(),
            'stock_statuses' => self::STOCK_STATUSES,
        ];
    }

    /**
     * @return array{key: string, label: string, color: string}
     */
    public function resolveStockStatus(ProductWarehouseStock $stock): array
    {
        $available = $stock->availableQuantity();

        if ($available <= 0) {
            return ['key' => 'out', 'label' => 'Out of Stock', 'color' => 'danger'];
        }

        if ($available <= (int) $stock->reorder_level) {
            return ['key' => 'low', 'label' => 'Low Stock', 'color' => 'warning'];
        }

        return ['key' => 'in', 'label' => 'In Stock', 'color' => 'success'];
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'quantity' => $query->orderBy('quantity', $direction),
            'reserved_quantity' => $query->orderBy('reserved_quantity', $direction),
            'available' => $query->orderByRaw('(quantity - reserved_quantity) '.$direction),
            'reorder_level' => $query->orderBy('reorder_level', $direction),
            'value' => $query->orderBy(
                Product::selectRaw('price * product_warehouse_stock.quantity')
                    ->whereColumn('products.id', 'product_warehouse_stock.product_id')
                    ->limit(1),
                $direction
            ),
            'product' => $query->orderBy(
                Product::select('name')
                    ->whereColumn('products.id', 'product_warehouse_stock.product_id')
                    ->limit(1),
                $direction
            ),
            'sku' => $query->orderBy(
                Product::select('sku')
                    ->whereColumn('products.id', 'product_warehouse_stock.product_id')
                    ->limit(1),
                $direction
            ),
            'warehouse' => $query->orderBy(
                Warehouse::select('name')
                    ->whereColumn('warehouses.id', 'product_warehouse_stock.warehouse_id')
                    ->limit(1),
                $direction
            ),
            'updated_at' => $query->orderBy('updated_at', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            default => $query->orderBy('updated_at', 'desc'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildValueCard(string $label, float $amount, string $icon, string $color): array
    {
        return [
            'label' => $label,
            'value' => $this->formatCurrency($amount),
            'icon' => $icon,
            'color' => $color,
            'trend' => '—',
            'trend_label' => 'Point-in-time valuation',
            'trend_positive' => true,
            'trend_available' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCountCard(string $label, int $count, string $icon, string $color): array
    {
        return [
            'label' => $label,
            'value' => number_format($count),
            'icon' => $icon,
            'color' => $color,
            'trend' => '—',
            'trend_label' => 'Live stock levels',
            'trend_positive' => true,
            'trend_available' => false,
        ];
    }

    private function formatCurrency(float $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return 'UGX '.number_format($amount / 1_000_000_000, 2).'B';
        }

        if ($amount >= 1_000_000) {
            return 'UGX '.number_format($amount / 1_000_000, 1).'M';
        }

        if ($amount >= 1_000) {
            return 'UGX '.number_format($amount / 1_000, 1).'K';
        }

        return 'UGX '.number_format($amount, 0);
    }
}
