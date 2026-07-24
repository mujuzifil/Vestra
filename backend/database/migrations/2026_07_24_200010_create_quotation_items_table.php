<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('quotation_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
