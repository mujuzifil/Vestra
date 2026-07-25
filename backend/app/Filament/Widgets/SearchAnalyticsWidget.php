<?php

namespace App\Filament\Widgets;

use App\Services\ReportService;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Facades\Cache;

class SearchAnalyticsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $data = Cache::remember('admin.widgets.search_analytics', 3600, function (): array {
            return app(ReportService::class)->searchConversionMetrics(30);
        });

        return [
            StatsOverviewWidget\Stat::make('Total Searches', number_format($data['total_searches']))
                ->description('Last 30 days')
                ->icon('heroicon-m-magnifying-glass')
                ->color('primary'),

            StatsOverviewWidget\Stat::make('Zero-Result Rate', $data['zero_result_rate'] . '%')
                ->description($data['zero_result_searches'] . ' searches with no results')
                ->icon('heroicon-m-x-circle')
                ->color('warning'),

            StatsOverviewWidget\Stat::make('Click-Through Rate', $data['click_through_rate'] . '%')
                ->description($data['clicks'] . ' product clicks')
                ->icon('heroicon-m-cursor-arrow-rays')
                ->color('info'),

            StatsOverviewWidget\Stat::make('Search Conversion Rate', $data['conversion_rate'] . '%')
                ->description($data['conversions'] . ' conversions')
                ->icon('heroicon-m-shopping-bag')
                ->color('success'),
        ];
    }
}
