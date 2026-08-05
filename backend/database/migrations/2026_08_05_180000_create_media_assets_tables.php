<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('file_name');
            $table->string('original_file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('media_type', 32)->default('other')->index();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('checksum', 64)->nullable()->index();
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();
            $table->text('description')->nullable();
            $table->json('tags')->nullable();
            $table->string('copyright')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('status', 32)->default('active')->index();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('created_at');
            $table->index('size_bytes');
        });

        Schema::create('media_asset_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_asset_id')->constrained('media_assets')->cascadeOnDelete();
            $table->morphs('usable');
            $table->string('context', 64)->default('general');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(
                ['media_asset_id', 'usable_type', 'usable_id', 'context'],
                'media_asset_usages_unique'
            );
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->foreignId('media_asset_id')
                ->nullable()
                ->after('product_id')
                ->constrained('media_assets')
                ->nullOnDelete();
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->foreignId('featured_media_asset_id')
                ->nullable()
                ->after('featured_image')
                ->constrained('media_assets')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('featured_media_asset_id');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropConstrainedForeignId('media_asset_id');
        });

        Schema::dropIfExists('media_asset_usages');
        Schema::dropIfExists('media_assets');
    }
};
