<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;

    protected function afterCreate(): void
    {
        /** @var \Spatie\Permission\Models\Permission $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'permission.created',
            $record,
            ['name' => $record->name, 'group' => $record->group]
        );
    }
}
