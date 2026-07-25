<?php

namespace App\Filament\Widgets;

use App\Services\ReportService;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Facades\Cache;

class DistributorIntelligenceWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $data = Cache::remember('admin.widgets.distributor_intelligence', 3600, function (): array {
            $service = app(ReportService::class);
            $summary = $service->distributorSummary();
            $outstanding = $service->distributorOutstandingBalances();
            $revenue = $service->distributorRevenue(now()->subDays(29)->startOfDay(), now()->endOfDay());

            return [
                'pending_applications' => $summary['pending_review'] ?? 0,
                'total_outstanding' => $outstanding['total_outstanding'] ?? 0,
                'top_distributor_revenue' => $revenue[0]['revenue'] ?? 0,
                'approved_distributors' => $summary['approved'] ?? 0,
            ];
        });

        return [
            StatsOverviewWidget\Stat::make('Pending Applications', number_format($data['pending_applications']))
                ->description('Awaiting review')
                ->icon('heroicon-m-clipboard-document-list')
                ->color('warning'),

            StatsOverviewWidget\Stat::make('Outstanding Balance', 'UGX ' . number_format($data['total_outstanding']))
                ->description('Unpaid distributor invoices')
                ->icon('heroicon-m-banknotes')
                ->color('danger'),

            StatsOverviewWidget\Stat::make('Top Distributor Revenue', 'UGX ' . number_format($data['top_distributor_revenue']))
                ->description('Last 30 days')
                ->icon('heroicon-m-building-office')
                ->color('primary'),

            StatsOverviewWidget\Stat::make('Approved Distributors', number_format($data['approved_distributors']))
                ->description('Active B2B accounts')
                ->icon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
