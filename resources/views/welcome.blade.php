<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Toko Segar — Belanja Segar dari Cibinong</title>
    <meta name="description" content="Toko Segar di Cibinong — Belanja kebutuhan harian & produk segar berkualitas. Mudah, cepat, dan harga bersahabat.">
    <meta name="keywords" content="POS, Point of Sale, sistem kasir, manajemen inventori, laporan penjualan, retail, bisnis">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- Lokal: nonaktifkan Google Fonts untuk melokalkan aset -->
    <!-- <link rel="preconnect" href="https://fonts.googleapis.com"> -->
    <!-- <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> -->
    <!-- <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"> -->
    <style>
        * {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
        }
        /* Latar belakang halaman solid biru (tanpa gradient) */
        body { background: #0B66FF; }
        
        .gradient-bg {
            background: #0B66FF; /* solid high-contrast blue, no gradient */
            position: relative;
            overflow: hidden;
        }
        
        .glass-effect {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .glass-dark {
            backdrop-filter: blur(20px);
            background: rgba(30, 58, 138, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            25% { transform: translateY(-10px) rotate(1deg); }
            50% { transform: translateY(-20px) rotate(0deg); }
            75% { transform: translateY(-10px) rotate(-1deg); }
        }
        
        .slide-up {
            animation: slideUp 0.8s ease-out;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 1.2s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .scale-in {
            animation: scaleIn 0.6s ease-out;
        }
        
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }
        
        .hero-pattern {
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(59, 130, 246, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(37, 99, 235, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(29, 78, 216, 0.2) 0%, transparent 50%);
        }
        
        .card-hover {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .card-hover:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #3b82f6, #2563eb, #1e40af);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.4);
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .feature-icon {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.4);
        }
        
        .mobile-menu {
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }
        
        .mobile-menu.active {
            transform: translateX(0);
        }
        
        .scroll-indicator {
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        /* High-contrast blue-white styles for Google Maps embed */
        .map-blue-overlay { position: relative; background: #ffffff; }
        .map-blue-overlay iframe { width: 100%; height: 420px; border: 0; }
        .map-blue-overlay::after {
            content: '';
            position: absolute;
            inset: 0;
            background: #0B66FF; /* solid blue overlay */
            opacity: 0.68; /* strong colorization for blue-white look */
            mix-blend-mode: color; /* force uniform blue hue without gradient */
            pointer-events: none;
        }
        /* Push map to blue-white palette and increase contrast */
        .map-blue-overlay iframe[src*="maps"] {
            filter: grayscale(100%) contrast(185%) brightness(112%) saturate(0);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <!-- Header Navigation -->
    <header class="fixed top-0 w-full glass-effect z-50 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center mr-3 floating-animation">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gradient">Toko Segar</h1>
                        <p class="text-sm text-gray-600">Cibinong</p>
                    </div>
                </div>
                
                <!-- Desktop Navigation -->
                <nav class="hidden lg:flex items-center space-x-8">
                    <a href="#home" class="text-gray-700 hover:text-blue-600 transition duration-300 font-medium">Beranda</a>
                    <a href="#features" class="text-gray-700 hover:text-blue-600 transition duration-300 font-medium">Fitur</a>
                    <a href="#location" class="text-gray-700 hover:text-blue-600 transition duration-300 font-medium">Lokasi</a>
                    <a href="#contact" class="text-gray-700 hover:text-blue-600 transition duration-300 font-medium">Kontak</a>
                </nav>
                
                <!-- CTA Buttons -->
                <div class="hidden lg:flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="btn-primary text-white px-6 py-3 rounded-full font-semibold shadow-lg">
                        Masuk Sistem
                    </a>
                    <button id="mobile-menu-toggle" class="lg:hidden text-gray-700 hover:text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="mobile-menu fixed top-0 right-0 w-80 h-full glass-effect z-40 lg:hidden">
        <div class="p-6">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-xl font-bold text-gray-900">Menu</h2>
                <button id="close-mobile-menu" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <nav class="space-y-4">
                <a href="#home" class="block text-gray-700 hover:text-blue-600 transition duration-300 font-medium py-2">Beranda</a>
                <a href="#features" class="block text-gray-700 hover:text-blue-600 transition duration-300 font-medium py-2">Fitur</a>
                <a href="#location" class="block text-gray-700 hover:text-blue-600 transition duration-300 font-medium py-2">Lokasi</a>
                <a href="#contact" class="block text-gray-700 hover:text-blue-600 transition duration-300 font-medium py-2">Kontak</a>
                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ route('login') }}" class="btn-primary text-white px-6 py-3 rounded-full font-semibold shadow-lg w-full text-center">
                        Masuk Sistem
                    </a>
                </div>
            </nav>
        </div>
    </div>

    <!-- Hero Section -->
    <section id="home" class="gradient-bg min-h-screen flex items-center relative overflow-hidden">
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pt-20">
            <div class="text-center slide-up">
                <div class="inline-flex items-center bg-white/20 backdrop-blur-sm rounded-full px-4 py-2 mb-6">
                    <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                    <span class="text-white font-medium">🛒 Toko Segar — Cibinong</span>
                </div>
                
                <h1 class="text-5xl lg:text-7xl font-bold text-white mb-6 leading-tight">
                    Toko <span class="text-blue-200">Segar</span>
                </h1>
                
                <p class="text-xl text-blue-100 mb-8 leading-relaxed max-w-3xl">
                    Toko Segar menghadirkan produk segar berkualitas untuk keluarga Anda di Cibinong. 
                    Belanja sayur, buah, daging, dan kebutuhan harian dengan mudah, cepat, dan harga bersahabat.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#location" class="btn-primary text-white px-8 py-4 rounded-full font-semibold shadow-xl text-center group">
                        <span class="flex items-center justify-center">
                            Lihat Lokasi
                            <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="absolute bottom-6 sm:bottom-8 left-1/2 transform -translate-x-1/2 scroll-indicator hidden sm:block z-0 pointer-events-none">
            <svg class="w-6 h-6 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-flex items-center bg-blue-100 text-blue-800 rounded-full px-4 py-2 mb-4">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Keunggulan Toko Segar
                </div>
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Mengapa Belanja di Toko Segar?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Toko Segar menghadirkan produk segar berkualitas, harga bersahabat, dan pelayanan ramah agar pengalaman belanja Anda nyaman dan memuaskan.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card bg-gray-50 rounded-2xl p-8 shadow-lg card-hover">
                    <div class="feature-icon w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Kasir Digital Canggih</h3>
                    <p class="text-gray-600 mb-4">Proses transaksi yang cepat dan akurat dengan interface modern, mendukung berbagai metode pembayaran dan cetak struk otomatis</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Multi-payment support</li>
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Auto receipt printing</li>
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Fast checkout</li>
                    </ul>
                </div>
                
                <!-- Feature 2 -->
                <div class="feature-card bg-gray-50 rounded-2xl p-8 shadow-lg card-hover">
                    <div class="feature-icon w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M9 21V9l3-2 3 2v12"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Manajemen Stok Pintar</h3>
                    <p class="text-gray-600 mb-4">Kelola inventori dengan mudah, pantau stok real-time, notifikasi stok menipis, dan laporan pergerakan barang</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Real-time tracking</li>
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Low stock alerts</li>
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Batch management</li>
                    </ul>
                </div>
                
                <!-- Feature 3 -->
                <div class="feature-card bg-gray-50 rounded-2xl p-8 shadow-lg card-hover">
                    <div class="feature-icon w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Laporan & Analisis</h3>
                    <p class="text-gray-600 mb-4">Dashboard analisis penjualan lengkap dengan grafik interaktif, laporan harian/bulanan, dan insight bisnis</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Interactive charts</li>
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Custom reports</li>
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Business insights</li>
                    </ul>
                </div>
                
                <!-- Feature 4 -->
                <div class="feature-card bg-gray-50 rounded-2xl p-8 shadow-lg card-hover">
                    <div class="feature-icon w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Manajemen Pengguna</h3>
                    <p class="text-gray-600 mb-4">Sistem role-based access control (RBAC) untuk mengatur hak akses karyawan dengan berbagai level permission</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Role-based access</li>
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Permission control</li>
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Audit logs</li>
                    </ul>
                </div>
                
                <!-- Feature 5 -->
                <div class="feature-card bg-gray-50 rounded-2xl p-8 shadow-lg card-hover">
                    <div class="feature-icon w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Keamanan Data</h3>
                    <p class="text-gray-600 mb-4">Enkripsi data dan backup otomatis untuk menjaga keamanan informasi bisnis dengan standar keamanan tinggi</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Data encryption</li>
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Auto backup</li>
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Secure access</li>
                    </ul>
                </div>
                
                <!-- Feature 6 -->
                <div class="feature-card bg-gray-50 rounded-2xl p-8 shadow-lg card-hover">
                    <div class="feature-icon w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Multi-Platform</h3>
                    <p class="text-gray-600 mb-4">Akses dari berbagai perangkat - desktop, tablet, atau smartphone dengan sinkronisasi data real-time</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Responsive design</li>
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Cross-platform</li>
                        <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Real-time sync</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Location Section -->
    <section id="location" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-flex items-center bg-blue-100 text-blue-800 rounded-full px-4 py-2 mb-4">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Kunjungi Kami
                </div>
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Lokasi Kantor</h2>
                <p class="text-xl text-gray-600">
                    Kunjungi kantor kami di Cibinong, Bogor untuk konsultasi dan demo langsung
                </p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="scale-in">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-8 shadow-xl">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Informasi Kontak</h3>
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Alamat</h4>
                                    <p class="text-gray-600">Jl. Raya Cibinong No. 123<br>Cibinong, Bogor 16911<br>Jawa Barat, Indonesia</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Jam Operasional</h4>
                                    <p class="text-gray-600">Senin - Jumat: 08:00 - 17:00</p>
                                    <p class="text-gray-600">Sabtu: 08:00 - 15:00</p>
                                    <p class="text-gray-600">Minggu: Tutup</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Kontak</h4>
                                    <p class="text-gray-600">+62 21 8765 4321</p>
                                    <p class="text-gray-600">info@posnabila.com</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="fade-in">
                    <div class="rounded-2xl overflow-hidden shadow-xl map-blue-overlay">
                        <iframe 
                            src="https://maps.google.com/maps?q=Cibinong,%20Bogor,%20Jawa%20Barat&z=14&output=embed" width="100%" height="420" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
