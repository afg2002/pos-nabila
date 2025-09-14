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
        Schema::table('products', function (Blueprint $table) {
            // Index untuk pencarian
            $table->index(['name'], 'idx_products_name');
            $table->index(['sku'], 'idx_products_sku');
            $table->index(['barcode'], 'idx_products_barcode');
            $table->index(['category'], 'idx_products_category');
            
            // Index untuk filter status
            $table->index(['status'], 'idx_products_status');
            
            // Composite index untuk query yang sering digunakan
            $table->index(['status', 'category'], 'idx_products_status_category');
            $table->index(['status', 'name'], 'idx_products_status_name');
            
            // Index untuk soft delete
            $table->index(['deleted_at'], 'idx_products_deleted_at');
            
            // Index untuk sorting
            $table->index(['created_at'], 'idx_products_created_at');
            $table->index(['updated_at'], 'idx_products_updated_at');
            
            // Index untuk relasi
            $table->index(['unit_id'], 'idx_products_unit_id');
        });
        
        Schema::table('product_units', function (Blueprint $table) {
            // Index untuk query units
            $table->index(['is_active'], 'idx_product_units_is_active');
            $table->index(['sort_order'], 'idx_product_units_sort_order');
            $table->index(['is_active', 'sort_order'], 'idx_product_units_active_sort');
        });
        
        Schema::table('stock_movements', function (Blueprint $table) {
            // Index untuk relasi dengan products
            $table->index(['product_id'], 'idx_stock_movements_product_id');
            $table->index(['product_id', 'created_at'], 'idx_stock_movements_product_date');
        });
        
        Schema::table('sale_items', function (Blueprint $table) {
            // Index untuk relasi dengan products
            $table->index(['product_id'], 'idx_sale_items_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_name');
            $table->dropIndex('idx_products_sku');
            $table->dropIndex('idx_products_barcode');
            $table->dropIndex('idx_products_category');
            $table->dropIndex('idx_products_status');
            $table->dropIndex('idx_products_status_category');
            $table->dropIndex('idx_products_status_name');
            $table->dropIndex('idx_products_deleted_at');
            $table->dropIndex('idx_products_created_at');
            $table->dropIndex('idx_products_updated_at');
            $table->dropIndex('idx_products_unit_id');
        });
        
        Schema::table('product_units', function (Blueprint $table) {
            $table->dropIndex('idx_product_units_is_active');
            $table->dropIndex('idx_product_units_sort_order');
            $table->dropIndex('idx_product_units_active_sort');
        });
        
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('idx_stock_movements_product_id');
            $table->dropIndex('idx_stock_movements_product_date');
        });
        
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex('idx_sale_items_product_id');
        });
    }
};
