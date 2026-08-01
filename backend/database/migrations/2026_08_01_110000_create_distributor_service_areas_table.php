<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_service_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('distributor_branches')->nullOnDelete();
            $table->string('region');
            $table->string('district');
            $table->string('status')->default('covered'); // covered, coming_soon, planned
            $table->timestamps();

            $table->index(['region', 'district']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_service_areas');
    }
};
