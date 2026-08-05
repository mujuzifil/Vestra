<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Pages\Administration\RoleFormPage;
use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    public function mount(): void
    {
        $this->redirect(RoleFormPage::getUrl(), navigate: true);
    }
}
