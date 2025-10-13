<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incoming_goods_agenda', function (Blueprint $table) {
            // Add supplier_id to link with suppliers table
            $table->foreignId('supplier_id')->nullable()->after('id')->constrained('suppliers')->onDelete('cascade')->comment('ID Supplier dari tabel suppliers');
            
            // Add simplified input fields
            $table->decimal('total_quantity', 15, 2)->nullable()->after('supplier_id')->comment('Total jumlah barang (input langsung)');
            $table->string('quantity_unit')->nullable()->after('total_quantity')->comment('Satuan untuk total quantity');
            $table->decimal('total_purchase_amount', 15, 2)->nullable()->after('total_amount')->comment('Jumlah total belanja (input langsung)');
            
            // Add indexes for performance
            $table->index(['supplier_id']);
            $table->index(['scheduled_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_goods_agenda', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['supplier_id', 'total_quantity', 'quantity_unit', 'total_purchase_amount']);
        });
    }
};