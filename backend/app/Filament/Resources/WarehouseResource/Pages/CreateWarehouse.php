<?php

namespace App\Filament\Resources\WarehouseResource\Pages;

use App\Filament\Resources\WarehouseResource;
use App\Models\Warehouse;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreateWarehouse extends CreateRecord
{
    protected static string $resource = WarehouseResource::class;

    protected function afterCreate(): void
    {
        /** @var Warehouse $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'warehouse.created',
            $record,
            ['name' => $record->name, 'code' => $record->code]
        );
    }
}
