<?php

namespace App\Filament\Pages\Sales;

use Filament\Pages\Page;

class PipelinePage extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static bool $isDiscovered = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Pipeline';

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.sales.pipeline';

    protected static ?string $slug = 'sales/pipeline';

    public static function canAccess(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Pipeline';
    }
}
