<?php

namespace App\Filament\Resources\DistributorBranchResource\Pages;

use App\Filament\Pages\Distributors\TerritoriesPage;
use App\Filament\Resources\DistributorBranchResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Legacy Branches index — permanently deferred to Distributors → Territories.
 */
class ListDistributorBranches extends ListRecords
{
    protected static string $resource = DistributorBranchResource::class;

    public function mount(): void
    {
        $this->redirect(TerritoriesPage::getUrl(), navigate: true);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
