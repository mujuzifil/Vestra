<?php

namespace App\Filament\Widgets;

use App\Services\ReportService;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Facades\Cache;

class CustomerIntelligenceWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $data = Cache::remember('admin.widgets.customer_intelligence', 3600, function (): array {
            $service = app(ReportService::class);
            $segments = $service->customerSegments();
            $summary = $service->customerSummary();
            $clv = $service->customerLifetimeValue(1);

            return [
                'vip' => collect($segments)->firstWhere('segment', 'VIP')['count'] ?? 0,
                'at_risk' => collect($segments)->firstWhere('segment', 'At Risk')['count'] ?? 0,
                'repeat_percentage' => $summary['total_customers'] > 0
                    ? round(($summary['repeat_customers'] / $summary['total_customers']) * 100, 1)
                    : 0,
                'top_clv' => $clv[0]['clv'] ?? 0,
            ];
        });

        return [
            StatsOverviewWidget\Stat::make('VIP Customers', number_format($data['vip']))
                ->description('Highest value segment')
                ->icon('heroicon-m-trophy'),

            StatsOverviewWidget\Stat::make('At-Risk Customers', number_format($data['at_risk']))
                ->description('No recent activity')
                ->icon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            StatsOverviewWidget\Stat::make('Repeat Customer Rate', $data['repeat_percentage'] . '%')
                ->description('Of total customer base')
                ->icon('heroicon-m-arrow-path')
                ->color('success'),

            StatsOverviewWidget\Stat::make('Top Customer CLV', 'UGX ' . number_format($data['top_clv']))
                ->description('Highest lifetime value')
                ->icon('heroicon-m-currency-dollar')
                ->color('primary'),
        ];
    }
}
