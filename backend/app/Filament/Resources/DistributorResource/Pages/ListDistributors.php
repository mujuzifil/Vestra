<?php

namespace App\Filament\Resources\DistributorResource\Pages;

use App\Filament\Pages\Distributors\ActivePartnersPage;
use App\Filament\Resources\DistributorResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Legacy Distributors index — permanently deferred to Distributors → Active Partners.
 */
class ListDistributors extends ListRecords
{
    protected static string $resource = DistributorResource::class;

    public function mount(): void
    {
        $this->redirect(ActivePartnersPage::getUrl(), navigate: true);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
