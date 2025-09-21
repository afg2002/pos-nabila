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
        Schema::create('capital_tracking', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nama modal usaha');
            $table->decimal('initial_amount', 15, 2)->comment('Jumlah modal awal');
            $table->decimal('current_amount', 15, 2)->comment('Jumlah modal saat ini');
            $table->text('description')->nullable()->comment('Deskripsi modal');
            $table->boolean('is_active')->default(true)->comment('Status aktif');
            $table->foreignId('created_by')->constrained('users')->comment('Dibuat oleh user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capital_tracking');
    }
};
