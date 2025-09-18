@extends('layouts.app')

@section('title', 'Laporan Keuangan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Laporan Keuangan</h1>
                <p class="text-gray-600">Export dan analisis data keuangan sistem POS</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('financial.dashboard') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-chart-line mr-2"></i>
                    Dashboard
                </a>
                <a href="{{ route('financial.forms') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-edit mr-2"></i>
                    Form Input
                </a>
            </div>
        </div>
    </div>

    @php
        $totalReceivable = \App\Receivable::outstanding()->get()->sum(fn($rec) => $rec->amount - $rec->paid_amount);
        $currentBalance = \App\CashBalance::orderByDesc('date')->orderByDesc('type')->value('closing_balance') ?? 0;
        $pendingSupplier = \App\PaymentSchedule::whereIn('status', ['pending', 'partial', 'overdue'])->get()->sum(fn($pay) => $pay->amount - $pay->paid_amount);
        $overdueSupplier = \App\PaymentSchedule::where('due_date', '<', now())->whereIn('status', ['pending', 'partial', 'overdue'])->get()->sum(fn($pay) => $pay->amount - $pay->paid_amount);
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Piutang Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalReceivable, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-wallet text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Saldo Kas</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($currentBalance, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pembayaran Pending</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($pendingSupplier, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Hutang Overdue</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($overdueSupplier, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <livewire:financial-reports />
</div>
@endsection