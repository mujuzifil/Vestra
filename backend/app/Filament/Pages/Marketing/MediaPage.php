<?php

namespace App\Filament\Pages\Marketing;

use Filament\Pages\Page;

class MediaPage extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'Media';

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.marketing.media';

    protected static ?string $slug = 'marketing/media';

    public function getTitle(): string
    {
        return 'Media';
    }
}
