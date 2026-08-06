<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('quote_requests', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
        });
    }
};
