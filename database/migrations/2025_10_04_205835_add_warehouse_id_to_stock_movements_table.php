<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('warehouse')->constrained('warehouses');
        });

        $existingWarehouses = DB::table('warehouses')->pluck('id', 'code');

        $warehouseStrings = DB::table('stock_movements')
            ->whereNotNull('warehouse')
            ->distinct()
            ->pluck('warehouse');

        $defaultWarehouseId = null;

        foreach ($warehouseStrings as $warehouseString) {
            $code = strtoupper(Str::slug($warehouseString ?: 'main', '_'));

            if (! isset($existingWarehouses[$code])) {
                $isDefault = $defaultWarehouseId === null && in_array($warehouseString, ['main', 'store', 'toko']);

                $id = DB::table('warehouses')->insertGetId([
                    'name' => ucwords(str_replace(['_', '-'], ' ', $warehouseString ?: 'Main Store')),
                    'code' => $code,
                    'type' => $warehouseString && in_array(strtolower($warehouseString), ['store', 'toko']) ? 'store' : 'warehouse',
                    'branch' => null,
                    'address' => null,
                    'phone' => null,
                    'is_default' => $isDefault,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $existingWarehouses[$code] = $id;
            }

            $defaultWarehouseId = $defaultWarehouseId ?? $existingWarehouses[$code];

            DB::table('stock_movements')
                ->where('warehouse', $warehouseString)
                ->update(['warehouse_id' => $existingWarehouses[$code]]);
        }

        if ($defaultWarehouseId === null) {
            // Check if MAIN warehouse already exists
            $mainWarehouse = DB::table('warehouses')->where('code', 'MAIN')->first();
            
            if ($mainWarehouse) {
                $defaultWarehouseId = $mainWarehouse->id;
            } else {
                $defaultWarehouseId = DB::table('warehouses')->insertGetId([
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

        DB::table('stock_movements')->whereNull('warehouse_id')->update(['warehouse_id' => $defaultWarehouseId]);
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warehouse_id');
        });
    }
};
