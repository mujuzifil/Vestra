<?php

namespace App\Filament\Resources\QuotationRequestResource\Pages;

use App\Filament\Resources\QuotationRequestResource;
use App\Models\QuotationRequest;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreateQuotationRequest extends CreateRecord
{
    protected static string $resource = QuotationRequestResource::class;

    protected function afterCreate(): void
    {
        /** @var QuotationRequest $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'quotation.created',
            $record,
            ['reference' => $record->reference_number, 'distributor_id' => $record->distributor_id]
        );
    }
}
