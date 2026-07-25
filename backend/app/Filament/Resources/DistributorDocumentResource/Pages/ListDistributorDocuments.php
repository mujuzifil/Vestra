<?php

namespace App\Filament\Resources\DistributorDocumentResource\Pages;

use App\Filament\Resources\DistributorDocumentResource;
use Filament\Resources\Pages\ListRecords;

class ListDistributorDocuments extends ListRecords
{
    protected static string $resource = DistributorDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Creation is handled via the distributor documents relation manager.
        ];
    }
}
