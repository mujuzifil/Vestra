<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Enums\SettingGroup;

class EditIntegrationSettings extends EditGroupSettings
{
    public function getGroup(): SettingGroup
    {
        return SettingGroup::INTEGRATIONS;
    }
}
