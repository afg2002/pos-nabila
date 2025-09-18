@extends('layouts.app')

@section('title', 'Form Pencatatan Keuangan')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Form Pencatatan Keuangan</h1>
            <p class="text-muted">Catat transaksi kas, pembayaran, dan piutang</p>
        </div>
        <div class="d-flex align-items-center space-x-3">
            <a href="{{ route('financial.dashboard') }}" class="btn btn-outline-primary">
                <i class="fas fa-chart-line me-2"></i>Dashboard Keuangan
            </a>
            <a href="{{ route('agenda.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-calendar me-2"></i>Agenda
            </a>
        </div>
    </div>

    @livewire('financial-forms')

    @php
        $todayCash = \App\CashBalance::whereDate('date', today())->orderByDesc('type')->value('closing_balance') ?? \App\CashBalance::orderByDesc('date')->orderByDesc('type')->value('closing_balance') ?? 0;
        $todayPayments = \App\PaymentSchedule::whereDate('paid_date', today())->sum('paid_amount');
        $activeReceivables = \App\Receivable::outstanding()->get()->sum(fn($rec) => $rec->amount - $rec->paid_amount);
    @endphp

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Saldo Kas Terkini</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($todayCash, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Pembayaran Supplier Hari Ini</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($todayPayments, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Piutang Aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($activeReceivables, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection