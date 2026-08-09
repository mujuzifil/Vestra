<?php

namespace App\Filament\Resources\DistributorResource\Pages;

use App\Enums\DistributorAccountStatus;
use App\Enums\DistributorStockAvailability;
use App\Enums\DistributorTier;
use App\Filament\Resources\DistributorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDistributor extends CreateRecord
{
    protected static string $resource = DistributorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] ??= DistributorAccountStatus::ACTIVE->value;
        $data['tier'] ??= DistributorTier::SILVER->value;
        $data['stock_availability'] ??= DistributorStockAvailability::IN_STOCK->value;
        $data['approved_at'] ??= now();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
