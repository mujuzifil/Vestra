<?php

namespace App\Filament\Pages\Workspace;

use Filament\Pages\Page;

class TasksPage extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Workspace';

    protected static ?string $navigationLabel = 'Tasks';

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.workspace.tasks';

    protected static ?string $slug = 'tasks';

    public function getTitle(): string
    {
        return 'Tasks';
    }
}
