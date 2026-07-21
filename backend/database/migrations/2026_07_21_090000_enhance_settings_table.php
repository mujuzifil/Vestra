<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->text('description')->nullable()->after('label');
            $table->json('options')->nullable()->after('description');
            $table->unsignedInteger('sort_order')->default(0)->after('options');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn(['description', 'options', 'sort_order']);
        });
    }
};
