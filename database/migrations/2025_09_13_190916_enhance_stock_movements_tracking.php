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
        Schema::table('stock_movements', function (Blueprint $table) {
            // Supplier/Vendor information
            $table->string('supplier_name')->nullable()->after('note');
            $table->string('supplier_invoice')->nullable()->after('supplier_name');
            
            // Batch and tracking info
            $table->string('batch_number')->nullable()->after('supplier_invoice');
            $table->date('expiry_date')->nullable()->after('batch_number');
            
            // Cost information
            $table->decimal('unit_cost', 15, 2)->nullable()->after('expiry_date');
            $table->decimal('total_cost', 15, 2)->nullable()->after('unit_cost');
            
            // Location and storage info
            $table->string('location')->nullable()->after('total_cost');
            $table->string('warehouse')->default('main')->after('location');
            
            // Additional metadata
            $table->json('metadata')->nullable()->after('warehouse'); // untuk data tambahan yang fleksibel
            $table->string('reason_code')->nullable()->after('metadata'); // kode alasan untuk adjustment
            
            // Approval workflow
            $table->foreignId('approved_by')->nullable()->constrained('users')->after('reason_code');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            
            // Add indexes for better performance
            $table->index(['supplier_name', 'created_at']);
            $table->index(['batch_number']);
            $table->index(['expiry_date']);
            $table->index(['warehouse', 'location']);
            $table->index(['approved_by', 'approved_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['supplier_name', 'created_at']);
            $table->dropIndex(['batch_number']);
            $table->dropIndex(['expiry_date']);
            $table->dropIndex(['warehouse', 'location']);
            $table->dropIndex(['approved_by', 'approved_at']);
            
            $table->dropColumn([
                'supplier_name',
                'supplier_invoice',
                'batch_number',
                'expiry_date',
                'unit_cost',
                'total_cost',
                'location',
                'warehouse',
                'metadata',
                'reason_code',
                'approved_by',
                'approved_at'
            ]);
        });
    }
};
