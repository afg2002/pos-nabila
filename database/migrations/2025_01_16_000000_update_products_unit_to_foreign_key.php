<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\ProductUnit;
use App\Product;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan ada unit default
        $defaultUnit = ProductUnit::firstOrCreate(
            ['name' => 'Pieces'],
            [
                'abbreviation' => 'pcs',
                'description' => 'Default unit for counting items',
                'is_active' => true,
                'sort_order' => 1
            ]
        );

        // Tambah kolom unit_id sementara
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable()->after('unit');
            $table->foreign('unit_id')->references('id')->on('product_units')->onDelete('set null');
        });

        // Migrasi data existing
        $products = Product::all();
        foreach ($products as $product) {
            // Cari atau buat unit berdasarkan string unit yang ada
            $unitName = ucfirst(strtolower($product->unit));
            $unitAbbr = strtolower($product->unit);
            
            $unit = ProductUnit::firstOrCreate(
                ['abbreviation' => $unitAbbr],
                [
                    'name' => $unitName,
                    'description' => "Unit {$unitName}",
                    'is_active' => true,
                    'sort_order' => 99
                ]
            );
            
            $product->update(['unit_id' => $unit->id]);
        }

        // Hapus kolom unit lama
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tambah kembali kolom unit string
        Schema::table('products', function (Blueprint $table) {
            $table->string('unit')->default('pcs')->after('category');
        });

        // Migrasi data kembali
        $products = Product::with('unit')->get();
        foreach ($products as $product) {
            if ($product->unit) {
                $product->update(['unit' => $product->unit->abbreviation]);
            }
        }

        // Hapus foreign key dan kolom unit_id
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};