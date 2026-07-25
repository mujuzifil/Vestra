<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->json('pros')->nullable()->after('comment');
            $table->json('cons')->nullable()->after('pros');
            $table->unsignedInteger('helpful_count')->default(0)->after('cons');
            $table->unsignedInteger('reported_count')->default(0)->after('helpful_count');
            $table->boolean('is_featured')->default(false)->after('reported_count');
            $table->boolean('is_pinned')->default(false)->after('is_featured');
            $table->text('admin_reply')->nullable()->after('is_pinned');
            $table->timestamp('admin_reply_at')->nullable()->after('admin_reply');
            $table->foreignId('admin_reply_by')->nullable()->after('admin_reply_at')->constrained('users')->nullOnDelete();

            $table->index(['product_id', 'status', 'is_featured']);
            $table->index(['product_id', 'status', 'is_pinned']);
            $table->index(['helpful_count']);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropColumn([
                'pros',
                'cons',
                'helpful_count',
                'reported_count',
                'is_featured',
                'is_pinned',
                'admin_reply',
                'admin_reply_at',
                'admin_reply_by',
            ]);
        });
    }
};
