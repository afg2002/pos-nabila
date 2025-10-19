<?php $__env->startSection('content'); ?>
<div class="space-y-8 animate-fadeInUp">
    <!-- Enhanced Welcome Section with Real-time Updates -->
    <div class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 rounded-2xl shadow-2xl overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-white/10 backdrop-blur-sm">
            <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"></div>
            <div class="absolute top-0 left-0 w-full h-full">
                <div class="absolute top-1/4 left-1/4 w-32 h-32 bg-white/10 rounded-full blur-xl animate-pulse"></div>
                <div class="absolute bottom-1/4 right-1/4 w-24 h-24 bg-white/20 rounded-full blur-lg animate-pulse" style="animation-delay: 1s"></div>
            </div>
        </div>
        
        <div class="relative p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-3 bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                        Selamat datang kembali, <?php echo e(auth()->user()->name); ?>!
                    </h1>
                    <p class="text-xl text-blue-100 mb-4">Berikut adalah ringkasan aktivitas sistem Anda hari ini.</p>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-blue-200">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <?php echo e(now()->format('l, F j, Y')); ?>

                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="flex items-center">
                                <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                                System Online
                            </span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            Laravel <?php echo e(app()->version()); ?>

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

    <!-- Enhanced Stats Cards with Real-time Data -->
    <?php
        $stats = \App\Shared\Services\CacheService::getDashboardStats();
        
        // Enhanced metrics with more data
        $today = \Carbon\Carbon::today();
        $weekStart = \Carbon\Carbon::now()->subDays(7);
        $monthStart = \Carbon\Carbon::now()->startOfMonth();
        
        $todaySales = \App\Sale::whereDate('created_at', $today)->get();
        $weekSales = \App\Sale::where('created_at', '>=', $weekStart)->get();
        $monthSales = \App\Sale::where('created_at', '>=', $monthStart)->get();
        
        $todayRevenue = (float) $todaySales->sum('final_total');
        $weekRevenue = (float) $weekSales->sum('final_total');
        $monthRevenue = (float) $monthSales->sum('final_total');
        
        $totalProducts = \App\Product::count();
        $totalSuppliers = \App\Supplier::count();
        $totalWarehouses = \App\Warehouse::count();
        
        $recentTransactions = \App\Sale::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        $upcomingAgendas = \App\IncomingGoodsAgenda::with('supplier')
            ->where('status', 'scheduled')
            ->where('scheduled_date', '>=', now())
            ->orderBy('scheduled_date', 'asc')
            ->limit(3)
            ->get();
            
        $lowStockThreshold = 5;
        $criticalStock = \App\Product::where('current_stock', '<=', $lowStockThreshold)
            ->orderBy('current_stock', 'asc')
            ->limit(5)
            ->get();
    ?>
    
    <!-- Main Stats Grid -->
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
                                <p class="text-2xl font-bold text-gray-900"><?php echo e(number_format($stats['total_users'])); ?></p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-sm text-green-600 font-semibold"><?php echo e($stats['active_users']); ?> active</span>
                            </div>
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                +12% bulan ini
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Today Card -->
        <div class="group relative bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-200 hover:shadow-2xl hover:border-green-300 transition-all duration-300 transform hover:-translate-y-1 animate-slideInRight" style="animation-delay: 0.2s">
            <div class="absolute inset-0 bg-gradient-to-br from-green-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 mb-1">Omzet Hari Ini</p>
                                <p class="text-2xl font-bold text-gray-900">Rp <?php echo e(number_format($todayRevenue, 0, ',', '.')); ?></p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <span class="text-sm text-green-600 font-semibold"><?php echo e($todaySales->count()); ?> transaksi</span>
                            </div>
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <?php echo e($todaySales->count() > 0 ? '+' . round((($todayRevenue / ($weekRevenue / 7)) - 1) * 100, 1) . '%' : '0%'); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Status Card -->
        <div class="group relative bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-200 hover:shadow-2xl hover:border-purple-300 transition-all duration-300 transform hover:-translate-y-1 animate-slideInRight" style="animation-delay: 0.3s">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 mb-1">Total Produk</p>
                                <p class="text-2xl font-bold text-gray-900"><?php echo e(number_format($totalProducts)); ?></p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 <?php echo e($criticalStock->count() > 0 ? 'bg-red-500' : 'bg-green-500'); ?> rounded-full <?php echo e($criticalStock->count() > 0 ? 'animate-pulse' : ''); ?>"></div>
                                <span class="text-sm <?php echo e($criticalStock->count() > 0 ? 'text-red-600' : 'text-green-600'); ?> font-semibold">
                                    <?php echo e($criticalStock->count()); ?> kritikal
                                </span>
                            </div>
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                Aktif
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
                                <p class="text-2xl font-bold text-gray-900"><?php echo e(auth()->user()->roles->count()); ?></p>
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
                                <?php echo e(auth()->user()->name); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Main Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Enhanced Quick Actions -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Quick Actions Panel -->
            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-white px-6 py-5 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                        </div>
                    </div>
                </div>
                <div class="p-6 space-y-3">
                    <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'users.create')): ?>
                        <a href="<?php echo e(route('users.index')); ?>" class="group w-full flex items-center px-4 py-4 text-sm font-medium text-gray-700 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl hover:from-blue-100 hover:to-blue-200 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
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
                    <?php endif; ?>
                    
                    <a href="<?php echo e(route('kasir.management')); ?>" class="group w-full flex items-center px-4 py-4 text-sm font-medium text-gray-700 bg-gradient-to-r from-green-50 to-green-100 rounded-xl hover:from-green-100 hover:to-green-200 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center mr-4 group-hover:shadow-lg transition-shadow duration-300">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900 group-hover:text-green-800">Buka Kasir</p>
                            <p class="text-xs text-gray-500 group-hover:text-green-600">POS System</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 transform group-hover:translate-x-1 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    
                    <a href="<?php echo e(route('products.index')); ?>" class="group w-full flex items-center px-4 py-4 text-sm font-medium text-gray-700 bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl hover:from-purple-100 hover:to-purple-200 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center mr-4 group-hover:shadow-lg transition-shadow duration-300">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900 group-hover:text-purple-800">Kelola Produk</p>
                            <p class="text-xs text-gray-500 group-hover:text-purple-600">Inventory Management</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 transform group-hover:translate-x-1 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>

                    <!-- System Status Widget -->
                    <div class="group w-full flex items-center px-4 py-4 text-sm font-medium text-gray-700 bg-gradient-to-r from-yellow-50 to-orange-100 rounded-xl">
                        <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">System Health</p>
                            <div class="flex items-center space-x-2 mt-1">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <p class="text-xs text-green-600 font-medium">All systems operational</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Agendas Widget -->
            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-50 to-white px-6 py-5 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Jadwal Datang</h3>
                        </div>
                        <a href="<?php echo e(route('incoming-goods-agenda.index')); ?>" class="text-sm text-orange-600 hover:text-orange-800">Lihat Semua</a>
                    </div>
                </div>
                <div class="p-6 space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $upcomingAgendas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agenda): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="p-3 bg-gradient-to-r from-orange-50 to-white rounded-lg border border-orange-200">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-900"><?php echo e(optional($agenda->supplier)->name); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo e(\Carbon\Carbon::parse($agenda->scheduled_date)->format('d M Y H:i')); ?></p>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-700 rounded-full">
                                    <?php echo e($agenda->status); ?>

                                </span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-4">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm text-gray-500">Tidak ada jadwal datang</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Enhanced Business Overview -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Revenue Overview Cards -->
            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-green-50 to-white px-6 py-5 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-teal-600 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M7 13l3 3 7-7"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Ringkasan Penjualan</h3>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Revenue Summary Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="p-4 rounded-xl border border-gray-200 bg-gradient-to-br from-green-50 to-white">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-600">Hari Ini</span>
                                <span class="text-xs font-semibold bg-green-100 text-green-700 px-2 py-1 rounded"><?php echo e($today->isoFormat('D MMM')); ?></span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 mb-1">Rp <?php echo e(number_format($todayRevenue, 0, ',', '.')); ?></p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500"><?php echo e($todaySales->count()); ?> transaksi</span>
                                <span class="text-xs text-green-600 font-semibold"><?php echo e($todaySales->count() > 0 ? '+' . round((($todayRevenue / ($weekRevenue / 7)) - 1) * 100, 1) . '%' : '0%'); ?></span>
                            </div>
                        </div>
                        
                        <div class="p-4 rounded-xl border border-gray-200 bg-gradient-to-br from-blue-50 to-white">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-600">7 Hari</span>
                                <span class="text-xs font-semibold bg-blue-100 text-blue-700 px-2 py-1 rounded"><?php echo e($weekStart->isoFormat('D MMM')); ?> - <?php echo e(now()->isoFormat('D MMM')); ?></span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 mb-1">Rp <?php echo e(number_format($weekRevenue, 0, ',', '.')); ?></p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500"><?php echo e($weekSales->count()); ?> transaksi</span>
                                <span class="text-xs text-blue-600 font-semibold">Rp <?php echo e(number_format($weekRevenue / 7, 0, ',', '.')); ?>/hari</span>
                            </div>
                        </div>
                        
                        <div class="p-4 rounded-xl border border-gray-200 bg-gradient-to-br from-purple-50 to-white">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-600">Bulan Ini</span>
                                <span class="text-xs font-semibold bg-purple-100 text-purple-700 px-2 py-1 rounded"><?php echo e($monthStart->isoFormat('MMMM')); ?></span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 mb-1">Rp <?php echo e(number_format($monthRevenue, 0, ',', '.')); ?></p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500"><?php echo e($monthSales->count()); ?> transaksi</span>
                                <span class="text-xs text-purple-600 font-semibold">Rp <?php echo e(number_format($monthRevenue / max(1, now()->diffInDays($monthStart)), 0, ',', '.')); ?>/hari</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-md font-semibold text-gray-900">Transaksi Terakhir</h4>
                        </div>
                        <div class="space-y-2">
                            <?php $__empty_1 = true; $__currentLoopData = $recentTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">#<?php echo e(str_pad($sale->id, 6, '0', STR_PAD_LEFT)); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo e(optional($sale->user)->name); ?> • <?php echo e($sale->created_at->diffForHumans()); ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-900">Rp <?php echo e(number_format($sale->final_total, 0, ',', '.')); ?></p>
                                        <span class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded-full"><?php echo e($sale->payment_status); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-center py-4">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                    <p class="text-sm text-gray-500">Belum ada transaksi hari ini</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Status -->
            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-red-50 to-white px-6 py-5 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-pink-600 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Status Inventori</h3>
                        </div>
                        <a href="<?php echo e(route('products.index')); ?>" class="text-sm text-red-600 hover:text-red-800">Kelola Stok</a>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Critical Stock Alert -->
                    <?php if($criticalStock->count() > 0): ?>
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-red-800"><?php echo e($criticalStock->count()); ?> produk stok kritikal!</p>
                                    <p class="text-xs text-red-600">Segera lakukan pengadaan untuk produk berikut:</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Critical Stock Products -->
                    <div class="space-y-2">
                        <?php $__empty_1 = true; $__currentLoopData = $criticalStock; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="flex items-center justify-between p-3 bg-gradient-to-r <?php echo e(($product->current_stock ?? 0) <= 0 ? 'from-red-50 to-red-100' : 'from-yellow-50 to-yellow-100'); ?> border <?php echo e(($product->current_stock ?? 0) <= 0 ? 'border-red-200' : 'border-yellow-200'); ?> rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 <?php echo e(($product->current_stock ?? 0) <= 0 ? 'bg-red-100' : 'bg-yellow-100'); ?> rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 <?php echo e(($product->current_stock ?? 0) <= 0 ? 'text-red-600' : 'text-yellow-600'); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900"><?php echo e($product->name); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo e($product->unit_name ?? 'Satuan'); ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold <?php echo e(($product->current_stock ?? 0) <= 0 ? 'text-red-600' : 'text-yellow-600'); ?>">
                                        <?php echo e(number_format($product->current_stock ?? 0, 0, ',', '.')); ?>

                                    </p>
                                    <p class="text-xs text-gray-500">stok tersisa</p>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center py-6">
                                <svg class="w-12 h-12 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-sm text-gray-600 font-medium">Semua produk aman!</p>
                                <p class="text-xs text-gray-500">Tidak ada produk dengan stok menipis</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Bar -->
    <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-2xl border border-gray-200 p-4">
        <div class="flex flex-wrap items-center justify-center gap-3">
            <a href="<?php echo e(route('kasir.management')); ?>" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-green-700 bg-green-100 hover:bg-green-200 rounded-md transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Kasir (POS)
            </a>
            <a href="<?php echo e(route('products.index')); ?>" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-md transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                Kelola Produk
            </a>
            <a href="<?php echo e(route('products.import')); ?>" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-purple-700 bg-purple-100 hover:bg-purple-200 rounded-md transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16M4 4l8 8"></path>
                </svg>
                Import Stok
            </a>
            <a href="<?php echo e(route('suppliers.index')); ?>" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-orange-700 bg-orange-100 hover:bg-orange-200 rounded-md transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Supplier
            </a>
            <a href="<?php echo e(route('incoming-goods-agenda.index')); ?>" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-red-700 bg-red-100 hover:bg-red-200 rounded-md transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Jadwal Datang
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\laravel_livewire_RBAC_boilerplate\resources\views/dashboard.blade.php ENDPATH**/ ?>