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
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['status'], $data['rejection_reason'], $data['information_request_notes']);

        return $data;
    }
}
