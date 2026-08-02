<?php

namespace App\Filament\Pages\CustomerSuccess;

use Filament\Pages\Page;

class SupportPage extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Customer Success';

    protected static ?string $navigationLabel = 'Support';

    protected static ?string $navigationIcon = 'heroicon-o-lifebuoy';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.customer-success.support';

    protected static ?string $slug = 'customer-success/support';

    public function getTitle(): string
    {
        return 'Support';
    }
}
