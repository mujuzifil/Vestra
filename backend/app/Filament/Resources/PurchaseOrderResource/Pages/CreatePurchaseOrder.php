<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function afterCreate(): void
    {
        /** @var PurchaseOrder $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'purchase_order.created',
            $record,
            ['po_number' => $record->po_number, 'supplier_id' => $record->supplier_id, 'total' => $record->total]
        );
    }
}
