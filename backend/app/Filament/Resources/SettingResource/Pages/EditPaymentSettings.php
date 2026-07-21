<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Enums\SettingGroup;

class EditPaymentSettings extends EditGroupSettings
{
    public function getGroup(): SettingGroup
    {
        return SettingGroup::PAYMENTS;
    }
}
