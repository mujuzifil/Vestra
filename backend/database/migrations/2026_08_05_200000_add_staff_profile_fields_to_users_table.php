<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('email');
            }
            if (! Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'job_title')) {
                $table->string('job_title')->nullable()->after('department');
            }
            if (! Schema::hasColumn('users', 'employee_id')) {
                $table->string('employee_id')->nullable()->unique()->after('job_title');
            }
            if (! Schema::hasColumn('users', 'notes')) {
                $table->text('notes')->nullable()->after('employee_id');
            }
            if (! Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('force_password_change_at');
            }
            if (! Schema::hasColumn('users', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('password_changed_at');
            }
            if (! Schema::hasColumn('users', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('locked_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['created_by', 'updated_by'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            $columns = array_filter([
                'username',
                'department',
                'job_title',
                'employee_id',
                'notes',
                'password_changed_at',
                'locked_at',
            ], fn (string $column) => Schema::hasColumn('users', $column));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
