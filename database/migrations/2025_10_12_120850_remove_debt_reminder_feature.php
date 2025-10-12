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
        // Drop the debt_reminders table
        Schema::dropIfExists('debt_reminders');
        
        // Remove reminder-related columns from purchase_orders table
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('reminder_enabled');
            $table->dropColumn('payment_schedule_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the debt_reminders table
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
        
        // Add back reminder-related columns to purchase_orders table
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->boolean('reminder_enabled')->default(true)->after('payment_due_date')->comment('Enable payment reminders');
            $table->date('payment_schedule_date')->nullable()->after('reminder_enabled')->comment('Scheduled payment date for reminders');
        });
    }
};
