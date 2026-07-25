<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('limit', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('authorized_amount', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_accounts');
    }
};
