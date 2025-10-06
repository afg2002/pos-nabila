<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            }
            if (!Schema::hasColumn('purchase_orders', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
            }
            if (!Schema::hasColumn('purchase_orders', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'supplier_id')) {
                $table->dropConstrainedForeignId('supplier_id');
            }
            if (Schema::hasColumn('purchase_orders', 'warehouse_id')) {
                $table->dropConstrainedForeignId('warehouse_id');
            }
            if (Schema::hasColumn('purchase_orders', 'cancellation_reason')) {
                $table->dropColumn('cancellation_reason');
            }
        });
    }
};