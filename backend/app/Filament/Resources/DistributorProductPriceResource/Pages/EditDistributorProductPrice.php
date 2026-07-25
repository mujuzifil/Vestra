<?php

namespace App\Filament\Resources\DistributorProductPriceResource\Pages;

use App\Filament\Resources\DistributorProductPriceResource;
use App\Models\DistributorProductPrice;
use App\Services\AuditService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDistributorProductPrice extends EditRecord
{
    protected static string $resource = DistributorProductPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (DistributorProductPrice $record) {
                    AuditService::log(
                        auth()->user(),
                        'distributor_product_price.deleted',
                        $record,
                        ['distributor_id' => $record->distributor_id, 'product_id' => $record->product_id]
                    );
                }),
        ];
    }

    protected function afterSave(): void
    {
        /** @var DistributorProductPrice $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'distributor_product_price.updated',
            $record,
            ['distributor_id' => $record->distributor_id, 'product_id' => $record->product_id, 'price' => $record->price]
        );
    }
}
