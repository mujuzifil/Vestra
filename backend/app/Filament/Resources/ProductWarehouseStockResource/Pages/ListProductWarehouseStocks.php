<?php

namespace App\Filament\Resources\ProductWarehouseStockResource\Pages;

use App\Filament\Pages\Products\InventoryPage;
use App\Filament\Resources\ProductWarehouseStockResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Legacy warehouse stock index — permanently deferred to Products → Inventory.
 */
class ListProductWarehouseStocks extends ListRecords
{
    protected static string $resource = ProductWarehouseStockResource::class;

    public function mount(): void
    {
        $this->redirect(InventoryPage::getUrl(), navigate: true);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
