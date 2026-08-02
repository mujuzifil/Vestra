<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('company_name')->nullable();
            $table->string('industry')->nullable();
            $table->string('business_type')->nullable();
            $table->string('tax_identification')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('website')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Uganda');
            $table->text('address')->nullable();
            $table->string('primary_contact_name')->nullable();
            $table->string('primary_contact_phone')->nullable();
            $table->string('primary_contact_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
