<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('courier')->nullable()->after('notes');
            $table->string('tracking_number')->nullable()->after('courier');
            $table->timestamp('dispatched_at')->nullable()->after('tracking_number');
            $table->timestamp('delivered_at')->nullable()->after('dispatched_at');
            $table->text('internal_notes')->nullable()->after('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['courier', 'tracking_number', 'dispatched_at', 'delivered_at', 'internal_notes']);
        });
    }
};
