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
        Schema::table('cashflow_agenda', function (Blueprint $table) {
            // Drop existing columns that don't match requirements
            $table->dropColumn(['retail_revenue', 'wholesale_revenue', 'cash_payment', 'total_revenue', 'total_payment']);
            
            // Add new columns according to requirements
            $table->decimal('total_omset', 15, 2)->default(0)->after('date'); // Total omset hari itu
            $table->decimal('total_ecer', 15, 2)->default(0)->after('total_omset'); // Total ecer
            $table->decimal('total_grosir', 15, 2)->default(0)->after('total_ecer'); // Total grosir
            $table->decimal('grosir_cash_hari_ini', 15, 2)->default(0)->after('total_grosir'); // Grosir cash hari ini
            $table->decimal('qr_payment_amount', 15, 2)->default(0)->after('grosir_cash_hari_ini'); // QR payment amount
            $table->decimal('edc_payment_amount', 15, 2)->default(0)->after('qr_payment_amount'); // EDC payment amount
            
            // Add created_by column for user tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('notes');
            
            // Add indexes for better performance
            $table->index(['date', 'created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashflow_agenda', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn(['total_omset', 'total_ecer', 'total_grosir', 'grosir_cash_hari_ini', 'qr_payment_amount', 'edc_payment_amount', 'created_by']);
            
            // Restore old columns
            $table->decimal('retail_revenue', 15, 2)->default(0)->after('date');
            $table->decimal('wholesale_revenue', 15, 2)->default(0)->after('retail_revenue');
            $table->decimal('cash_payment', 15, 2)->default(0)->after('wholesale_revenue');
            $table->decimal('total_revenue', 15, 2)->default(0)->after('cash_payment');
            $table->decimal('total_payment', 15, 2)->default(0)->after('total_revenue');
        });
    }
};