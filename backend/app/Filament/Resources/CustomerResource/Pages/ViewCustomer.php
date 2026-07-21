<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected static string $view = 'filament.resources.customer-resource.pages.view-customer';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit')
                ->url(fn ($record): string => static::getResource()::getUrl('edit', ['record' => $record]))
                ->icon('heroicon-o-pencil')
                ->color('primary'),
        ];
    }
}
