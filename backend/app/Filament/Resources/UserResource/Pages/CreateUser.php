<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);
        $record->forceFill(['is_admin' => true])->save();

        return $record;
    }

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
