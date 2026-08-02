<?php

namespace App\Filament\Pages\Sales;

use Filament\Pages\Page;

class OpportunitiesPage extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Opportunities';

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.sales.opportunities';

    protected static ?string $slug = 'sales/opportunities';

    public function getTitle(): string
    {
        return 'Opportunities';
    }
}
