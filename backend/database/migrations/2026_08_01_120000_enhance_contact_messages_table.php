<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('company')->nullable()->after('name');
            $table->string('enquiry_type')->default('general')->after('subject');
            $table->json('attachments')->nullable()->after('message');
            $table->foreignId('assigned_to')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable()->after('assigned_to');
            $table->string('source')->default('website')->after('internal_notes');
            $table->ipAddress('ip_address')->nullable()->after('source');
            $table->text('user_agent')->nullable()->after('ip_address');

            $table->index('enquiry_type');
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropIndex(['enquiry_type']);
            $table->dropIndex(['assigned_to']);
            $table->dropForeign(['assigned_to']);
            $table->dropColumn([
                'company',
                'enquiry_type',
                'attachments',
                'assigned_to',
                'internal_notes',
                'source',
                'ip_address',
                'user_agent',
            ]);
        });
    }
};
