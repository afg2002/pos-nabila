<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type', 20)->default('store')->comment('store, warehouse, kiosk, etc');
            $table->string('branch')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        if (! DB::table('warehouses')->where('code', 'MAIN')->exists()) {
            DB::table('warehouses')->insert([
                'name' => 'Main Store',
                'code' => 'MAIN',
                'type' => 'store',
                'branch' => null,
                'address' => null,
                'phone' => null,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
