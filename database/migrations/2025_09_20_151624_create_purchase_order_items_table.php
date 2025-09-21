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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade')->comment('ID Purchase Order');
            $table->foreignId('product_id')->nullable()->constrained()->comment('ID Produk (jika sudah ada)');
            $table->string('product_name')->comment('Nama produk');
            $table->string('product_sku')->nullable()->comment('SKU produk');
            $table->integer('quantity')->comment('Jumlah');
            $table->decimal('unit_cost', 15, 2)->comment('Harga per unit');
            $table->decimal('total_cost', 15, 2)->comment('Total harga');
            $table->integer('received_quantity')->default(0)->comment('Jumlah yang sudah diterima');
            $table->text('notes')->nullable()->comment('Catatan item');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
