<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_warehouse_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->integer('stock_on_hand')->default(0);
            $table->integer('reserved_stock')->default(0);
            $table->integer('safety_stock')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);
        });

        $defaultWarehouseId = DB::table('warehouses')->where('is_default', true)->value('id');

        if ($defaultWarehouseId) {
            $products = DB::table('products')->select('id', 'current_stock')->get();

            foreach ($products as $product) {
                DB::table('product_warehouse_stock')->insert([
                    'product_id' => $product->id,
                    'warehouse_id' => $defaultWarehouseId,
                    'stock_on_hand' => $product->current_stock ?? 0,
                    'reserved_stock' => 0,
                    'safety_stock' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_warehouse_stock');
    }
};
