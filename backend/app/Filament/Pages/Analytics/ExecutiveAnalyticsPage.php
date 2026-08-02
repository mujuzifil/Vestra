<?php

namespace App\Filament\Pages\Analytics;

use Filament\Pages\Page;

class ExecutiveAnalyticsPage extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?string $navigationLabel = 'Executive';

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.analytics.executive';

    protected static ?string $slug = 'analytics/executive';

    public function getTitle(): string
    {
        return 'Executive';
    }
}
