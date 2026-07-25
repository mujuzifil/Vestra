<?php

namespace App\Filament\Widgets;

use App\Services\ForecastingService;
use App\Services\ReportService;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Facades\Cache;

class InventoryIntelligenceWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $data = Cache::remember('admin.widgets.inventory_intelligence', 3600, function (): array {
            $service = app(ReportService::class);
            $summary = $service->inventorySummary();
            $forecast = app(ForecastingService::class)->inventoryForecast(30);
            $deadStock = $service->deadStock(90, 1);

            return [
                'total_value' => $summary['total_value'] ?? 0,
                'low_stock' => $summary['low_stock'] ?? 0,
                'out_of_stock' => $summary['out_of_stock'] ?? 0,
                'at_risk_stockouts' => $forecast['total_at_risk'] ?? 0,
                'top_dead_stock_value' => $deadStock[0]['stock_value'] ?? 0,
            ];
        });

        return [
            StatsOverviewWidget\Stat::make('Inventory Value', 'UGX ' . number_format($data['total_value']))
                ->description('Total stock value')
                ->icon('heroicon-m-cube')
                ->color('primary'),

            StatsOverviewWidget\Stat::make('Low Stock Items', number_format($data['low_stock']))
                ->description('Below reorder threshold')
                ->icon('heroicon-m-arrow-down')
                ->color('warning'),

            StatsOverviewWidget\Stat::make('Out of Stock', number_format($data['out_of_stock']))
                ->description('Need immediate restock')
                ->icon('heroicon-m-x-circle')
                ->color('danger'),

            StatsOverviewWidget\Stat::make('At-Risk Stockouts', number_format($data['at_risk_stockouts']))
                ->description('Projected within 30 days')
                ->icon('heroicon-m-exclamation-triangle')
                ->color('warning'),
        ];
    }
}
