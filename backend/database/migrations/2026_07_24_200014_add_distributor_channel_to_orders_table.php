<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('distributor_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('channel', 20)->default('retail')->after('distributor_id');
            $table->decimal('distributor_discount_amount', 12, 2)->default(0)->after('tax_amount');

            $table->index('distributor_id');
            $table->index('channel');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['distributor_id']);
            $table->dropColumn(['distributor_id', 'channel', 'distributor_discount_amount']);
        });
    }
};
