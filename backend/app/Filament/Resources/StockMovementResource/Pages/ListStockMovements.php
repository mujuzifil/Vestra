<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Pages\Products\InventoryPage;
use App\Filament\Resources\StockMovementResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Legacy Inventory (stock movements) index — permanently deferred to Products → Inventory.
 */
class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;

    public function mount(): void
    {
        $this->redirect(InventoryPage::getUrl(), navigate: true);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
