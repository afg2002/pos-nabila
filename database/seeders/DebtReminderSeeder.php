<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\DebtReminder;
use Carbon\Carbon;

class DebtReminderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first purchase order for reference
        $purchaseOrder = \App\PurchaseOrder::first();
        if (!$purchaseOrder) {
            return;
        }

        $debtReminders = [
            [
                'purchase_order_id' => $purchaseOrder->id,
                'reminder_date' => now()->addDays(5),
                'reminder_type' => 'payment_due',
                'title' => 'Pembayaran Purchase Order Jatuh Tempo',
                'message' => 'Purchase Order ' . $purchaseOrder->po_number . ' akan jatuh tempo dalam 5 hari.',
                'status' => 'pending',
                'sent_at' => null,
                'acknowledged_at' => null,
                'acknowledged_by' => null,
            ],
            [
                'purchase_order_id' => $purchaseOrder->id,
                'reminder_date' => now()->addDays(10),
                'reminder_type' => 'overdue',
                'title' => 'Pengiriman Purchase Order Terlambat',
                'message' => 'Purchase Order ' . $purchaseOrder->po_number . ' sudah melewati tanggal pengiriman yang diharapkan.',
                'status' => 'pending',
                'sent_at' => null,
                'acknowledged_at' => null,
                'acknowledged_by' => null,
            ],
        ];

        foreach ($debtReminders as $reminder) {
            \App\DebtReminder::create($reminder);
        }
    }
}
