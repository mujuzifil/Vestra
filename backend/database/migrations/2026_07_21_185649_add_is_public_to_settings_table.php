<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Settings that may be exposed to unauthenticated storefront callers.
     */
    private const PUBLIC_KEYS = [
        // General
        'app_name',
        'company_name',
        'support_email',
        'support_phone',
        'website_url',
        'company_logo',
        'favicon',
        'business_address',
        'timezone',
        'language',
        'date_format',
        'currency',
        'founded',
        'headquarters',

        // Business
        'business_registration',
        'tax_number',
        'vat_number',
        'invoice_prefix',
        'invoice_starting_number',
        'default_distributor_status',
        'business_hours',

        // Commerce
        'default_product_status',
        'default_review_behaviour',
        'featured_product_rules',
        'stock_threshold',
        'default_category',
        'price_precision',
        'tax_display',

        // Orders
        'order_number_prefix',
        'default_order_status',
        'cancellation_window_hours',
        'auto_archive_days',

        // Payments (operational only; credentials remain private)
        'enabled_payment_methods',
        'default_payment_method',
        'offline_payment_instructions',
        'payment_timeout_minutes',
        'transaction_reference_prefix',

        // Inventory
        'low_stock_threshold',
        'out_of_stock_behaviour',
        'inventory_alerts_enabled',
        'sku_format',

        // Notifications (enabled flags only)
        'admin_notifications_enabled',
        'customer_notifications_enabled',
        'distributor_notifications_enabled',
        'review_notifications_enabled',
        'email_notifications_enabled',

        // Localization
        'default_language',
        'localization_timezone',
        'localization_date_format',
        'time_format',
        'number_format',
        'currency_symbol',
        'currency_position',

        // Social
        'facebook',
        'instagram',
        'linkedin',

        // Content
        'mission',
        'vision',
        'company_description',
        'philosophy',
        'footer_text',
        'core_values',
        'distributor_benefits',
        'hero_features',
        'promise_items',
        'why_choose_features',
        'general_faqs',
        'distributor_faqs',

        // Integrations (public identifiers only)
        'google_analytics_id',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('group');
        });

        // Backfill existing settings. Any key not in the allow-list is kept private.
        DB::table('settings')
            ->whereIn('key', self::PUBLIC_KEYS)
            ->update(['is_public' => true]);

        DB::table('settings')
            ->whereNotIn('key', self::PUBLIC_KEYS)
            ->update(['is_public' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
};
