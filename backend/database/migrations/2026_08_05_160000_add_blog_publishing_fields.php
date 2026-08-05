<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_authors', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->boolean('show_on_homepage')->default(false)->after('is_featured');
            $table->boolean('is_pinned')->default(false)->after('show_on_homepage');
            $table->boolean('allow_comments')->default(true)->after('is_pinned');
            $table->string('og_title')->nullable()->after('canonical_url');
            $table->text('og_description')->nullable()->after('og_title');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn([
                'show_on_homepage',
                'is_pinned',
                'allow_comments',
                'og_title',
                'og_description',
            ]);
        });

        Schema::table('blog_authors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
