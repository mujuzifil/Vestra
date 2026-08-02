<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->index();
            $table->string('priority')->index();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_id')->constrained('users')->cascadeOnDelete();
            $table->nullableMorphs('related');
            $table->dateTime('due_date')->nullable()->index();
            $table->dateTime('completed_at')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('attachment_paths')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'priority', 'assignee_id']);
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
