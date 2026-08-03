<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CreditService::updateLimit()/addTransaction() create CreditTransaction rows
 * without a polymorphic reference (e.g. limit_change, payment, adjustment).
 * The original migration defined `reference` as non-nullable morphs, which
 * makes those inserts fail. This makes the pair nullable to match actual usage.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->dropMorphs('reference');
        });

        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->nullableMorphs('reference');
        });
    }

    public function down(): void
    {
        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->dropMorphs('reference');
        });

        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->morphs('reference');
        });
    }
};
