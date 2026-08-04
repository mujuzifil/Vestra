<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Pages\Products\CategoriesPage;
use App\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Legacy Categories index — permanently deferred to Products → Categories.
 */
class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    public function mount(): void
    {
        $this->redirect(CategoriesPage::getUrl(), navigate: true);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
