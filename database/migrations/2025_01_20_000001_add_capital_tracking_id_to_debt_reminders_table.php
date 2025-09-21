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
        Schema::table('debt_reminders', function (Blueprint $table) {
            $table->foreignId('capital_tracking_id')->nullable()->after('purchase_order_id')->constrained('capital_tracking')->comment('Modal yang terkait');
            $table->string('debtor_name')->nullable()->after('title')->comment('Nama debitur');
            $table->decimal('amount', 15, 2)->nullable()->after('debtor_name')->comment('Jumlah hutang');
            $table->text('description')->nullable()->after('message')->comment('Deskripsi hutang');
            $table->date('due_date')->nullable()->after('reminder_date')->comment('Tanggal jatuh tempo');
            $table->text('notes')->nullable()->after('description')->comment('Catatan tambahan');
            $table->string('contact_info')->nullable()->after('notes')->comment('Info kontak debitur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('debt_reminders', function (Blueprint $table) {
            $table->dropForeign(['capital_tracking_id']);
            $table->dropColumn([
                'capital_tracking_id',
                'debtor_name',
                'amount',
                'description',
                'due_date',
                'notes',
                'contact_info'
            ]);
        });
    }
};