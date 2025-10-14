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
        // Check if permissions table exists
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name')->default('web');
                $table->timestamps();
                
                $table->unique(['name', 'guard_name']);
            });
        } else {
            // Add guard_name column if it doesn't exist
            if (!Schema::hasColumn('permissions', 'guard_name')) {
                Schema::table('permissions', function (Blueprint $table) {
                    $table->string('guard_name')->default('web')->after('name');
                    
                    // Drop existing unique index if exists
                    $table->dropUnique(['name']);
                    
                    // Add new unique index
                    $table->unique(['name', 'guard_name']);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropUnique(['name', 'guard_name']);
            $table->dropColumn('guard_name');
            
            // Add back old unique index
            $table->unique(['name']);
        });
    }
};