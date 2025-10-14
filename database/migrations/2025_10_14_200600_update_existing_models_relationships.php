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
        // Update sales table to link with sales invoices
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->after('id')->comment('Link to sales invoice');
                $table->index(['invoice_number']);
            }
            
            if (!Schema::hasColumn('sales', 'payment_method')) {
                $table->enum('payment_method', ['cash', 'qr', 'edc', 'transfer'])->nullable()->after('payment_status')->comment('Primary payment method');
            }
            
            if (!Schema::hasColumn('sales', 'cash_amount')) {
                $table->decimal('cash_amount', 15, 2)->default(0)->after('payment_method')->comment('Cash payment amount');
            }
            
            if (!Schema::hasColumn('sales', 'qr_amount')) {
                $table->decimal('qr_amount', 15, 2)->default(0)->after('cash_amount')->comment('QR payment amount');
            }
            
            if (!Schema::hasColumn('sales', 'edc_amount')) {
                $table->decimal('edc_amount', 15, 2)->default(0)->after('qr_amount')->comment('EDC payment amount');
            }
            
            if (!Schema::hasColumn('sales', 'change_amount')) {
                $table->decimal('change_amount', 15, 2)->default(0)->after('edc_amount')->comment('Change amount');
            }
        });
        
        // Update sale_items table to link with batch expirations
        Schema::table('sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_items', 'batch_expiration_id')) {
                // Explicitly reference correct table name to avoid pluralization issues
                $table->foreignId('batch_expiration_id')->nullable()->constrained('batch_expirations')->onDelete('set null')->comment('Link to batch expiration');
                $table->index(['batch_expiration_id']);
            }
            
            if (!Schema::hasColumn('sale_items', 'expired_date')) {
                $table->date('expired_date')->nullable()->after('batch_expiration_id')->comment('Product expiration date');
                $table->index(['expired_date']);
            }
        });
        
        // Update stock_movements table to link with batch expirations
        Schema::table('stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_movements', 'batch_expiration_id')) {
                // Explicitly reference correct table name to avoid pluralization issues
                $table->foreignId('batch_expiration_id')->nullable()->constrained('batch_expirations')->onDelete('set null')->comment('Link to batch expiration');
                $table->index(['batch_expiration_id']);
            }
            
            if (!Schema::hasColumn('stock_movements', 'incoming_goods_agenda_id')) {
                // incoming_goods_agenda table is singular; explicitly reference it
                $table->foreignId('incoming_goods_agenda_id')->nullable()->constrained('incoming_goods_agenda')->onDelete('set null')->comment('Link to incoming goods agenda');
                $table->index(['incoming_goods_agenda_id']);
            }
            
            if (!Schema::hasColumn('stock_movements', 'sales_invoice_id')) {
                // Explicitly reference sales_invoices table
                $table->foreignId('sales_invoice_id')->nullable()->constrained('sales_invoices')->onDelete('set null')->comment('Link to sales invoice');
                $table->index(['sales_invoice_id']);
            }
        });
        
        // Update products table to link with batch expirations
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'track_expiration')) {
                $table->boolean('track_expiration')->default(false)->after('is_active')->comment('Track product expiration');
            }
            
            if (!Schema::hasColumn('products', 'default_shelf_life')) {
                $table->integer('default_shelf_life')->nullable()->after('track_expiration')->comment('Default shelf life in days');
            }
        });
        
        // Update cash_ledger table to link with cashflow agenda
        Schema::table('cash_ledger', function (Blueprint $table) {
            if (!Schema::hasColumn('cash_ledger', 'cashflow_agenda_id')) {
                // cashflow_agenda table is singular; explicitly reference it
                $table->foreignId('cashflow_agenda_id')->nullable()->constrained('cashflow_agenda')->onDelete('set null')->comment('Link to cashflow agenda');
                $table->index(['cashflow_agenda_id']);
            }
            
            if (!Schema::hasColumn('cash_ledger', 'reference_type')) {
                $table->string('reference_type')->nullable()->after('reference_id')->comment('Reference model type');
                $table->index(['reference_type', 'reference_id']);
            }
        });
        
        // Update capital_tracking table to link with agenda
        Schema::table('capital_tracking', function (Blueprint $table) {
            if (!Schema::hasColumn('capital_tracking', 'reference_type')) {
                // capital_tracking table doesn't have reference_id; just add reference_type and index it
                $table->string('reference_type')->nullable()->comment('Reference model type');
                $table->index(['reference_type']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove columns from sales table
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'payment_method', 'cash_amount', 'qr_amount', 'edc_amount', 'change_amount']);
        });
        
        // Remove columns from sale_items table
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['batch_expiration_id']);
            $table->dropColumn(['batch_expiration_id', 'expired_date']);
        });
        
        // Remove columns from stock_movements table
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['batch_expiration_id']);
            $table->dropForeign(['incoming_goods_agenda_id']);
            $table->dropForeign(['sales_invoice_id']);
            $table->dropColumn(['batch_expiration_id', 'incoming_goods_agenda_id', 'sales_invoice_id']);
        });
        
        // Remove columns from products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['track_expiration', 'default_shelf_life']);
        });
        
        // Remove columns from cash_ledger table
        Schema::table('cash_ledger', function (Blueprint $table) {
            $table->dropForeign(['cashflow_agenda_id']);
            $table->dropColumn(['cashflow_agenda_id', 'reference_type']);
        });
        
        // Remove columns from capital_tracking table
        Schema::table('capital_tracking', function (Blueprint $table) {
            $table->dropColumn(['reference_type']);
        });
    }
};