<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        /** @var User $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'administrator.created',
            $record,
            ['email' => $record->email, 'roles' => $record->roles->pluck('name')]
        );
    }
}
