<?php

namespace App\Filament\Widgets\Reports;

use App\Services\ReportService;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Facades\Cache;

/**
 * High-level KPI cards shown on the Reports landing dashboard.
 */
class ReportsOverviewKpiWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $service = app(ReportService::class);
        $today = now();
        $monthStart = $today->copy()->startOfMonth();

        $revenue = Cache::remember(
            'reports.overview.revenue.' . $monthStart->toDateString(),
            3600,
            fn () => $service->revenueSummary($monthStart, $today)
        );

        $sales = Cache::remember(
            'reports.overview.sales.' . $monthStart->toDateString(),
            3600,
            fn () => $service->salesSummary($monthStart, $today)
        );

        $customers = Cache::remember(
            'reports.overview.customers',
            3600,
            fn () => $service->customerSummary()
        );

        $inventory = Cache::remember(
            'reports.overview.inventory',
            3600,
            fn () => $service->inventorySummary()
        );

        $engagement = Cache::remember(
            'reports.overview.engagement',
            3600,
            fn () => $service->engagementSummary()
        );

        $distributors = Cache::remember(
            'reports.overview.distributors',
            3600,
            fn () => $service->distributorSummary()
        );

        return [
            StatsOverviewWidget\Stat::make('Revenue This Month', 'UGX ' . number_format($revenue['total_revenue']))
                ->description('Paid revenue')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            StatsOverviewWidget\Stat::make('Orders', number_format($sales['paid_orders']))
                ->description('Paid orders this month')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            StatsOverviewWidget\Stat::make('New Customers', number_format($customers['new_this_month']))
                ->description('This month')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success'),

            StatsOverviewWidget\Stat::make('Low Stock Products', number_format($inventory['low_stock']))
                ->description('Require attention')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            StatsOverviewWidget\Stat::make('Pending Reviews', number_format($engagement['pending_reviews']))
                ->description('Awaiting moderation')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            StatsOverviewWidget\Stat::make('Distributor Requests', number_format($distributors['pending_review']))
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary'),
        ];
    }
}
