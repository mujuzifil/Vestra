<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Pages\Administration\StaffPage;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function mount(): void
    {
        $this->redirect(StaffPage::getUrl(), navigate: true);
    }
}
