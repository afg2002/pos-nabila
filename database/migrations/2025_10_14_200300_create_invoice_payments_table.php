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
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('sales_invoices')->onDelete('cascade')->comment('ID invoice');
            $table->decimal('amount', 15, 2)->comment('Jumlah pembayaran');
            $table->enum('payment_method', ['cash', 'qr', 'edc', 'transfer'])->comment('Metode pembayaran');
            $table->timestamp('payment_date')->comment('Tanggal pembayaran');
            $table->text('notes')->nullable()->comment('Catatan pembayaran');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('Dibuat oleh');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['invoice_id']);
            $table->index(['payment_method', 'payment_date']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};