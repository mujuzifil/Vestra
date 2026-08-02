<?php

namespace App\Filament\Pages\Administration;

use Filament\Pages\Page;

class IntegrationsPage extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Integrations';

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.administration.integrations';

    protected static ?string $slug = 'administration/integrations';

    public function getTitle(): string
    {
        return 'Integrations';
    }
}
