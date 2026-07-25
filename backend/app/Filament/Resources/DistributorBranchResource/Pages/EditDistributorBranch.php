<?php

namespace App\Filament\Resources\DistributorBranchResource\Pages;

use App\Filament\Resources\DistributorBranchResource;
use App\Models\DistributorBranch;
use App\Services\AuditService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDistributorBranch extends EditRecord
{
    protected static string $resource = DistributorBranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (DistributorBranch $record) {
                    AuditService::log(
                        auth()->user(),
                        'distributor_branch.deleted',
                        $record,
                        ['distributor_id' => $record->distributor_id, 'name' => $record->name]
                    );
                }),
        ];
    }

    protected function afterSave(): void
    {
        /** @var DistributorBranch $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'distributor_branch.updated',
            $record,
            ['distributor_id' => $record->distributor_id, 'name' => $record->name]
        );
    }
}
