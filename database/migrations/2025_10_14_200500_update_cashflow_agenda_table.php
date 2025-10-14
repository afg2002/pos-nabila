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
            // Add integration fields
            // Reference the correct table name for cash ledger
            $table->foreignId('cash_ledger_id')->nullable()->constrained('cash_ledger')->onDelete('set null')->comment('Link ke cash ledger');
            $table->decimal('total_expenses', 15, 2)->default(0)->after('total_omset')->comment('Total pengeluaran hari ini');
            
            // Add computed column for net cashflow (MySQL 5.7+)
            if (DB::connection()->getDriverName() === 'mysql') {
                $table->decimal('net_cashflow', 15, 2)->generatedAs('total_omset - total_expenses')->comment('Net cashflow otomatis');
            }
            
            // Add indexes for performance
            $table->index(['cash_ledger_id']);
            $table->index(['date', 'total_expenses']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashflow_agenda', function (Blueprint $table) {
            $table->dropColumn(['cash_ledger_id', 'total_expenses']);
            
            // Drop computed column if exists
            if (Schema::hasColumn('cashflow_agenda', 'net_cashflow')) {
                $table->dropColumn('net_cashflow');
            }
        });
    }
};