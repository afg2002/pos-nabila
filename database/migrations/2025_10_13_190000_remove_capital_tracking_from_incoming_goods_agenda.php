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
            $table->dropForeign(['capital_tracking_id']);
            $table->dropColumn('capital_tracking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_goods_agenda', function (Blueprint $table) {
            $table->foreignId('capital_tracking_id')->nullable()->constrained('capital_tracking')->comment('ID Capital Tracking untuk pembayaran');
        });
    }
};