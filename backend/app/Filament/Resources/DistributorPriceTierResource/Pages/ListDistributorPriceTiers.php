<?php

namespace App\Filament\Resources\DistributorPriceTierResource\Pages;

use App\Filament\Resources\DistributorPriceTierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDistributorPriceTiers extends ListRecords
{
    protected static string $resource = DistributorPriceTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
