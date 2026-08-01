<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('blog_authors')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable();
            $table->json('content_blocks')->nullable();
            $table->string('status')->default('draft');
            $table->string('visibility')->default('public');
            $table->boolean('is_featured')->default(false);
            $table->integer('reading_time_minutes')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps();

            $table->index('slug');
            $table->index('status');
            $table->index('visibility');
            $table->index('is_featured');
            $table->index('published_at');
            $table->index(['status', 'visibility', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
