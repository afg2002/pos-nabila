@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col">
    <!-- Page Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Point of Sale (POS)</h1>
                <p class="text-gray-600 mt-1">Sistem kasir untuk penjualan produk</p>
            </div>
            <div class="flex items-center gap-3 text-sm text-gray-500">
                <div class="flex items-center gap-2">
                    <span>Kasir: {{ Auth::user()->name }}</span>
                    <span class="mx-2">|</span>
                    <span>{{ now()->format('d/m/Y H:i') }}</span>
                </div>
                <a href="{{ route('kasir.management') }}"
                   class="btn-primary flex items-center gap-2 px-4 py-2 text-sm font-semibold">
                    <i class="fas fa-history"></i>
                    <span>Riwayat Kasir</span>
                </a>
            </div>
        </div>
    </div>

    <!-- POS Interface -->
    <div class="flex-1 min-h-0 pb-24 md:pb-0">
        <livewire:pos-responsive />
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #receipt, #receipt * {
            visibility: visible;
        }
        #receipt {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
</style>
@endpush
