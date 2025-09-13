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
            $table->enum('default_price_type', ['retail', 'semi_grosir', 'grosir', 'custom'])
                  ->default('retail')
                  ->after('min_margin_pct')
                  ->comment('Default price type for this product in POS');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('default_price_type');
        });
    }
};