<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('body');
            $table->string('target_audience')->default('everyone');
            $table->string('priority')->default('normal');
            $table->boolean('pinned')->default(false);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('target_audience');
            $table->index('priority');
            $table->index('pinned');
            $table->index('start_at');
            $table->index('end_at');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
