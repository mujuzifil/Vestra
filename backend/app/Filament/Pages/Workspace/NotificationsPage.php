<?php

namespace App\Filament\Pages\Workspace;

use Filament\Pages\Page;

class NotificationsPage extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Workspace';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.workspace.notifications';

    protected static ?string $slug = 'workspace/notifications';

    public function getTitle(): string
    {
        return 'Notifications';
    }
}
