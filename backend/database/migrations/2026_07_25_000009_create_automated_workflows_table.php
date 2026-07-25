<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automated_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('event'); // e.g. order.created, stock.low, distributor.approved
            $table->json('conditions')->nullable();
            $table->string('action'); // e.g. notification, email, status_change, webhook
            $table->json('action_config');
            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');
            $table->integer('run_count')->default(0);
            $table->timestamp('last_run_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('event');
            $table->index('status');
            $table->index(['event', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automated_workflows');
    }
};
