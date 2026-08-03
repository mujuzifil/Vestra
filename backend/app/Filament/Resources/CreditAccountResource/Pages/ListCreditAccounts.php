<?php

namespace App\Filament\Resources\CreditAccountResource\Pages;

use App\Filament\Pages\Distributors\CreditPage;
use App\Filament\Resources\CreditAccountResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Legacy Credit Accounts index — permanently deferred to Distributors → Credit.
 */
class ListCreditAccounts extends ListRecords
{
    protected static string $resource = CreditAccountResource::class;

    public function mount(): void
    {
        $this->redirect(CreditPage::getUrl(), navigate: true);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
