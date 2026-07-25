<?php

namespace App\Filament\Resources\DistributorProductPriceResource\Pages;

use App\Filament\Resources\DistributorProductPriceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDistributorProductPrices extends ListRecords
{
    protected static string $resource = DistributorProductPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
