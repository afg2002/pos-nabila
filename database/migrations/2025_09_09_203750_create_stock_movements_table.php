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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('qty'); // bisa negatif untuk OUT
            $table->enum('type', ['IN', 'OUT', 'ADJ']); // IN=masuk, OUT=keluar, ADJ=adjustment
            $table->string('ref_type')->nullable(); // 'sale', 'purchase', 'adjustment', etc
            $table->unsignedBigInteger('ref_id')->nullable(); // ID dari tabel referensi
            $table->text('note')->nullable();
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['product_id', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['ref_type', 'ref_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
