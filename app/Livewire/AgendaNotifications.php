<?php

namespace App\Livewire;

use App\IncomingGoods;
use App\PaymentSchedule;
use App\CashBalance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\Attributes\On;

class AgendaNotifications extends Component
{
    public $notifications = [];
    public $unreadCount = 0;
    public $showNotifications = false;
    public $autoRefresh = true;
    
    // Notification types
    public $showIncoming = true;
    public $showPayments = true;
    public $showOverdue = true;
    public $showFinancial = true;

    public function mount()
    {
        // Check authorization
        if (!Gate::allows('agenda.view')) {
            abort(403, 'Unauthorized access to agenda notifications');
        }
        
        $this->loadNotifications();
    }

    #[On('refresh-notifications')]
    public function refreshNotifications()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $notifications = collect();
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        $nextWeek = Carbon::today()->addWeek();

        // 1. Barang yang akan datang hari ini
        if ($this->showIncoming) {
            $incomingToday = IncomingGoods::where('expected_date', $today)
                ->where('status', 'pending')
                ->get();

            foreach ($incomingToday as $item) {
                $notifications->push([
                    'id' => 'incoming_' . $item->id,
                    'type' => 'incoming_today',
                    'priority' => 'high',
                    'title' => 'Barang Datang Hari Ini',
                    'message' => "{$item->supplier_name} - {$item->invoice_number}",
                    'amount' => $item->total_cost,
                    'time' => $item->expected_date,
                    'icon' => 'truck',
                    'color' => 'blue',
                    'action' => 'mark_arrived',
                    'data' => $item
                ]);
            }

            // Barang yang akan datang besok
            $incomingTomorrow = IncomingGoods::where('expected_date', $tomorrow)
                ->where('status', 'pending')
                ->get();

            foreach ($incomingTomorrow as $item) {
                $notifications->push([
                    'id' => 'incoming_tomorrow_' . $item->id,
                    'type' => 'incoming_tomorrow',
                    'priority' => 'medium',
                    'title' => 'Barang Datang Besok',
                    'message' => "{$item->supplier_name} - {$item->invoice_number}",
                    'amount' => $item->total_cost,
                    'time' => $item->expected_date,
                    'icon' => 'clock',
                    'color' => 'yellow',
                    'action' => 'view_detail',
                    'data' => $item
                ]);
            }
        }

        // 2. Pembayaran yang jatuh tempo hari ini
        if ($this->showPayments) {
            $paymentsToday = PaymentSchedule::where('due_date', $today)
                ->where('status', 'pending')
                ->with('incomingGoods')
                ->get();

            foreach ($paymentsToday as $payment) {
                $notifications->push([
                    'id' => 'payment_today_' . $payment->id,
                    'type' => 'payment_today',
                    'priority' => 'high',
                    'title' => 'Pembayaran Jatuh Tempo Hari Ini',
                    'message' => "Supplier: {$payment->incomingGoods->supplier_name}",
                    'amount' => max($payment->amount - $payment->paid_amount, 0),
                    'time' => $payment->due_date,
                    'icon' => 'credit-card',
                    'color' => 'red',
                    'action' => 'add_payment',
                    'data' => $payment
                ]);
            }

            // Pembayaran yang akan jatuh tempo minggu depan
            $paymentsNextWeek = PaymentSchedule::whereBetween('due_date', [$tomorrow, $nextWeek])
                ->where('status', 'pending')
                ->with('incomingGoods')
                ->get();

            foreach ($paymentsNextWeek as $payment) {
                $notifications->push([
                    'id' => 'payment_week_' . $payment->id,
                    'type' => 'payment_upcoming',
                    'priority' => 'medium',
                    'title' => 'Pembayaran Minggu Depan',
                    'message' => "Supplier: {$payment->incomingGoods->supplier_name}",
                    'amount' => max($payment->amount - $payment->paid_amount, 0),
                    'time' => $payment->due_date,
                    'icon' => 'calendar',
                    'color' => 'yellow',
                    'action' => 'view_detail',
                    'data' => $payment
                ]);
            }
        }

        // 3. Pembayaran yang sudah lewat jatuh tempo (overdue)
        if ($this->showOverdue) {
            $overduePayments = PaymentSchedule::where('due_date', '<', $today)
                ->where('status', 'pending')
                ->with('incomingGoods')
                ->get();

            foreach ($overduePayments as $payment) {
                $daysOverdue = $today->diffInDays($payment->due_date);
                $notifications->push([
                    'id' => 'overdue_' . $payment->id,
                    'type' => 'payment_overdue',
                    'priority' => 'critical',
                    'title' => "Pembayaran Terlambat ({$daysOverdue} hari)",
                    'message' => "Supplier: {$payment->incomingGoods->supplier_name}",
                    'amount' => max($payment->amount - $payment->paid_amount, 0),
                    'time' => $payment->due_date,
                    'icon' => 'exclamation-triangle',
                    'color' => 'red',
                    'action' => 'urgent_payment',
                    'data' => $payment
                ]);
            }
        }

