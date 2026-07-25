<?php

namespace App\Filament\Resources\AutomatedWorkflowResource\Pages;

use App\Filament\Resources\AutomatedWorkflowResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAutomatedWorkflows extends ListRecords
{
    protected static string $resource = AutomatedWorkflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
