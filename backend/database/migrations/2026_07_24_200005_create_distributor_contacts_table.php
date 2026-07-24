<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->json('permissions_json')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('distributor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_contacts');
    }
};
