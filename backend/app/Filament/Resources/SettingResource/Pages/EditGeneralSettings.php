<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Enums\SettingGroup;

class EditGeneralSettings extends EditGroupSettings
{
    public function getGroup(): SettingGroup
    {
        return SettingGroup::GENERAL;
    }
}
