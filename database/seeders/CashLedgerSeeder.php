<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\CashLedger;
use App\CapitalTracking;
use Carbon\Carbon;

class CashLedgerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada capital tracking
        $capitalTracking = CapitalTracking::first();
        
        if (!$capitalTracking) {
            $capitalTracking = CapitalTracking::create([
                'name' => 'Modal Utama Toko',
                'description' => 'Modal utama untuk operasional toko sehari-hari',
                'initial_amount' => 50000000,
                'current_amount' => 50000000,
                'is_active' => true,
                'created_by' => 1,
            ]);
        }

        $cashLedgerEntries = [
            [
                'transaction_date' => Carbon::now()->subDays(10),
                'type' => 'in',
                'category' => 'sales',
                'amount' => 2500000,
                'description' => 'Penjualan produk elektronik',
                'balance_before' => 50000000,
                'balance_after' => 52500000,
                'created_by' => 1,
            ],
            [
                'transaction_date' => Carbon::now()->subDays(9),
                'type' => 'out',
                'category' => 'purchase',
                'amount' => 1500000,
                'description' => 'Pembelian stok barang',
                'balance_before' => 52500000,
                'balance_after' => 51000000,
                'created_by' => 1,
            ],
            [
                'transaction_date' => Carbon::now()->subDays(8),
                'type' => 'in',
                'category' => 'sales',
                'amount' => 3200000,
                'description' => 'Penjualan produk fashion',
                'balance_before' => 51000000,
                'balance_after' => 54200000,
                'created_by' => 1,
            ],
            [
                'transaction_date' => Carbon::now()->subDays(7),
                'type' => 'out',
                'category' => 'expense',
                'amount' => 500000,
                'description' => 'Biaya operasional toko',
                'balance_before' => 54200000,
                'balance_after' => 53700000,
                'created_by' => 1,
            ],
            [
                'transaction_date' => Carbon::now()->subDays(6),
                'type' => 'in',
                'category' => 'sales',
                'amount' => 1800000,
                'description' => 'Penjualan produk rumah tangga',
                'balance_before' => 53700000,
                'balance_after' => 55500000,
                'created_by' => 1,
            ],
            [
                'transaction_date' => Carbon::now()->subDays(5),
                'type' => 'out',
                'category' => 'purchase',
                'amount' => 2200000,
                'description' => 'Pembelian stok baru',
                'balance_before' => 55500000,
                'balance_after' => 53300000,
                'created_by' => 1,
            ],
            [
                'transaction_date' => Carbon::now()->subDays(4),
                'type' => 'in',
                'category' => 'sales',
                'amount' => 2800000,
                'description' => 'Penjualan produk olahraga',
                'balance_before' => 53300000,
                'balance_after' => 56100000,
                'created_by' => 1,
            ],
            [
                'transaction_date' => Carbon::now()->subDays(3),
                'type' => 'out',
                'category' => 'expense',
                'amount' => 300000,
                'description' => 'Biaya listrik dan air',
                'balance_before' => 56100000,
                'balance_after' => 55800000,
                'created_by' => 1,
            ],
        ];

        foreach ($cashLedgerEntries as $ledger) {
            $ledger['capital_tracking_id'] = $capitalTracking->id;
            CashLedger::create($ledger);
        }
    }
}
