<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\ProductWarehouseStock;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Facades\Cache;

class InventoryValueWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $inventoryValue = Cache::remember('admin.inventory.total_value', 300, function (): float {
            return ProductWarehouseStock::query()
                ->join('products', 'products.id', '=', 'product_warehouse_stock.product_id')
                ->selectRaw('SUM(product_warehouse_stock.quantity * products.price) as value')
                ->value('value') ?? 0;
        });

        $lowStockCount = Cache::remember('admin.inventory.low_stock_count', 300, function (): int {
            return ProductWarehouseStock::query()
                ->whereColumn('quantity', '<=', 'reorder_level')
                ->count();
        });

        $outOfStockCount = Cache::remember('admin.inventory.out_of_stock_count', 300, function (): int {
            return ProductWarehouseStock::query()
                ->where('quantity', 0)
                ->count();
        });

        $warehouseCount = Cache::remember('admin.inventory.warehouse_count', 300, function (): int {
            return \App\Models\Warehouse::active()->count();
        });

        return [
            StatsOverviewWidget\Stat::make('Inventory Value', 'UGX ' . number_format($inventoryValue))
                ->description('Total value at regular price')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            StatsOverviewWidget\Stat::make('Low Stock Items', number_format($lowStockCount))
                ->description('At or below reorder level')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockCount > 0 ? 'warning' : 'gray'),

            StatsOverviewWidget\Stat::make('Out of Stock Items', number_format($outOfStockCount))
                ->description('Zero quantity in warehouses')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($outOfStockCount > 0 ? 'danger' : 'gray'),

            StatsOverviewWidget\Stat::make('Active Warehouses', number_format($warehouseCount))
                ->description('Operational storage locations')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),
        ];
    }
}
