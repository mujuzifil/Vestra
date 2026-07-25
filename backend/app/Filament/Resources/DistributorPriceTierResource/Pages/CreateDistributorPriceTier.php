<?php

namespace App\Filament\Resources\DistributorPriceTierResource\Pages;

use App\Filament\Resources\DistributorPriceTierResource;
use App\Models\DistributorPriceTier;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreateDistributorPriceTier extends CreateRecord
{
    protected static string $resource = DistributorPriceTierResource::class;

    protected function afterCreate(): void
    {
        /** @var DistributorPriceTier $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'distributor_price_tier.created',
            $record,
            ['product_id' => $record->product_id, 'price' => $record->price]
        );
    }
}
