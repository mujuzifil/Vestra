<?php

namespace App\Filament\Resources\DistributorContactResource\Pages;

use App\Filament\Resources\DistributorContactResource;
use App\Models\DistributorContact;
use App\Services\AuditService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDistributorContact extends EditRecord
{
    protected static string $resource = DistributorContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (DistributorContact $record) {
                    AuditService::log(
                        auth()->user(),
                        'distributor_contact.deleted',
                        $record,
                        ['distributor_id' => $record->distributor_id, 'name' => $record->name]
                    );
                }),
        ];
    }

    protected function afterSave(): void
    {
        /** @var DistributorContact $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'distributor_contact.updated',
            $record,
            ['distributor_id' => $record->distributor_id, 'name' => $record->name]
        );
    }
}
