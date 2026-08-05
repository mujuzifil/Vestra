<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Pages\Administration\RoleFormPage;
use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    public function mount(int|string $record): void
    {
        $this->redirect(RoleFormPage::getUrl(['id' => $record]), navigate: true);
    }
}
