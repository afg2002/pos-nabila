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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('type')->default('regular')->after('status');
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('type');
            $table->decimal('total_purchases', 15, 2)->default(0)->after('discount_percentage');
            $table->integer('total_transactions')->default(0)->after('total_purchases');
            $table->date('birth_date')->nullable()->after('total_transactions');
            $table->enum('gender', ['male', 'female'])->nullable()->after('birth_date');
            $table->boolean('is_active')->default(true)->after('gender');
            $table->text('notes')->nullable()->after('is_active');
            $table->softDeletes();
            
            // Add indexes
            $table->index(['name', 'phone']);
            $table->index('type');
            $table->index('is_active');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'discount_percentage', 
                'total_purchases',
                'total_transactions',
                'birth_date',
                'gender',
                'is_active',
                'notes'
            ]);
            $table->dropSoftDeletes();
        });
    }
};