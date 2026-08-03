<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('status')->default('prospect')->after('primary_contact_email');
            $table->foreignId('account_manager_id')->nullable()->constrained('users')->nullOnDelete()->after('status');
            $table->string('region')->nullable()->after('account_manager_id');
            $table->text('notes')->nullable()->after('region');
        });
    }

    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropForeign(['account_manager_id']);
            $table->dropColumn(['status', 'account_manager_id', 'region', 'notes']);
        });
    }
};
