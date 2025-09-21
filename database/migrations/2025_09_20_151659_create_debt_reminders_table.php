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
        Schema::create('debt_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->comment('ID Purchase Order');
            $table->date('reminder_date')->comment('Tanggal reminder');
            $table->enum('reminder_type', ['payment_due', 'overdue', 'custom'])->comment('Tipe reminder');
            $table->string('title')->comment('Judul reminder');
            $table->text('message')->comment('Pesan reminder');
            $table->enum('status', ['pending', 'sent', 'acknowledged', 'dismissed'])->default('pending')->comment('Status reminder');
            $table->timestamp('sent_at')->nullable()->comment('Waktu dikirim');
            $table->timestamp('acknowledged_at')->nullable()->comment('Waktu diakui');
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->comment('Diakui oleh user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_reminders');
    }
};
