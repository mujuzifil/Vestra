<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Models\Supplier;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;

    protected function afterCreate(): void
    {
        /** @var Supplier $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'supplier.created',
            $record,
            ['name' => $record->name, 'code' => $record->code]
        );
    }
}
