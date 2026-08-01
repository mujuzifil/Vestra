<?php

namespace App\Enums;

enum ContactEnquiryType: string
{
    case GENERAL = 'general';
    case SALES = 'sales';
    case DISTRIBUTOR = 'distributor';
    case QUOTE = 'quote';
    case TECHNICAL_SUPPORT = 'technical_support';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL => 'General Enquiry',
            self::SALES => 'Sales',
            self::DISTRIBUTOR => 'Distributor',
            self::QUOTE => 'Quote',
            self::TECHNICAL_SUPPORT => 'Technical Support',
            self::OTHER => 'Other',
        };
    }
}
