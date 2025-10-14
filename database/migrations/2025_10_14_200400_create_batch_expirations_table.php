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
        Schema::create('batch_expirations', function (Blueprint $table) {
            $table->id();
            // Explicitly reference the correct table name (incoming_goods_agenda)
            $table->foreignId('incoming_goods_agenda_id')->constrained('incoming_goods_agenda')->onDelete('cascade')->comment('ID agenda barang datang');
            $table->string('batch_number')->comment('Nomor batch unik');
            $table->date('expired_date')->comment('Tanggal kadaluarsa');
            $table->decimal('quantity', 15, 2)->comment('Jumlah awal batch');
            $table->decimal('remaining_quantity', 15, 2)->comment('Jumlah sisa batch');
            $table->text('notes')->nullable()->comment('Catatan tambahan');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('Dibuat oleh');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['incoming_goods_agenda_id']);
            $table->index(['batch_number']);
            $table->index(['expired_date']);
            $table->unique(['incoming_goods_agenda_id', 'batch_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_expirations');
    }
};