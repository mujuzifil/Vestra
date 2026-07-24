<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_account_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->morphs('reference');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('credit_account_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
