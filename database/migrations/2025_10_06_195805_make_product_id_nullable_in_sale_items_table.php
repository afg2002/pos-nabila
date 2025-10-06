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
        Schema::table('sale_items', function (Blueprint $table) {
            // Make product_id nullable to support custom items
            $table->foreignId('product_id')->nullable()->change();
            
            // Add fields for custom items
            $table->string('custom_item_name')->nullable()->after('product_id');
            $table->text('custom_item_description')->nullable()->after('custom_item_name');
            $table->boolean('is_custom')->default(false)->after('custom_item_description');
            
            // Add index for custom items
            $table->index(['is_custom']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Remove custom item fields
            $table->dropColumn(['custom_item_name', 'custom_item_description', 'is_custom']);
            
            // Make product_id not nullable again
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
