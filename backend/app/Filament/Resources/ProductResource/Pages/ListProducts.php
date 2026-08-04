<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Pages\Products\ProductsPage;
use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Legacy Products index — permanently deferred to Products → Catalog workspace.
 */
class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    public function mount(): void
    {
        $this->redirect(ProductsPage::getUrl(), navigate: true);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
