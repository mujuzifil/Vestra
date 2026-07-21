<?php

namespace App\Enums;

enum SettingType: string
{
    case STRING = 'string';
    case TEXT = 'text';
    case IMAGE = 'image';
    case BOOLEAN = 'boolean';
    case NUMBER = 'number';
    case JSON = 'json';
    case SELECT = 'select';

    public function label(): string
    {
        return match ($this) {
            self::STRING => 'String',
            self::TEXT => 'Text',
            self::IMAGE => 'Image',
            self::BOOLEAN => 'Boolean',
            self::NUMBER => 'Number',
            self::JSON => 'JSON',
            self::SELECT => 'Select',
        };
    }
}
