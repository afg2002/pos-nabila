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
        Schema::create('agenda_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->time('event_time')->nullable();
            $table->enum('event_type', ['meeting', 'reminder', 'task', 'appointment', 'deadline', 'other'])->default('reminder');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->string('location')->nullable();
            $table->json('attendees')->nullable(); // Array of attendee names/emails
            $table->text('notes')->nullable();
            $table->integer('reminder_minutes')->default(15); // Minutes before event to remind
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['event_date', 'event_time']);
            $table->index(['event_type', 'status']);
            $table->index(['priority', 'event_date']);
            $table->index(['created_by', 'event_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_events');
    }
};