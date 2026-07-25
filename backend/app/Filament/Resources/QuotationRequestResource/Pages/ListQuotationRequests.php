<?php

namespace App\Filament\Resources\QuotationRequestResource\Pages;

use App\Filament\Resources\QuotationRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuotationRequests extends ListRecords
{
    protected static string $resource = QuotationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
