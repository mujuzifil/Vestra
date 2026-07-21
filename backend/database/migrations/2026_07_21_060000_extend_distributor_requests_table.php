<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distributor_requests', function (Blueprint $table) {
            $table->string('priority')->default('medium')->after('status');
            $table->string('country')->nullable()->after('address');
            $table->string('region')->nullable()->after('country');
            $table->string('business_type')->nullable()->after('company_name');
            $table->unsignedTinyInteger('years_in_operation')->nullable()->after('business_type');
            $table->text('products_interested_in')->nullable()->after('business_description');
            $table->string('target_region')->nullable()->after('products_interested_in');
            $table->string('estimated_volume')->nullable()->after('target_region');
            $table->boolean('existing_customer')->default(false)->after('estimated_volume');
            $table->unsignedInteger('previous_applications')->default(0)->after('existing_customer');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete()->after('priority');
            $table->text('internal_notes')->nullable()->after('assigned_to');
            $table->json('documents')->nullable()->after('internal_notes');

            $table->index(['status', 'priority']);
            $table->index('country');
            $table->index('region');
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::table('distributor_requests', function (Blueprint $table) {
            $table->dropColumn([
                'priority',
                'country',
                'region',
                'business_type',
                'years_in_operation',
                'products_interested_in',
                'target_region',
                'estimated_volume',
                'existing_customer',
                'previous_applications',
                'assigned_to',
                'internal_notes',
                'documents',
            ]);
        });
    }
};
