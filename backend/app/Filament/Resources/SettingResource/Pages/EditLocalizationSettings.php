<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Enums\SettingGroup;

class EditLocalizationSettings extends EditGroupSettings
{
    public function getGroup(): SettingGroup
    {
        return SettingGroup::LOCALIZATION;
    }
}
