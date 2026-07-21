<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function afterCreate(): void
    {
        /** @var Category $record */
        $record = $this->record;

        AuditService::log(
            auth()->user(),
            'category.created',
            $record,
            ['name' => $record->name]
        );
    }
}
