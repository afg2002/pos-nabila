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
            // Add columns only if they don't exist
            if (!Schema::hasColumn('incoming_goods_agenda', 'purchase_order_id')) {
                $table->foreignId('purchase_order_id')->nullable()->constrained()->onDelete('set null')->comment('Link to purchase order');
                $table->index(['purchase_order_id']);
            }
            
            if (!Schema::hasColumn('incoming_goods_agenda', 'po_number')) {
                $table->string('po_number')->nullable()->after('purchase_order_id')->comment('Purchase order number');
                $table->index(['po_number']);
            }
            
            if (!Schema::hasColumn('incoming_goods_agenda', 'is_purchase_order_generated')) {
                $table->boolean('is_purchase_order_generated')->default(false)->after('po_number')->comment('Flag for auto-generated PO');
            }
            
            if (!Schema::hasColumn('incoming_goods_agenda', 'total_quantity')) {
                $table->decimal('total_quantity', 15, 2)->nullable()->after('quantity')->comment('Total quantity for simplified input');
            }
            
            if (!Schema::hasColumn('incoming_goods_agenda', 'quantity_unit')) {
                $table->string('quantity_unit')->nullable()->after('total_quantity')->comment('Unit for total quantity');
            }
            
            if (!Schema::hasColumn('incoming_goods_agenda', 'total_purchase_amount')) {
                $table->decimal('total_purchase_amount', 15, 2)->nullable()->after('unit_price')->comment('Total purchase amount for simplified input');
            }
            
            if (!Schema::hasColumn('incoming_goods_agenda', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0)->after('total_purchase_amount')->comment('Amount paid');
            }
            
            if (!Schema::hasColumn('incoming_goods_agenda', 'remaining_amount')) {
                $table->decimal('remaining_amount', 15, 2)->default(0)->after('paid_amount')->comment('Remaining payment amount');
            }
            
            if (!Schema::hasColumn('incoming_goods_agenda', 'payment_status')) {
                $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'overdue'])->default('unpaid')->after('remaining_amount')->comment('Payment status');
            }
            
            if (!Schema::hasColumn('incoming_goods_agenda', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_status')->comment('Payment completion timestamp');
            }
            
            if (!Schema::hasColumn('incoming_goods_agenda', 'expired_date')) {
                $table->date('expired_date')->nullable()->after('scheduled_date')->comment('Product expiration date');
                $table->index(['expired_date']);
            }
            
            if (!Schema::hasColumn('incoming_goods_agenda', 'batch_number')) {
                $table->string('batch_number')->nullable()->after('expired_date')->comment('Batch number for expiration tracking');
                $table->index(['batch_number']);
            }
            
            if (!Schema::hasColumn('incoming_goods_agenda', 'business_modal_id')) {
                $table->foreignId('business_modal_id')->nullable()->constrained()->onDelete('set null')->comment('Link to business modal');
            }
            
            if (!Schema::hasColumn('incoming_goods_agenda', 'source')) {
                $table->string('source')->nullable()->after('notes')->comment('Source of agenda');
            }
            
            if (!Schema::hasColumn('incoming_goods_agenda', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null')->comment('Link to warehouse');
                $table->index(['warehouse_id']);
            }
            
            if (!Schema::hasColumn('incoming_goods_agenda', 'product_id')) {
                $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null')->comment('Link to product');
                $table->index(['product_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_goods_agenda', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['purchase_order_id']);
            $table->dropForeign(['business_modal_id']);
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['product_id']);
            
            // Drop columns
            $table->dropColumn([
                'purchase_order_id',
                'po_number',
                'is_purchase_order_generated',
                'total_quantity',
                'quantity_unit',
                'total_purchase_amount',
                'paid_amount',
                'remaining_amount',
                'payment_status',
                'paid_at',
                'expired_date',
                'batch_number',
                'business_modal_id',
                'source',
                'warehouse_id',
                'product_id'
            ]);
        });
    }
};