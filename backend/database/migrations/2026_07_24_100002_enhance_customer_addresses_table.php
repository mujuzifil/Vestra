<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->string('address_line_2')->nullable()->after('address_line');
            $table->string('postal_code', 20)->nullable()->after('address_line_2');
            $table->string('country', 100)->nullable()->after('postal_code');
            $table->text('delivery_notes')->nullable()->after('country');
            $table->boolean('is_default_shipping')->default(false)->after('is_default');
            $table->boolean('is_default_billing')->default(false)->after('is_default_shipping');
        });
    }

    public function down(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropColumn([
                'address_line_2',
                'postal_code',
                'country',
                'delivery_notes',
                'is_default_shipping',
                'is_default_billing',
            ]);
        });
    }
};
