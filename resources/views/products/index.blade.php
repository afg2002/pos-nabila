@extends('layouts.app')

@section('title', 'Produk')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-box-open mr-3 text-blue-600 dark:text-blue-400"></i>
                        Manajemen Produk
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Kelola inventori produk, stok, dan harga dengan mudah dan efisien
                    </p>
                </div>
                
                <!-- Quick Stats -->
                <div class="grid grid-cols-3 gap-4 sm:gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ \App\Product::count() }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total Produk</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ \App\Product::where('status', 'active')->count() }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Aktif</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ \App\Product::whereRaw('current_stock < 5')->count() }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Stok Rendah</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Products Table Component -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
            <livewire:product-table key="product-table" />
        </div>
    </div>
</div>
@endsection
