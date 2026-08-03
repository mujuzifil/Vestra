<?php

namespace App\Filament\Resources\DistributorRequestResource\Pages;

use App\Filament\Pages\Distributors\ApplicationsPage;
use App\Filament\Resources\DistributorRequestResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Legacy Distributor Requests index — permanently deferred to Distributors → Applications.
 */
class ListDistributorRequests extends ListRecords
{
    protected static string $resource = DistributorRequestResource::class;

    public function mount(): void
    {
        $this->redirect(ApplicationsPage::getUrl(), navigate: true);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
