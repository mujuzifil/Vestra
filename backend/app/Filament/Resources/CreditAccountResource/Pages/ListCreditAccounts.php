<?php

namespace App\Filament\Resources\CreditAccountResource\Pages;

use App\Filament\Resources\CreditAccountResource;
use Filament\Resources\Pages\ListRecords;

class ListCreditAccounts extends ListRecords
{
    protected static string $resource = CreditAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
