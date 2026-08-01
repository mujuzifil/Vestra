<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->string('priority')->nullable()->after('status');
            $table->decimal('estimated_value', 12, 2)->nullable()->after('priority');
            $table->date('expected_close_date')->nullable()->after('estimated_value');
            $table->json('attachments')->nullable()->after('requirements');
            $table->json('crm_metadata')->nullable()->after('attachments');

            $table->index('priority');
            $table->index('expected_close_date');
        });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropIndex(['priority']);
            $table->dropIndex(['expected_close_date']);
            $table->dropColumn(['priority', 'estimated_value', 'expected_close_date', 'attachments', 'crm_metadata']);
        });
    }
};
