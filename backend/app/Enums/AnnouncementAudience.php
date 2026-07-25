<?php

namespace App\Enums;

enum AnnouncementAudience: string
{
    case EVERYONE = 'everyone';
    case CUSTOMERS = 'customers';
    case DISTRIBUTORS = 'distributors';
    case ADMINS = 'admins';

    public function label(): string
    {
        return match ($this) {
            self::EVERYONE => 'Everyone',
            self::CUSTOMERS => 'Customers',
            self::DISTRIBUTORS => 'Distributors',
            self::ADMINS => 'Administrators',
        };
    }
}
