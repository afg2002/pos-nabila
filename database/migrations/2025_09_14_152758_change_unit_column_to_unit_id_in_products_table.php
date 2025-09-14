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
        // First, create a default unit if it doesn't exist
        $defaultUnit = \App\ProductUnit::firstOrCreate(
            ['name' => 'Pieces'],
            [
                'abbreviation' => 'pcs',
                'description' => 'Default unit for products',
                'is_active' => true,
                'sort_order' => 1
            ]
        );
        
        Schema::table('products', function (Blueprint $table) use ($defaultUnit) {
            // Add the new unit_id column
            $table->foreignId('unit_id')->default($defaultUnit->id)->after('category');
        });
        
        // Update existing records to use the default unit
        \DB::table('products')->update(['unit_id' => $defaultUnit->id]);
        
        Schema::table('products', function (Blueprint $table) {
            // Drop the old unit column
            $table->dropColumn('unit');
            
            // Add foreign key constraint
            $table->foreign('unit_id')->references('id')->on('product_units')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['unit_id']);
            
            // Add back the old unit column
            $table->string('unit')->default('pcs')->after('category');
            
            // Update existing records to use string unit
            \DB::table('products')
                ->join('product_units', 'products.unit_id', '=', 'product_units.id')
                ->update(['products.unit' => \DB::raw('product_units.abbreviation')]);
            
            // Drop the unit_id column
            $table->dropColumn('unit_id');
        });
    }
};
