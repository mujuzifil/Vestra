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
        ];
    }
}
