<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->timestamps();

            $table->unique(['distributor_id', 'product_id', 'effective_from']);
            $table->index(['distributor_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_product_prices');
    }
};
