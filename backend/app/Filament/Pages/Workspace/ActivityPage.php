<?php

namespace App\Filament\Pages\Workspace;

use Filament\Pages\Page;

class ActivityPage extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Workspace';

    protected static ?string $navigationLabel = 'Activity';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.workspace.activity';

    protected static ?string $slug = 'activity';

    public function getTitle(): string
    {
        return 'Activity';
    }
}
