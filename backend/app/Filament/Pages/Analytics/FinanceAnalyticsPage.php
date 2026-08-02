<?php

namespace App\Filament\Pages\Analytics;

use Filament\Pages\Page;

class FinanceAnalyticsPage extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?string $navigationLabel = 'Finance';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.analytics.finance';

    protected static ?string $slug = 'analytics/finance';

    public function getTitle(): string
    {
        return 'Finance';
    }
}
