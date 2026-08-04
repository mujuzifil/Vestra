<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Pages\Administration\StaffPage;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Legacy Staff (Users) index — permanently deferred to Administration → Staff workspace.
 */
class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function mount(): void
    {
        $this->redirect(StaffPage::getUrl(), navigate: true);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
