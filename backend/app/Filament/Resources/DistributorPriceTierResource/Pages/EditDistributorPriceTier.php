<?php

namespace App\Filament\Resources\DistributorPriceTierResource\Pages;

use App\Filament\Resources\DistributorPriceTierResource;
use App\Models\DistributorPriceTier;
use App\Services\AuditService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDistributorPriceTier extends EditRecord
{
    protected static string $resource = DistributorPriceTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (DistributorPriceTier $record) {
                    AuditService::log(
                        auth()->user(),
                        'distributor_price_tier.deleted',
                        $record,
                        ['product_id' => $record->product_id, 'price' => $record->price]
                    );
                }),
        ];
    }

    protected function afterSave(): void
    {
        /** @var DistributorPriceTier $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'distributor_price_tier.updated',
            $record,
            ['product_id' => $record->product_id, 'price' => $record->price]
        );
    }
}
