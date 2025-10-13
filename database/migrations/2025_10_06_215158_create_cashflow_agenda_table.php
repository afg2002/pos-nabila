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
        Schema::create('cashflow_agenda', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('retail_revenue', 15, 2)->default(0);
            $table->decimal('wholesale_revenue', 15, 2)->default(0);
            $table->decimal('cash_payment', 15, 2)->default(0);
            $table->decimal('qr_payment', 15, 2)->default(0);
            $table->decimal('edc_payment', 15, 2)->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('total_payment', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('capital_tracking_id');
            $table->timestamps();

            $table->foreign('capital_tracking_id')->references('id')->on('capital_tracking')->onDelete('cascade');
            $table->index(['date', 'capital_tracking_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashflow_agenda');
    }
};
