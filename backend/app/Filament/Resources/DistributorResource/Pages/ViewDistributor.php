<?php

namespace App\Filament\Resources\DistributorResource\Pages;

use App\Filament\Resources\DistributorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDistributor extends ViewRecord
{
    protected static string $resource = DistributorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->url(fn ($record): string => route('filament.admin.resources.distributor-requests.edit', $record->distributor_request_id ?? $record->id))
                ->hidden(fn ($record): bool => $record->distributor_request_id === null),
        ];
    }
}
