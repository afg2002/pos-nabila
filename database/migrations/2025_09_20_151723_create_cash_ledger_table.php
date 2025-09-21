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
        Schema::create('cash_ledger', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date')->comment('Tanggal transaksi');
            $table->enum('type', ['in', 'out'])->comment('Tipe transaksi (masuk/keluar)');
            $table->enum('category', ['sales', 'purchase', 'expense', 'capital_injection', 'capital_withdrawal', 'other'])->comment('Kategori transaksi');
            $table->string('description')->comment('Deskripsi transaksi');
            $table->decimal('amount', 15, 2)->comment('Jumlah');
            $table->decimal('balance_before', 15, 2)->comment('Saldo sebelum transaksi');
            $table->decimal('balance_after', 15, 2)->comment('Saldo setelah transaksi');
            $table->string('reference_type')->nullable()->comment('Tipe referensi (sale, purchase_order, etc)');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID referensi');
            $table->foreignId('capital_tracking_id')->nullable()->constrained('capital_tracking')->comment('Modal yang terkait');
            $table->text('notes')->nullable()->comment('Catatan tambahan');
            $table->foreignId('created_by')->constrained('users')->comment('Dibuat oleh user');
            $table->timestamps();
            
            $table->index(['transaction_date', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_ledger');
    }
};
