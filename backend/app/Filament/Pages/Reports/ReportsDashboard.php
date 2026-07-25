<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Widgets\Reports\ReportsOverviewKpiWidget;
use Filament\Pages\Page;

class ReportsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Reports Dashboard';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.reports.reports-dashboard';

    public function getTitle(): string
    {
        return 'Reports & Business Intelligence';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getReportLinks(): array
    {
        return [
            [
                'label' => 'Revenue Analytics',
                'description' => 'Revenue trends, payment methods, and order status breakdowns.',
                'icon' => 'heroicon-o-banknotes',
                'route' => RevenueReport::getUrl(),
                'color' => 'primary',
            ],
            [
                'label' => 'Sales Analytics',
                'description' => 'Orders, best sellers, category performance, and cancellations.',
                'icon' => 'heroicon-o-shopping-bag',
                'route' => SalesReport::getUrl(),
                'color' => 'info',
            ],
            [
                'label' => 'Customer Analytics',
                'description' => 'Customer growth, top customers, and lifetime value.',
                'icon' => 'heroicon-o-users',
                'route' => CustomerReport::getUrl(),
                'color' => 'success',
            ],
            [
                'label' => 'Inventory Analytics',
                'description' => 'Stock levels, low stock, and product movement.',
                'icon' => 'heroicon-o-cube',
                'route' => InventoryReport::getUrl(),
                'color' => 'warning',
            ],
            [
                'label' => 'Engagement Analytics',
                'description' => 'Reviews, feedback, contact messages, and moderation.',
                'icon' => 'heroicon-o-chat-bubble-left-right',
                'route' => EngagementReport::getUrl(),
                'color' => 'danger',
            ],
            [
                'label' => 'Distributor Analytics',
                'description' => 'Applications, approvals, and geographic distribution.',
                'icon' => 'heroicon-o-truck',
                'route' => DistributorReport::getUrl(),
                'color' => 'primary',
            ],
            [
                'label' => 'Procurement Report',
                'description' => 'Purchase orders, suppliers, and committed spend.',
                'icon' => 'heroicon-o-clipboard-document-list',
                'route' => ProcurementReport::getUrl(),
                'color' => 'info',
            ],
            [
                'label' => 'Credit Report',
                'description' => 'Distributor credit limits, utilization, and transactions.',
                'icon' => 'heroicon-o-credit-card',
                'route' => CreditReport::getUrl(),
                'color' => 'warning',
            ],
            [
                'label' => 'Forecasting',
                'description' => 'Revenue, order, inventory, and growth forecasts.',
                'icon' => 'heroicon-o-arrow-trending-up',
                'route' => ForecastReport::getUrl(),
                'color' => 'success',
            ],
            [
                'label' => 'Customer Intelligence',
                'description' => 'Segments, lifetime value, retention, churn, and regions.',
                'icon' => 'heroicon-o-user-group',
                'route' => CustomerIntelligenceReport::getUrl(),
                'color' => 'primary',
            ],
            [
                'label' => 'Distributor Intelligence',
                'description' => 'Wholesale revenue, credit utilization, and performance.',
                'icon' => 'heroicon-o-building-office',
                'route' => DistributorIntelligenceReport::getUrl(),
                'color' => 'info',
            ],
            [
                'label' => 'Inventory Intelligence',
                'description' => 'Turnover, category valuation, warehouses, and dead stock.',
                'icon' => 'heroicon-o-cube-transparent',
                'route' => InventoryIntelligenceReport::getUrl(),
                'color' => 'warning',
            ],
            [
                'label' => 'API Analytics',
                'description' => 'Request volume, endpoints, latency, errors, and auth failures.',
                'icon' => 'heroicon-o-signal',
                'route' => ApiAnalyticsReport::getUrl(),
                'color' => 'primary',
            ],
            [
                'label' => 'Operational Monitoring',
                'description' => 'Queue health, scheduler, storage, cache, and notifications.',
                'icon' => 'heroicon-o-server-stack',
                'route' => OperationalMonitoringReport::getUrl(),
                'color' => 'danger',
            ],
        ];
    }
}
