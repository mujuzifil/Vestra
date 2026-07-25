<?php

namespace App\Filament\Resources\DistributorDocumentResource\Pages;

use App\Filament\Resources\DistributorDocumentResource;
use App\Models\DistributorDocument;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDistributorDocument extends ViewRecord
{
    protected static string $resource = DistributorDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->label('Download Document')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn (DistributorDocument $record): string => $record->fileUrl())
                ->openUrlInNewTab()
                ->color('primary'),
        ];
    }
}
