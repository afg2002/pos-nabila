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
        Schema::create('incoming_goods_agenda', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_name')->comment('Nama supplier');
            $table->string('goods_name')->comment('Nama barang');
            $table->text('description')->nullable()->comment('Deskripsi barang');
            $table->integer('quantity')->comment('Jumlah barang');
            $table->string('unit')->comment('Satuan barang');
            $table->decimal('unit_price', 15, 2)->comment('Harga per unit');
            $table->decimal('total_amount', 15, 2)->comment('Total harga');
            $table->date('scheduled_date')->comment('Tanggal jadwal barang masuk');
            $table->date('payment_due_date')->comment('Tanggal jatuh tempo pembayaran');
            $table->enum('status', ['scheduled', 'received', 'paid', 'cancelled'])->default('scheduled')->comment('Status agenda');
            $table->text('notes')->nullable()->comment('Catatan tambahan');
            $table->string('contact_person')->nullable()->comment('Kontak person supplier');
            $table->string('phone_number')->nullable()->comment('Nomor telepon');
            $table->decimal('paid_amount', 15, 2)->default(0)->comment('Jumlah yang sudah dibayar');
            $table->foreignId('capital_tracking_id')->nullable()->constrained('capital_tracking')->comment('ID Capital Tracking untuk pembayaran');
            $table->timestamp('received_at')->nullable()->comment('Waktu barang diterima');
            $table->timestamp('paid_at')->nullable()->comment('Waktu pembayaran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_goods_agenda');
    }
};
