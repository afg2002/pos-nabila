<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('incoming_goods_agenda', function (Blueprint $table) {
            if (!Schema::hasColumn('incoming_goods_agenda', 'input_mode')) {
                $table->string('input_mode', 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('incoming_goods_agenda', function (Blueprint $table) {
            if (Schema::hasColumn('incoming_goods_agenda', 'input_mode')) {
                $table->dropColumn('input_mode');
            }
        });
    }
};