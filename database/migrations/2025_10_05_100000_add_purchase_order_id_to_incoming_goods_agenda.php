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
            $table->foreignId('purchase_order_id')->nullable()->after('id')->constrained('purchase_orders')->onDelete('cascade')->comment('ID Purchase Order yang terkait');
            $table->enum('source', ['manual', 'purchase_order'])->default('manual')->after('purchase_order_id')->comment('Sumber agenda: manual atau dari PO');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_goods_agenda', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->dropColumn(['purchase_order_id', 'source']);
        });
    }
};