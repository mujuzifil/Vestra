<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->string('full_name');
            $table->string('company_name');
            $table->string('email');
            $table->string('phone');
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->date('preferred_delivery_date')->nullable();
            $table->text('delivery_location')->nullable();
            $table->string('status')->default('pending');
            $table->string('source')->default('website');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('email');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
