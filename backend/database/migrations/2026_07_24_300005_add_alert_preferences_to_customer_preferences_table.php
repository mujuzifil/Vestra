<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_preferences', function (Blueprint $table) {
            $table->boolean('system_alerts')->default(true)->after('account_preferences');
            $table->boolean('emergency_alerts')->default(true)->after('system_alerts');
        });
    }

    public function down(): void
    {
        Schema::table('customer_preferences', function (Blueprint $table) {
            $table->dropColumn(['system_alerts', 'emergency_alerts']);
        });
    }
};
