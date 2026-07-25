<?php

namespace App\Filament\Resources\DistributorContactResource\Pages;

use App\Filament\Resources\DistributorContactResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDistributorContacts extends ListRecords
{
    protected static string $resource = DistributorContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
