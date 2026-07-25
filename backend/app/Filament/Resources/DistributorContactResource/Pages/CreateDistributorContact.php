<?php

namespace App\Filament\Resources\DistributorContactResource\Pages;

use App\Filament\Resources\DistributorContactResource;
use App\Models\DistributorContact;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreateDistributorContact extends CreateRecord
{
    protected static string $resource = DistributorContactResource::class;

    protected function afterCreate(): void
    {
        /** @var DistributorContact $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'distributor_contact.created',
            $record,
            ['distributor_id' => $record->distributor_id, 'name' => $record->name]
        );
    }
}
