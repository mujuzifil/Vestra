<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->string('subject')->nullable();
            $table->longText('email_body')->nullable();
            $table->text('sms_body')->nullable();
            $table->longText('in_app_body')->nullable();
            $table->json('channels_json')->nullable();
            $table->json('variables_json')->nullable();
            $table->string('priority')->default('normal');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('version')->default(1);
            $table->timestamps();

            $table->index('event_key');
            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
