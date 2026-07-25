<?php

namespace App\Filament\Resources\QuotationRequestResource\Pages;

use App\Filament\Resources\QuotationRequestResource;
use App\Models\QuotationRequest;
use App\Services\AuditService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuotationRequest extends EditRecord
{
    protected static string $resource = QuotationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (QuotationRequest $record) {
                    AuditService::log(
                        auth()->user(),
                        'quotation.deleted',
                        $record,
                        ['reference' => $record->reference_number]
                    );
                }),
        ];
    }

    protected function afterSave(): void
    {
        /** @var QuotationRequest $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'quotation.updated',
            $record,
            ['reference' => $record->reference_number, 'status' => $record->status->value]
        );
    }
}
