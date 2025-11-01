@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-100 via-blue-50 to-indigo-100">
    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-xl shadow-xl border-l-4 border-emerald-400 flex items-center backdrop-blur-sm" 
             x-data="{ show: true }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-500" 
             x-transition:enter-start="opacity-0 transform translate-y-4 scale-95" 
             x-transition:enter-end="opacity-100 transform translate-y-0 scale-100" 
             x-transition:leave="transition ease-in duration-300" 
             x-transition:leave-start="opacity-100 transform translate-y-0 scale-100" 
             x-transition:leave-end="opacity-0 transform translate-y-4 scale-95" 
             x-init="setTimeout(() => show = false, 4000)">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <span class="flex-1 font-medium">{{ session('message') }}</span>
            <button @click="show = false" class="ml-3 text-white hover:text-emerald-200 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endif

    <!-- Header -->
    <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl shadow-lg p-6 mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-black">Inventory Management</h1>
                <p class="text-emerald-100 mt-1">Kelola stok dan pergerakan inventory produk secara real-time</p>
            </div>
            <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm px-4 py-3 rounded-lg border border-white border-opacity-30 transform hover:scale-105 transition-all duration-200">
                    <div class="text-xs text-emerald-100 font-medium">Total Produk</div>
                    <div class="text-xl font-bold text-black">{{ App\Product::count() }}</div>
                </div>
                <div class="bg-white bg-opacity-20 backdrop-blur-sm px-4 py-3 rounded-lg border border-white border-opacity-30 transform hover:scale-105 transition-all duration-200">
                    <div class="text-xs text-emerald-100 font-medium">Total Warehouse</div>
                    <div class="text-xl font-bold text-black">{{ App\Warehouse::count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-6">
        <!-- Warehouse Stock View Component -->
        <div class="mb-8">
            @livewire('warehouse-stock-view', [], key('warehouse-stock-view'))
        </div>
        
        <!-- Stock Form Component -->
        <div class="mb-8">
            @livewire('stock-form', [], key('stock-form'))
        </div>
        
        <!-- Stock History Component -->
        <div>
            @livewire('stock-history', [], key('stock-history'))
        </div>
    </div>
</div>
@endsection
