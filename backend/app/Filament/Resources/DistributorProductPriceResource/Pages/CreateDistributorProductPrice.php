<?php

namespace App\Filament\Resources\DistributorProductPriceResource\Pages;

use App\Filament\Resources\DistributorProductPriceResource;
use App\Models\DistributorProductPrice;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreateDistributorProductPrice extends CreateRecord
{
    protected static string $resource = DistributorProductPriceResource::class;

    protected function afterCreate(): void
    {
        /** @var DistributorProductPrice $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'distributor_product_price.created',
            $record,
            ['distributor_id' => $record->distributor_id, 'product_id' => $record->product_id, 'price' => $record->price]
        );
    }
}
