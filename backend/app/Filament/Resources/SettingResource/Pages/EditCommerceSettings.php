<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Enums\SettingGroup;

class EditCommerceSettings extends EditGroupSettings
{
    public function getGroup(): SettingGroup
    {
        return SettingGroup::COMMERCE;
    }
}
