<?php

namespace App\Filament\Pages\Marketing;

use Filament\Pages\Page;

class SeoPage extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'SEO';

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.marketing.seo';

    protected static ?string $slug = 'marketing/seo';

    public function getTitle(): string
    {
        return 'SEO';
    }
}
