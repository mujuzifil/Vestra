<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Pages\Administration\RolesPage;
use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    public function mount(): void
    {
        $this->redirect(RolesPage::getUrl(), navigate: true);
    }
}
