<?php

namespace App\Filament\Pages\Analytics;

use Filament\Pages\Page;

class SalesAnalyticsPage extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?string $navigationLabel = 'Sales';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.analytics.sales';

    protected static ?string $slug = 'analytics/sales';

    public function getTitle(): string
    {
        return 'Sales';
    }
}
