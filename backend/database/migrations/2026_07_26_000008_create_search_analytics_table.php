<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('term', 255)->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 64)->nullable()->index();
            $table->unsignedInteger('results_count')->default(0);
            $table->foreignId('clicked_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->boolean('converted')->default(false);
            $table->timestamp('searched_at')->useCurrent();
            $table->timestamps();

            $table->index(['term', 'searched_at']);
            $table->index(['session_id', 'searched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_analytics');
    }
};
