@extends('layouts.app')

@section('content')
<div class="h-screen">
    <!-- Page Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Point of Sale (POS)</h1>
                <p class="text-gray-600 mt-1">Sistem kasir untuk penjualan produk</p>
            </div>
            <div class="text-sm text-gray-500">
                <span>Kasir: {{ Auth::user()->name }}</span>
                <span class="mx-2">|</span>
                <span>{{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>
    </div>

    <!-- POS Interface -->
    <div class="h-full">
        <livewire:pos-kasir />
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