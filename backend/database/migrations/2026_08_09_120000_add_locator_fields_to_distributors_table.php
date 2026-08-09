<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distributors', function (Blueprint $table) {
            $table->string('tier')->default('silver')->after('status');
            $table->string('whatsapp')->nullable()->after('phone');
            $table->string('google_maps_url')->nullable()->after('postal_address');
            $table->string('stock_availability')->default('in_stock')->after('google_maps_url');

            $table->index('tier');
            $table->index('stock_availability');
            $table->index(['status', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::table('distributors', function (Blueprint $table) {
            $table->dropColumn(['tier', 'whatsapp', 'google_maps_url', 'stock_availability']);
        });
    }
};
