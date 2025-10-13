<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'sale_number')) {
                $table->string('sale_number')->unique()->after('id');
            }
            if (!Schema::hasColumn('sales', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->after('final_total');
            }
            if (!Schema::hasColumn('sales', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->after('subtotal');
            }
            if (!Schema::hasColumn('sales', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->after('tax_amount');
            }
            if (!Schema::hasColumn('sales', 'payment_amount')) {
                $table->decimal('payment_amount', 15, 2)->after('total_amount');
            }
            if (!Schema::hasColumn('sales', 'payment_method')) {
                $table->string('payment_method', 20)->after('payment_amount');
            }
            if (!Schema::hasColumn('sales', 'payment_status')) {
                $table->string('payment_status', 20)->default('pending')->after('payment_method');
            }
            if (!Schema::hasColumn('sales', 'notes')) {
                $table->text('notes')->nullable()->after('payment_status');
            }
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'total_amount',
                'subtotal',
                'tax_amount',
                'payment_amount',
                'payment_method',
                'payment_status',
                'sale_number',
                'notes'
            ]);
        });
    }
};
