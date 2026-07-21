<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('priority')->default('medium')->after('status');
            $table->timestamp('read_at')->nullable()->after('priority');
            $table->index(['status', 'priority']);
            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn(['priority', 'read_at']);
        });
    }
};
