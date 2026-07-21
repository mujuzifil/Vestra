<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Enums\SettingGroup;

class EditSecuritySettings extends EditGroupSettings
{
    public function getGroup(): SettingGroup
    {
        return SettingGroup::SECURITY;
    }
}
