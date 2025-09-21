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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique()->comment('Nomor PO');
            $table->string('supplier_name')->comment('Nama supplier');
            $table->text('supplier_contact')->nullable()->comment('Kontak supplier');
            $table->date('order_date')->comment('Tanggal order');
            $table->date('expected_delivery_date')->nullable()->comment('Tanggal pengiriman diharapkan');
            $table->date('actual_delivery_date')->nullable()->comment('Tanggal pengiriman aktual');
            $table->date('payment_due_date')->nullable()->comment('Tanggal jatuh tempo pembayaran');
            $table->decimal('total_amount', 15, 2)->comment('Total jumlah');
            $table->decimal('paid_amount', 15, 2)->default(0)->comment('Jumlah yang sudah dibayar');
            $table->enum('status', ['pending', 'ordered', 'delivered', 'paid', 'cancelled'])->default('pending')->comment('Status PO');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->comment('Status pembayaran');
            $table->text('notes')->nullable()->comment('Catatan');
            $table->foreignId('capital_tracking_id')->nullable()->constrained('capital_tracking')->comment('Modal yang digunakan');
            $table->foreignId('created_by')->constrained('users')->comment('Dibuat oleh user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
