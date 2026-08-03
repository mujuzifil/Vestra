<?php

namespace App\Filament\Resources\DistributorBranchResource\Pages;

use App\Filament\Resources\DistributorBranchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDistributorBranches extends ListRecords
{
    protected static string $resource = DistributorBranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
