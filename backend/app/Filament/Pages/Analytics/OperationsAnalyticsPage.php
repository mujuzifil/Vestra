<?php

namespace App\Filament\Pages\Analytics;

use Filament\Pages\Page;

class OperationsAnalyticsPage extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?string $navigationLabel = 'Operations';

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.analytics.operations';

    protected static ?string $slug = 'analytics/operations';

    public function getTitle(): string
    {
        return 'Operations';
    }
}
