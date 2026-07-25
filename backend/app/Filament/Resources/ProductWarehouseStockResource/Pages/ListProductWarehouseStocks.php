<?php

namespace App\Filament\Resources\ProductWarehouseStockResource\Pages;

use App\Filament\Resources\ProductWarehouseStockResource;
use Filament\Resources\Pages\ListRecords;

class ListProductWarehouseStocks extends ListRecords
{
    protected static string $resource = ProductWarehouseStockResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
