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
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade')->comment('ID transaksi penjualan');
            $table->string('invoice_number')->unique()->comment('Nomor invoice unik');
            $table->string('customer_name')->nullable()->comment('Nama customer');
            $table->string('customer_phone')->nullable()->comment('Nomor telepon customer');
            $table->decimal('subtotal', 15, 2)->comment('Subtotal sebelum pajak & diskon');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('Jumlah pajak');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Jumlah diskon');
            $table->decimal('total_amount', 15, 2)->comment('Total amount setelah pajak & diskon');
            $table->decimal('paid_amount', 15, 2)->default(0)->comment('Jumlah yang sudah dibayar');
            $table->decimal('remaining_amount', 15, 2)->default(0)->comment('Sisa pembayaran');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->comment('Status pembayaran');
            $table->enum('payment_method', ['cash', 'qr', 'edc', 'transfer'])->nullable()->comment('Metode pembayaran');
            $table->text('notes')->nullable()->comment('Catatan tambahan');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['invoice_number']);
            $table->index(['sale_id']);
            $table->index(['payment_status', 'payment_method']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoices');
    }
};