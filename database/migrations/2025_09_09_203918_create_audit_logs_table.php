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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->constrained('users')->onDelete('cascade');
            $table->string('action'); // 'create', 'update', 'delete'
            $table->string('table_name');
            $table->unsignedBigInteger('record_id');
            $table->json('diff_json')->nullable(); // perubahan data dalam format JSON
            $table->timestamps();
            
            $table->index(['actor_id', 'created_at']);
            $table->index(['table_name', 'record_id']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
