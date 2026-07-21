<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Enums\SettingGroup;

class EditInventorySettings extends EditGroupSettings
{
    public function getGroup(): SettingGroup
    {
        return SettingGroup::INVENTORY;
    }
}
