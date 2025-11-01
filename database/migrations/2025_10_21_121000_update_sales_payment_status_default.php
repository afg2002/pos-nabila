<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Standardize Sales.payment_status default to 'UNPAID' (uppercase)
        // Also migrate legacy 'pending' values to 'UNPAID'
        $connection = config('database.default');
        $driver = config("database.connections.$connection.driver");

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE sales MODIFY COLUMN payment_status VARCHAR(20) DEFAULT 'UNPAID'");
            DB::statement("UPDATE sales SET payment_status = 'UNPAID' WHERE payment_status = 'pending' OR payment_status = '' OR payment_status IS NULL");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE sales ALTER COLUMN payment_status SET DEFAULT 'UNPAID'");
            DB::statement("UPDATE sales SET payment_status = 'UNPAID' WHERE payment_status = 'pending' OR payment_status = '' OR payment_status IS NULL");
        } else {
            // Fallback: attempt a generic update for default via Schema change if DBAL is installed
            try {
                Schema::table('sales', function ($table) {
                    $table->string('payment_status', 20)->default('UNPAID')->change();
                });
            } catch (\Throwable $e) {
                // If change() isn't supported, at least update rows to UNPAID
                DB::statement("UPDATE sales SET payment_status = 'UNPAID' WHERE payment_status = 'pending' OR payment_status = '' OR payment_status IS NULL");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = config('database.default');
        $driver = config("database.connections.$connection.driver");

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE sales MODIFY COLUMN payment_status VARCHAR(20) DEFAULT 'pending'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE sales ALTER COLUMN payment_status SET DEFAULT 'pending'");
        } else {
            try {
                Schema::table('sales', function ($table) {
                    $table->string('payment_status', 20)->default('pending')->change();
                });
            } catch (\Throwable $e) {
                // No-op
            }
        }
    }
};