<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\PurchaseOrder;
use App\Domains\User\Models\User;
use App\CapitalTracking;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user for created_by
        $user = User::first();
        if (!$user) {
            return;
        }

        // Get first capital tracking for reference
        $capitalTracking = CapitalTracking::first();
        if (!$capitalTracking) {
            return;
        }

        $purchaseOrders = [
            [
                'po_number' => 'PO-2024-001',
                'supplier_name' => 'PT Supplier Utama',
                'supplier_contact' => '081234567890',
                'order_date' => now()->subDays(30),
                'expected_delivery_date' => now()->subDays(25),
                'actual_delivery_date' => now()->subDays(23),
                'payment_due_date' => now()->subDays(15),
                'total_amount' => 5000000,
                'paid_amount' => 2500000,
                'status' => 'delivered',
                'payment_status' => 'partial',
                'notes' => 'Pembelian stok obat-obatan',
                'capital_tracking_id' => $capitalTracking->id,
                'created_by' => $user->id,
            ],
            [
                'po_number' => 'PO-2024-002',
                'supplier_name' => 'CV Distributor Medis',
                'supplier_contact' => '081987654321',
                'order_date' => now()->subDays(20),
                'expected_delivery_date' => now()->subDays(15),
                'actual_delivery_date' => null,
                'payment_due_date' => now()->addDays(10),
                'total_amount' => 3000000,
                'paid_amount' => 0,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'notes' => 'Pembelian alat kesehatan',
                'capital_tracking_id' => $capitalTracking->id,
                'created_by' => $user->id,
            ],
            [
                'po_number' => 'PO-2024-003',
                'supplier_name' => 'Toko Farmasi Jaya',
                'supplier_contact' => '081555666777',
                'order_date' => now()->subDays(10),
                'expected_delivery_date' => now()->addDays(5),
                'actual_delivery_date' => null,
                'payment_due_date' => now()->addDays(20),
                'total_amount' => 1500000,
                'paid_amount' => 1500000,
                'status' => 'ordered',
                'payment_status' => 'paid',
                'notes' => 'Pembelian vitamin dan suplemen',
                'capital_tracking_id' => $capitalTracking->id,
                'created_by' => $user->id,
            ],
        ];

        foreach ($purchaseOrders as $po) {
            PurchaseOrder::create($po);
        }
    }
}
