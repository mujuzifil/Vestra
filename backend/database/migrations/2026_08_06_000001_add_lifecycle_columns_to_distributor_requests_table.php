<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distributor_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('distributor_requests', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('internal_notes');
            }

            if (! Schema::hasColumn('distributor_requests', 'information_request_notes')) {
                $table->text('information_request_notes')->nullable()->after('rejection_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('distributor_requests', function (Blueprint $table) {
            if (Schema::hasColumn('distributor_requests', 'information_request_notes')) {
                $table->dropColumn('information_request_notes');
            }

            if (Schema::hasColumn('distributor_requests', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};
