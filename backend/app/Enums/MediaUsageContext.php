<?php

namespace App\Enums;

enum MediaUsageContext: string
{
    case PRODUCT_PRIMARY = 'product_primary';
    case PRODUCT_GALLERY = 'product_gallery';
    case BLOG_FEATURED = 'blog_featured';
    case BLOG_GALLERY = 'blog_gallery';
    case BLOG_INLINE = 'blog_inline';
    case HOMEPAGE = 'homepage';
    case MARKETING = 'marketing';
    case GENERAL = 'general';

    public function label(): string
    {
        return match ($this) {
            self::PRODUCT_PRIMARY => 'Product primary',
            self::PRODUCT_GALLERY => 'Product gallery',
            self::BLOG_FEATURED => 'Blog featured',
            self::BLOG_GALLERY => 'Blog gallery',
            self::BLOG_INLINE => 'Blog inline',
            self::HOMEPAGE => 'Homepage',
            self::MARKETING => 'Marketing',
            self::GENERAL => 'General',
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::PRODUCT_PRIMARY, self::PRODUCT_GALLERY => 'Products',
            self::BLOG_FEATURED, self::BLOG_GALLERY, self::BLOG_INLINE => 'Blog Articles',
            self::HOMEPAGE => 'Homepage',
            self::MARKETING => 'Marketing',
            self::GENERAL => 'Other',
        };
    }
}
