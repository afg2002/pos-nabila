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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('qty');
            $table->enum('price_tier', ['retail', 'grosir', 'semi_grosir', 'custom']);
            $table->decimal('unit_price', 15, 2);
            $table->text('custom_reason')->nullable(); // wajib diisi jika price_tier = custom dan below margin
            $table->decimal('margin_pct_at_sale', 5, 2)->nullable(); // margin saat penjualan
            $table->boolean('below_margin_flag')->default(false); // flag jika di bawah margin minimum
            $table->timestamps();
            
            $table->index(['sale_id']);
            $table->index(['product_id']);
            $table->index(['below_margin_flag']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
