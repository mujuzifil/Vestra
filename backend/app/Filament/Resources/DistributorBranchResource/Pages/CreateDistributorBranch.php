<?php

namespace App\Filament\Resources\DistributorBranchResource\Pages;

use App\Filament\Resources\DistributorBranchResource;
use App\Models\DistributorBranch;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreateDistributorBranch extends CreateRecord
{
    protected static string $resource = DistributorBranchResource::class;

    protected function afterCreate(): void
    {
        /** @var DistributorBranch $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'distributor_branch.created',
            $record,
            ['distributor_id' => $record->distributor_id, 'name' => $record->name]
        );
    }
}
