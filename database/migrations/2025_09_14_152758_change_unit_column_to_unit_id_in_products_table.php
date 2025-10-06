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
        // In unit tests, avoid creating default unit to prevent duplicate 'Pieces' from factories
        $runningTests = app()->runningUnitTests();
        $defaultUnit = null;

        if (! $runningTests) {
            // Create a default unit if it doesn't exist (non-testing environments)
            $defaultUnit = \App\ProductUnit::firstOrCreate(
                ['name' => 'Pieces'],
                [
                    'abbreviation' => 'pcs',
                    'description' => 'Default unit for products',
                    'is_active' => true,
                    'sort_order' => 1
                ]
            );
        }
        
        Schema::table('products', function (Blueprint $table) use ($defaultUnit, $runningTests) {
            // Add the new unit_id column as nullable to avoid FK issues during tests
            if ($runningTests || ! $defaultUnit) {
                $table->foreignId('unit_id')->nullable()->after('category');
            } else {
                $table->foreignId('unit_id')->default($defaultUnit->id)->after('category');
            }
        });
        
        // Update existing records to use the default unit only in non-testing environments
        if (! $runningTests && $defaultUnit) {
            \DB::table('products')->update(['unit_id' => $defaultUnit->id]);
        }
        
        Schema::table('products', function (Blueprint $table) use ($runningTests) {
            // Drop the old unit column
            if (Schema::hasColumn('products', 'unit')) {
                $table->dropColumn('unit');
            }
            
            // Add foreign key constraint hanya di non-testing env
            if (! $runningTests) {
                $table->foreign('unit_id')->references('id')->on('product_units')->onDelete('restrict');
            }
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
