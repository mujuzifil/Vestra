<?php

namespace App\Filament\Pages;

use App\Services\Admin\WorkspaceDataService;
use Filament\Pages\Page;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class Dashboard extends Page
{
    protected static string $routePath = '/';

    protected static ?int $navigationSort = -2;

    protected static string $layout = 'filament.layouts.crm';

    protected static string $view = 'filament.pages.workspace-dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Workspace';

    protected static ?string $navigationLabel = 'Dashboard';

    public static function getRoutePath(): string
    {
        return static::$routePath;
    }

    #[Url(as: 'range')]
    public string $dateRange = 'this-week';

    public function getTitle(): string
    {
        return 'Workspace Dashboard';
    }

    #[On('dashboard-range-changed')]
    public function updateRange(string $range): void
    {
        if (in_array($range, ['this-week', 'this-month', 'last-30-days'], true)) {
            $this->dateRange = $range;
        }
    }

    public function getWorkspaceData(): WorkspaceDataService
    {
        return app(WorkspaceDataService::class);
    }

    public function getHeroDate(): string
    {
        return now()->format('l, F j, Y');
    }
}
