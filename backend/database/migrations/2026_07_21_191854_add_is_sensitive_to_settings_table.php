<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Setting keys that contain credentials, secrets, or other values that
     * must be encrypted at rest and masked in the administration interface.
     */
    private const SENSITIVE_KEYS = [
        'smtp_password',
        'smtp_username',
        'sender_email',
        'flutterwave_secret_key',
        'flutterwave_encryption_key',
        'flutterwave_webhook_secret',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('is_sensitive')->default(false)->after('is_public');
        });

        // Classify sensitive settings.
        DB::table('settings')
            ->whereIn('key', self::SENSITIVE_KEYS)
            ->update(['is_sensitive' => true]);

        // Encrypt existing plaintext values for sensitive settings.
        foreach (self::SENSITIVE_KEYS as $key) {
            $row = DB::table('settings')->where('key', $key)->first();

            if (! $row || blank($row->value)) {
                continue;
            }

            // Avoid double-encryption if the migration is re-run.
            if (str_starts_with($row->value, 'eyJpdiI6')) {
                continue;
            }

            DB::table('settings')
                ->where('key', $key)
                ->update([
                    'value' => Crypt::encryptString($row->value),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('is_sensitive');
        });
    }
};
