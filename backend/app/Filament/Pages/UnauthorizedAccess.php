<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class UnauthorizedAccess extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static string $view = 'filament.pages.unauthorized-access';

    protected static ?string $slug = 'unauthorized';

    protected static bool $shouldRegisterNavigation = false;

    public string $message = 'You do not have permission to access this page.';

    public function getTitle(): string
    {
        return 'Unauthorized';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        $this->message = (string) (request()->query('message')
            ?: 'You do not have permission to access this page.');
    }
}
