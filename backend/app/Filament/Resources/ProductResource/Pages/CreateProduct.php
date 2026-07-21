<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        /** @var Product $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'product.created',
            $record,
            ['name' => $record->name, 'price' => $record->price]
        );
    }
}