        // 4. Kondisi keuangan kritis
        if ($this->showFinancial) {
            $currentBalance = CashBalance::getCurrentBalance();
            $totalDebt = PaymentSchedule::where('status', 'pending')->sum('amount');
            $financialCondition = $currentBalance - $totalDebt;

            if ($financialCondition < 0) {
                $notifications->push([
                    'id' => 'financial_critical',
                    'type' => 'financial_alert',
                    'priority' => 'critical',
                    'title' => 'Kondisi Keuangan Kritis',
                    'message' => 'Saldo kas tidak mencukupi untuk melunasi piutang',
                    'amount' => abs($financialCondition),
                    'time' => now(),
                    'icon' => 'exclamation-circle',
                    'color' => 'red',
                    'action' => 'view_financial',
                    'data' => [
                        'balance' => $currentBalance,
                        'debt' => $totalDebt,
                        'deficit' => $financialCondition
                    ]
                ]);
            } elseif ($financialCondition < 1000000) { // Kurang dari 1 juta
                $notifications->push([
                    'id' => 'financial_warning',
                    'type' => 'financial_warning',
                    'priority' => 'medium',
                    'title' => 'Peringatan Keuangan',
                    'message' => 'Saldo kas setelah piutang rendah',
                    'amount' => $financialCondition,
                    'time' => now(),
                    'icon' => 'exclamation-triangle',
                    'color' => 'yellow',
                    'action' => 'view_financial',
                    'data' => [
                        'balance' => $currentBalance,
                        'debt' => $totalDebt,
                        'remaining' => $financialCondition
                    ]
                ]);
            }
        }

        // Sort by priority and time
        $this->notifications = $notifications->sortBy([
            ['priority', 'desc'],
            ['time', 'asc']
        ])->values()->all();

        $this->unreadCount = count($this->notifications);
    }

    public function toggleNotifications()
    {
        $this->showNotifications = !$this->showNotifications;
        if ($this->showNotifications) {
            $this->loadNotifications();
        }
    }

    public function markAsRead($notificationId)
    {
        $this->notifications = array_filter($this->notifications, function($notification) use ($notificationId) {
            return $notification['id'] !== $notificationId;
        });
        $this->unreadCount = count($this->notifications);
    }

    public function markAllAsRead()
    {
        $this->notifications = [];
        $this->unreadCount = 0;
    }

    public function handleAction($notificationId, $action)
    {
        $notification = collect($this->notifications)->firstWhere('id', $notificationId);
        
        if (!$notification) return;

        switch ($action) {
            case 'mark_arrived':
                $this->markItemArrived($notification['data']);
                break;
            case 'add_payment':
                $this->redirectToPayment($notification['data']);
                break;
            case 'urgent_payment':
                $this->redirectToUrgentPayment($notification['data']);
                break;
            case 'view_financial':
                $this->redirectToFinancial();
                break;
            case 'view_detail':
                $this->redirectToDetail($notification['data']);
                break;
        }

        $this->markAsRead($notificationId);
    }

    private function markItemArrived($item)
    {
        $item->update([
            'status' => 'arrived',
            'actual_arrival_date' => now(),
            'updated_by' => auth()->id(),
        ]);
        $this->dispatch('item-arrived', ['id' => $item->id]);
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Barang berhasil ditandai sebagai tiba'
        ]);
    }

    private function redirectToPayment($payment)
    {
        $this->dispatch('open-payment-modal', ['paymentId' => $payment->id]);
    }

    private function redirectToUrgentPayment($payment)
    {
        $this->dispatch('open-urgent-payment-modal', ['paymentId' => $payment->id]);
    }

    private function redirectToFinancial()
    {
        return redirect()->route('agenda.financial');
    }

    private function redirectToDetail($data)
    {
        if ($data instanceof IncomingGoods) {
            $this->dispatch('open-incoming-detail', ['id' => $data->id]);
        } elseif ($data instanceof PaymentSchedule) {
            $this->dispatch('open-payment-detail', ['id' => $data->id]);
        }
    }

    public function toggleFilter($type)
    {
        $this->$type = !$this->$type;
        $this->loadNotifications();
    }

    public function getPriorityColor($priority)
    {
        return match($priority) {
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'blue',
            default => 'gray'
        };
    }

    public function render()
    {
        return view('livewire.agenda-notifications');
    }
}
