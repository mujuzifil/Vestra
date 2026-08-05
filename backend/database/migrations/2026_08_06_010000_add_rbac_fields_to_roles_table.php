<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (! Schema::hasColumn('roles', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
            if (! Schema::hasColumn('roles', 'status')) {
                $table->string('status', 20)->default('active')->after('description');
            }
            if (! Schema::hasColumn('roles', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
            if (! Schema::hasColumn('roles', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('roles', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            foreach (['created_by', 'updated_by'] as $column) {
                if (Schema::hasColumn('roles', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            $columns = array_filter(
                ['slug', 'status', 'notes'],
                fn (string $column) => Schema::hasColumn('roles', $column)
            );

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
