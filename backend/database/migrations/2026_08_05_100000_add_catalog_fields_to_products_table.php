<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->nullable()->after('distributor_price');
            $table->string('currency', 3)->nullable()->after('cost_price');
            $table->string('cost_currency', 3)->nullable()->after('currency');
            $table->unsignedInteger('low_stock_threshold')->nullable()->after('stock_quantity');
            $table->string('stock_status')->nullable()->after('low_stock_threshold');
            $table->string('unit')->nullable()->after('stock_status');
            $table->decimal('weight', 10, 3)->nullable()->after('unit');
            $table->string('barcode')->nullable()->after('weight');
            $table->decimal('tax_rate', 5, 2)->nullable()->after('barcode');
            $table->foreignId('created_by')->nullable()->after('meta_description')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn([
                'cost_price',
                'currency',
                'cost_currency',
                'low_stock_threshold',
                'stock_status',
                'unit',
                'weight',
                'barcode',
                'tax_rate',
            ]);
        });
    }
};
