<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AlertsWidget;
use App\Filament\Widgets\ExecutiveKpiWidget;
use App\Filament\Widgets\LowStockWidget;
use App\Filament\Widgets\OperationalKpiWidget;
use App\Filament\Widgets\OrderStatusChartWidget;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\RevenueChartWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getTitle(): string
    {
        return 'VESTRA Dashboard';
    }

    public function getWidgets(): array
    {
        return [
            ExecutiveKpiWidget::class,
            OperationalKpiWidget::class,
            QuickActionsWidget::class,
            RevenueChartWidget::class,
            OrderStatusChartWidget::class,
            RecentOrdersWidget::class,
            LowStockWidget::class,
            AlertsWidget::class,
            RecentActivityWidget::class,
        ];
    }
}
