<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function afterCreate(): void
    {
        /** @var \Spatie\Permission\Models\Role $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'role.created',
            $record,
            ['name' => $record->name, 'permissions' => $record->permissions->pluck('name')]
        );
    }
}
