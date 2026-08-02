<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\KpiCardsWidget;
use App\Filament\Widgets\MyTasksWidget;
use App\Filament\Widgets\NotificationsWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\SalesOverviewChartWidget;
use App\Filament\Widgets\UpcomingEventsWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Livewire\Attributes\Url;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Workspace';

    #[Url(as: 'range')]
    public string $dateRange = 'this-week';

    public function getTitle(): string
    {
        return 'Workspace Dashboard';
    }

    public function getWidgets(): array
    {
        return [
            KpiCardsWidget::class,
            SalesOverviewChartWidget::class,
            RecentActivityWidget::class,
            MyTasksWidget::class,
            NotificationsWidget::class,
            UpcomingEventsWidget::class,
        ];
    }
}
