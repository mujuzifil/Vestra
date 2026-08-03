<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Pages\Sales\CompaniesPage;
use App\Filament\Resources\CustomerResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Legacy Customers index — permanently deferred to Sales → Companies.
 */
class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    public function mount(): void
    {
        $this->redirect(CompaniesPage::getUrl(), navigate: true);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
