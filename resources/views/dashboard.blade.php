@extends('layouts.app')

@section('content')
<div class="space-y-8 animate-fadeInUp">
    <!-- Modern Welcome Section with Glassmorphism -->
    <div class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 rounded-2xl shadow-2xl overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-white/10 backdrop-blur-sm">
            <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"></div>
            <div class="absolute top-0 left-0 w-full h-full">
                <div class="absolute top-1/4 left-1/4 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
                <div class="absolute bottom-1/4 right-1/4 w-24 h-24 bg-white/20 rounded-full blur-lg"></div>
            </div>
        </div>
        
        <div class="relative p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-3 bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                        Selamat datang kembali, {{ auth()->user()->name }}!
                    </h1>
                    <p class="text-xl text-blue-100 mb-4">Berikut adalah ringkasan aktivitas sistem Anda hari ini.</p>
                    <div class="flex items-center space-x-4 text-sm text-blue-200">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ now()->format('l, F j, Y') }}
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            System Online
                        </div>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Stats Cards -->
    @php
        $stats = \App\Shared\Services\CacheService::getDashboardStats();
    @endphp
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Users Card -->
        <div class="group relative bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-200 hover:shadow-2xl hover:border-blue-300 transition-all duration-300 transform hover:-translate-y-1 animate-slideInRight" style="animation-delay: 0.1s">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 mb-1">Total Pengguna</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-sm text-green-600 font-semibold">{{ $stats['active_users'] }} active</span>
                            </div>
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                +12% this month
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Roles Card -->
        <div class="group relative bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-200 hover:shadow-2xl hover:border-green-300 transition-all duration-300 transform hover:-translate-y-1 animate-slideInRight" style="animation-delay: 0.2s">
            <div class="absolute inset-0 bg-gradient-to-br from-green-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 mb-1">Total Peran</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_roles']) }}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <span class="text-sm text-green-600 font-semibold">{{ $stats['active_roles'] }} active</span>
                            </div>
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                All secure
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions Card -->
        <div class="group relative bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-200 hover:shadow-2xl hover:border-purple-300 transition-all duration-300 transform hover:-translate-y-1 animate-slideInRight" style="animation-delay: 0.3s">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 mb-1">Izin Akses</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_permissions']) }}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                <span class="text-sm text-purple-600 font-semibold">System wide</span>
                            </div>
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Protected
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Your Roles Card -->
        <div class="group relative bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-200 hover:shadow-2xl hover:border-yellow-300 transition-all duration-300 transform hover:-translate-y-1 animate-slideInRight" style="animation-delay: 0.4s">
            <div class="absolute inset-0 bg-gradient-to-br from-yellow-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 mb-1">Your Roles</p>
                                <p class="text-2xl font-bold text-gray-900">{{ auth()->user()->roles->count() }}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                <span class="text-sm text-yellow-600 font-semibold">Personal</span>
                            </div>
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ auth()->user()->name }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Quick Actions & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Modern Quick Actions -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-white px-6 py-5 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                    </div>
                </div>
                <div class="p-6 space-y-3">
                    @permission('users.create')
                        <a href="{{ route('users.index') }}" class="group w-full flex items-center px-4 py-4 text-sm font-medium text-gray-700 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl hover:from-blue-100 hover:to-blue-200 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-4 group-hover:shadow-lg transition-shadow duration-300">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 group-hover:text-blue-800">Add New User</p>
                                <p class="text-xs text-gray-500 group-hover:text-blue-600">Create and manage users</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transform group-hover:translate-x-1 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    @endpermission
                    
                    @permission('roles.create')
                        <a href="{{ route('roles.index') }}" class="group w-full flex items-center px-4 py-4 text-sm font-medium text-gray-700 bg-gradient-to-r from-green-50 to-green-100 rounded-xl hover:from-green-100 hover:to-green-200 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center mr-4 group-hover:shadow-lg transition-shadow duration-300">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 group-hover:text-green-800">Create Role</p>
                                <p class="text-xs text-gray-500 group-hover:text-green-600">Manage roles & permissions</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 transform group-hover:translate-x-1 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    @endpermission

                    <!-- System Status -->
                    <div class="group w-full flex items-center px-4 py-4 text-sm font-medium text-gray-700 bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">System Status</p>
                            <div class="flex items-center space-x-2 mt-1">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <p class="text-xs text-green-600 font-medium">All systems operational</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Store Owner Metrics: Sales & Inventory Overview -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-white px-6 py-5 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-teal-600 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M7 13l3 3 7-7"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Ringkasan Penjualan & Inventori</h3>
                        </div>
                        <div class="flex items-center space-x-2 text-sm text-gray-500">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span>Live updates</span>
                        </div>
                    </div>
                </div>

                @php
                    $today = \Carbon\Carbon::today();
                    $weekStart = \Carbon\Carbon::now()->subDays(7);

                    $todaySales = \App\Sale::whereDate('created_at', $today)->get();
                    $weekSales = \App\Sale::where('created_at', '>=', $weekStart)->get();

                    $todayTransactions = $todaySales->count();
                    $todayRevenue = (float) $todaySales->sum('final_total');
                    $todayItemsSold = (int) \App\SaleItem::whereIn('sale_id', $todaySales->pluck('id'))->sum('qty');

                    $weekTransactions = $weekSales->count();
                    $weekRevenue = (float) $weekSales->sum('final_total');
                    $weekItemsSold = (int) \App\SaleItem::whereIn('sale_id', $weekSales->pluck('id'))->sum('qty');

                    $lowStockThreshold = 5; // default threshold, dapat diubah
                    $lowStockProducts = \App\Product::where('is_active', true)
                        ->where('current_stock', '<=', $lowStockThreshold)
                        ->orderBy('current_stock', 'asc')
                        ->limit(10)
                        ->get();

                    $weekSaleIds = $weekSales->pluck('id');
                    $topProducts = \App\SaleItem::with('product')
                        ->selectRaw('product_id, SUM(qty) as total_qty, SUM(qty * unit_price) as total_revenue')
                        ->whereIn('sale_id', $weekSaleIds)
                        ->where('is_custom', false)
                        ->groupBy('product_id')
                        ->orderByDesc('total_qty')
                        ->limit(10)
                        ->get();
                @endphp

                <div class="px-6 py-6 space-y-8">
                    <!-- Sales Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-5 rounded-xl border border-gray-200 bg-gradient-to-br from-green-50 to-white">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-600">Hari Ini</p>
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-md">{{ $today->isoFormat('D MMMM') }}</span>
                            </div>
                            <div class="mt-4 grid grid-cols-3 gap-3">
                                <div>
                                    <p class="text-xs text-gray-500">Transaksi</p>
                                    <p class="text-xl font-bold text-gray-900">{{ number_format($todayTransactions, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Item Terjual</p>
                                    <p class="text-xl font-bold text-gray-900">{{ number_format($todayItemsSold, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Omzet</p>
                                    <p class="text-xl font-bold text-green-700">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-5 rounded-xl border border-gray-200 bg-gradient-to-br from-blue-50 to-white">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-600">7 Hari Terakhir</p>
                                <span class="px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-700 rounded-md">{{ $weekStart->isoFormat('D MMM') }} - {{ now()->isoFormat('D MMM') }}</span>
                            </div>
                            <div class="mt-4 grid grid-cols-3 gap-3">
                                <div>
                                    <p class="text-xs text-gray-500">Transaksi</p>
                                    <p class="text-xl font-bold text-gray-900">{{ number_format($weekTransactions, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Item Terjual</p>
                                    <p class="text-xl font-bold text-gray-900">{{ number_format($weekItemsSold, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Omzet</p>
                                    <p class="text-xl font-bold text-blue-700">Rp {{ number_format($weekRevenue, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Products -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-md font-semibold text-gray-900">Stok Menipis (≤ {{ $lowStockThreshold }} unit)</h4>
                            <a href="{{ route('products.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Kelola Produk</a>
                        </div>
                        <div class="overflow-hidden rounded-xl border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @forelse($lowStockProducts as $p)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                <a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-800">{{ $p->name }}</a>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right font-semibold {{ ($p->current_stock ?? 0) <= 0 ? 'text-red-600' : 'text-yellow-600' }}">
                                                {{ number_format($p->current_stock ?? 0, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-4 py-3 text-sm text-gray-500 text-center">Tidak ada produk dengan stok menipis.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Top Products (7 Days) -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-md font-semibold text-gray-900">Produk Terlaris (7 Hari)</h4>
                            <a href="{{ route('kasir.management') }}" class="text-sm text-green-600 hover:text-green-800">Buka Kasir</a>
                        </div>
                        <div class="overflow-hidden rounded-xl border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Omzet</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @forelse($topProducts as $t)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ optional($t->product)->name ?? 'Produk Tidak Dikenal' }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right text-gray-900">{{ number_format($t->total_qty ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2 text-sm text-right font-semibold text-gray-900">Rp {{ number_format($t->total_revenue ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-3 text-sm text-gray-500 text-center">Belum ada data penjualan untuk periode ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-green-50 border-t border-gray-100">
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('kasir.management') }}" class="inline-flex items-center px-3 py-2 text-sm font-semibold text-green-700 bg-green-100 hover:bg-green-200 rounded-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"></path></svg>
                            Kasir (POS)
                        </a>
                        <a href="{{ route('products.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-semibold text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"></path></svg>
                            Kelola Produk
                        </a>
                        <a href="{{ route('products.import') }}" class="inline-flex items-center px-3 py-2 text-sm font-semibold text-purple-700 bg-purple-100 hover:bg-purple-200 rounded-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16M4 4l8 8"></path></svg>
                            Import Stok
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection