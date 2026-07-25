<?php

namespace App\Filament\Resources\AutomatedWorkflowResource\Pages;

use App\Filament\Resources\AutomatedWorkflowResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAutomatedWorkflow extends EditRecord
{
    protected static string $resource = AutomatedWorkflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
