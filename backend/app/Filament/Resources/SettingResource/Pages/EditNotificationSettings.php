<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Enums\SettingGroup;

class EditNotificationSettings extends EditGroupSettings
{
    public function getGroup(): SettingGroup
    {
        return SettingGroup::NOTIFICATIONS;
    }
}
