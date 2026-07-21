<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Enums\SettingGroup;

class EditBusinessSettings extends EditGroupSettings
{
    public function getGroup(): SettingGroup
    {
        return SettingGroup::BUSINESS;
    }
}
