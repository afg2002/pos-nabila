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
        Schema::create('incoming_goods', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('supplier_name');
            $table->string('supplier_contact')->nullable();
            $table->date('expected_date');
            $table->date('actual_arrival_date')->nullable();
            $table->decimal('total_cost', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_debt', 15, 2);
            $table->enum('status', ['pending', 'arrived', 'partial_paid', 'fully_paid'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['expected_date', 'status']);
            $table->index(['supplier_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_goods');
    }
};
