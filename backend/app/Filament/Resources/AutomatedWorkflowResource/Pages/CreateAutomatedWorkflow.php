<?php

namespace App\Filament\Resources\AutomatedWorkflowResource\Pages;

use App\Filament\Resources\AutomatedWorkflowResource;
use App\Models\AutomatedWorkflow;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreateAutomatedWorkflow extends CreateRecord
{
    protected static string $resource = AutomatedWorkflowResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var AutomatedWorkflow $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'workflow.created',
            $record,
            ['name' => $record->name, 'event' => $record->event, 'action' => $record->action]
        );
    }
}
