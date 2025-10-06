<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'price_purchase')) {
                $table->decimal('price_purchase', 15, 2)->nullable()->after('cost_price')->comment('Harga beli (alias cost_price)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'price_purchase')) {
                $table->dropColumn('price_purchase');
            }
        });
    }
};