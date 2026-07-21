<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Services\AuditService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** @var array<int, string> */
    private array $previousRoles = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        /** @var User $record */
        $record = $this->record;
        $this->previousRoles = $record->roles->pluck('name')->toArray();
    }

    protected function afterSave(): void
    {
        /** @var User $record */
        $record = $this->record;
        $currentRoles = $record->roles->pluck('name')->toArray();

        if ($this->previousRoles !== $currentRoles) {
            AuditService::log(
                auth()->user(),
                'user.roles_changed',
                $record,
                [
                    'previous_roles' => $this->previousRoles,
                    'current_roles' => $currentRoles,
                ]
            );
        }
    }
}
