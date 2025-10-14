<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the payment_status enum to include 'unpaid' and remove 'pending'
        DB::statement("ALTER TABLE incoming_goods_agenda MODIFY COLUMN payment_status ENUM('unpaid', 'partial', 'paid', 'overdue') DEFAULT 'unpaid' COMMENT 'Payment status'");
        
        // Update existing records with 'pending' to 'unpaid'
        DB::statement("UPDATE incoming_goods_agenda SET payment_status = 'unpaid' WHERE payment_status = 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_goods_agenda', function (Blueprint $table) {
            //
        });
    }
};
