<?php

namespace App\Filament\Resources\CustomerTagResource\Pages;

use App\Filament\Resources\CustomerTagResource;
use App\Models\CustomerTag;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCustomerTag extends CreateRecord
{
    protected static string $resource = CustomerTagResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['slug'] = $data['slug'] ?? \Illuminate\Support\Str::slug($data['name']);

        return CustomerTag::create($data);
    }

    protected function afterCreate(): void
    {
        /** @var CustomerTag $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'customer_tag.created',
            $record,
            ['name' => $record->name, 'color' => $record->color]
        );
    }
}
