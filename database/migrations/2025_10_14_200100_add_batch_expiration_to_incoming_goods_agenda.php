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
            // Add batch expiration fields
            if (!Schema::hasColumn('incoming_goods_agenda', 'batch_number')) {
                $table->string('batch_number')->nullable()->after('supplier_id')->comment('Nomor batch untuk tracking expired date');
            }

            if (!Schema::hasColumn('incoming_goods_agenda', 'expired_date')) {
                $table->date('expired_date')->nullable()->after('batch_number')->comment('Tanggal kadaluarsa barang');
            }
            
            // Add indexes for performance
            if (Schema::hasColumn('incoming_goods_agenda', 'batch_number') && Schema::hasColumn('incoming_goods_agenda', 'expired_date')) {
                $table->index(['batch_number', 'expired_date']);
            }

            if (Schema::hasColumn('incoming_goods_agenda', 'expired_date') && Schema::hasColumn('incoming_goods_agenda', 'status')) {
                $table->index(['expired_date', 'status']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_goods_agenda', function (Blueprint $table) {
            $table->dropColumn(['batch_number', 'expired_date']);
        });
    }
};