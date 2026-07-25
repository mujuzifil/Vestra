<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('distributor_request_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('sales_representative_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('active');

            // Company information
            $table->string('company_name');
            $table->string('trading_name')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('tax_identification')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('business_type')->nullable();
            $table->string('industry')->nullable();
            $table->unsignedTinyInteger('years_in_business')->nullable();
            $table->string('company_size')->nullable();
            $table->string('website')->nullable();

            // Contacts
            $table->string('primary_contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Address
            $table->string('country')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->text('postal_address')->nullable();

            // Branding & documents
            $table->string('logo_path')->nullable();
            $table->json('operating_hours_json')->nullable();
            $table->json('bank_info_json')->nullable();
            $table->json('billing_info_json')->nullable();

            // Volumes
            $table->string('expected_monthly_volume')->nullable();
            $table->text('products_of_interest')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['status', 'approved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributors');
    }
};
