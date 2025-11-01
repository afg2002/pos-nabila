<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_unit_scales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('unit_id');
            // Conversion factor: how many base units (product.unit_id) are contained in ONE of this unit
            $table->decimal('to_base_qty', 12, 6)->default(1);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'unit_id']);
            $table->index(['product_id']);
            $table->index(['unit_id']);

            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onDelete('cascade');

            $table->foreign('unit_id')
                ->references('id')->on('product_units')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_unit_scales');
    }
};