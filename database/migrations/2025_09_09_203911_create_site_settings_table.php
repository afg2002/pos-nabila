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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name')->default('POS Nabila');
            $table->string('tagline')->default('Sistem Point of Sale Terpercaya');
            $table->text('address')->nullable();
            $table->decimal('maps_lat', 10, 8)->nullable();
            $table->decimal('maps_lng', 11, 8)->nullable();
            $table->text('maps_url')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('instagram')->nullable();
            $table->string('hero_image_url')->nullable();
            $table->json('gallery_json')->nullable();
            $table->string('copyright')->default('© 2025 POS Nabila. All rights reserved.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
