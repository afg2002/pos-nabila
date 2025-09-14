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
        Schema::table('products', function (Blueprint $table) {
            // Add soft delete column
            $table->softDeletes();
            
            // Add status column with enum values
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active')->after('current_stock');
            
            // Add index for better performance
            $table->index(['status', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop index first
            $table->dropIndex(['status', 'deleted_at']);
            
            // Drop columns
            $table->dropSoftDeletes();
            $table->dropColumn('status');
        });
    }
};
