<?php

namespace App\Filament\Resources\PaymentUploadResource\Pages;

use App\Filament\Resources\PaymentUploadResource;
use Filament\Resources\Pages\ListRecords;

class ListPaymentUploads extends ListRecords
{
    protected static string $resource = PaymentUploadResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
