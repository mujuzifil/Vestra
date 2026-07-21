<?php

namespace App\Filament\Resources\DistributorRequestResource\Pages;

use App\Filament\Resources\DistributorRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDistributorRequest extends EditRecord
{
    protected static string $resource = DistributorRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
