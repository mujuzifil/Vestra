<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Enums\SettingGroup;

class EditOrderSettings extends EditGroupSettings
{
    public function getGroup(): SettingGroup
    {
        return SettingGroup::ORDERS;
    }
}
