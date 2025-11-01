<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'Toko Segar') }}</title>
    <meta name="description" content="Masuk ke sistem Point of Sale {{ config('app.name', 'Toko Segar') }}">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
        }
        
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading .loading-spinner {
            display: inline-block;
        }
        
        .loading .btn-text {
            display: none;
        }
        
        /* Accessibility */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        
        /* Focus visible for keyboard navigation */
        *:focus-visible {
            outline: 2px solid rgba(59, 130, 246, 0.8);
            outline-offset: 2px;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <main class="min-h-screen flex" role="main">
        <!-- Left Side - Brand Section -->
        <div class="hidden lg:flex lg:w-1/2 hero-gradient items-center justify-center">
            <div class="text-center text-white max-w-md px-8">
                <div class="mb-8">
                    <div class="w-20 h-20 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold mb-4">
                        {{ config('app.name', 'Toko Segar') }}
                    </h1>
                    <p class="text-xl text-blue-100 mb-6">
                        Selamat datang kembali!
                    </p>
                    <p class="text-blue-200 leading-relaxed">
                        Sistem Point of Sale modern untuk mengelola bisnis Anda dengan mudah dan efisien. Nikmati pengalaman berbelanja yang lebih baik dengan teknologi terkini.
                    </p>
                </div>
                
                <div class="grid grid-cols-3 gap-6 mt-12">
                    <div class="text-center">
                        <div class="text-3xl font-bold mb-2">500+</div>
                        <div class="text-blue-200 text-sm">Produk</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold mb-2">1000+</div>
                        <div class="text-blue-200 text-sm">Transaksi</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold mb-2">50+</div>
                        <div class="text-blue-200 text-sm">Pelanggan</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 bg-gray-50 flex items-center justify-center p-4 sm:p-6 lg:p-12">
            <div class="w-full max-w-md">
                <!-- Mobile & Tablet Brand Header -->
                <div class="lg:hidden text-center mb-6 sm:mb-8">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-3 sm:mb-4">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">
                        {{ config('app.name', 'Toko Segar') }}
                    </h1>
                    <p class="text-sm sm:text-base text-gray-600">
                        Masuk ke sistem Point of Sale
                    </p>
                </div>
                
                <!-- Login Form -->
                <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="rounded-lg bg-red-50 border border-red-200 p-3 sm:p-4 mb-4 sm:mb-6" role="alert">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
 
                <form class="space-y-6" method="POST" action="{{ route('login') }}" id="loginForm" aria-label="Form Login">
                    @csrf
                    
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2 sm:mb-3">
                            Alamat Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                            <input 
                                id="email" 
                                name="email" 
                                type="email" 
                                autocomplete="email" 
                                required 
                                aria-required="true"
                                aria-describedby="email-error"
                                class="block w-full pl-12 pr-4 py-3 sm:py-4 border border-gray-300 rounded-lg leading-5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                                placeholder="Masukkan email Anda"
                                value="{{ old('email') }}"
                                tabindex="1">
                        </div>
                        @if ($errors->has('email'))
                            <p id="email-error" class="mt-2 text-sm text-red-600">{{ $errors->first('email') }}</p>
                        @endif
                    </div>
 
                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2 sm:mb-3">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input 
                                id="password" 
                                name="password" 
                                type="password" 
                                autocomplete="current-password" 
                                required 
                                aria-required="true"
                                aria-describedby="password-error"
                                class="block w-full pl-12 pr-12 py-3 sm:py-4 border border-gray-300 rounded-lg leading-5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                                placeholder="Masukkan kata sandi Anda"
                                tabindex="2">
                            <button 
                                type="button" 
                                id="togglePassword" 
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                                aria-label="Toggle password visibility"
                                tabindex="3">
                                <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 9.879a4 4 0 115.656 0H9a4 4 0 00-5.656 0l4-4z"></path>
                                </svg>
                            </button>
                        </div>
                        @if ($errors->has('password'))
                            <p id="password-error" class="mt-2 text-sm text-red-600">{{ $errors->first('password') }}</p>
                        @endif
                    </div>
 
                    <!-- Remember -->
                    <div class="flex items-center">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            {{ old('remember') ? 'checked' : '' }}
                            tabindex="4">
                        <label for="remember" class="ml-2 block text-sm text-gray-700 cursor-pointer">
                            Ingat saya
                        </label>
                    </div>
 
                    <!-- Sign In Button -->
                    <div>
                        <button
                            type="submit"
                            class="w-full flex justify-center py-3 sm:py-3 px-4 bg-blue-600 hover:bg-blue-700 border border-transparent text-sm sm:text-base font-semibold rounded-lg text-white transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            tabindex="5"
                            id="submitBtn">
                            <span class="loading-spinner" aria-hidden="true"></span>
                            <span class="btn-text flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5-4v14"></path>
                                </svg>
                                Masuk ke Sistem
                            </span>
                        </button>
                    </div>
                </form>
 
                </form>
                
                <!-- Demo Accounts Info -->
                <div class="mt-6 sm:mt-8 p-4 sm:p-6 bg-white border border-gray-200 rounded-lg">
                    <h3 class="text-sm font-bold text-gray-800 mb-3 sm:mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Akun Demo
                    </h3>
                    <div class="text-sm text-gray-700 space-y-3">
                        <div class="bg-gray-50 p-3 sm:p-3 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-2">
                                <span class="text-2xl mr-2" role="img" aria-label="Administrator">👑</span>
                                <div>
                                    <p class="font-bold text-gray-900">Administrator</p>
                                    <p class="text-xs text-gray-600">Akses penuh sistem</p>
                                </div>
                            </div>
                            <div class="text-xs text-gray-600">
                                <p><strong>Email:</strong> admin@example.com</p>
                                <p><strong>Password:</strong> password</p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 p-3 sm:p-3 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-2">
                                <span class="text-2xl mr-2" role="img" aria-label="Manager">👨‍💼</span>
                                <div>
                                    <p class="font-bold text-gray-900">Manajer</p>
                                    <p class="text-xs text-gray-600">Kelola produk & laporan</p>
                                </div>
                            </div>
                            <div class="text-xs text-gray-600">
                                <p><strong>Email:</strong> manager@example.com</p>
                                <p><strong>Password:</strong> password</p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 p-3 sm:p-3 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-2">
                                <span class="text-2xl mr-2" role="img" aria-label="Cashier">🛒️</span>
                                <div>
                                    <p class="font-bold text-gray-900">Kasir</p>
                                    <p class="text-xs text-gray-600">Operasional POS</p>
                                </div>
                            </div>
                            <div class="text-xs text-gray-600">
                                <p><strong>Email:</strong> kasir@example.com</p>
                                <p><strong>Password:</strong> password</p>
                            </div>
                        </div>
                        
                        <p class="text-gray-700 mt-3 sm:mt-4 text-center font-medium text-sm sm:text-base">
                            Pilih akun mana untuk menjelajahi berbagai tingkat akses!
                        </p>
                    </div>
                </div>
                
                <!-- Back to Home -->
                <div class="text-center mt-4 sm:mt-6">
                    <a
                        href="{{ route('welcome') }}"
                        class="text-gray-500 hover:text-blue-600 text-sm transition-colors duration-300 flex items-center justify-center"
                        tabindex="6">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-10M7 15h3a1 1 0 001-1h3a1 1 0 001-1h3"></path>
                        </svg>
                        Kembali ke Halaman Utama
                    </a>
                </div>
            </div>
        </div>
    </main>
 
    <!-- Scripts -->
    <script>
        // Password toggle functionality
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Update ARIA label
            togglePassword.setAttribute('aria-label', type === 'text' ? 'Hide password' : 'Show password');
            
            // Change eye icon
            if (type === 'text') {
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c2.769 0 5.202-1.105 6.875-2.875L15.125 15.875A10.05 10.05 0 0112 5c0-2.769-1.105-5.202-2.875-6.875L12 2.875A10.05 10.05 0 005.125 5.125L8.875 2.875A10.05 10.05 0 0012 5c0 2.769 1.105 5.202 2.875 6.875z"></path>';
            } else {
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 9.879a4 4 0 115.656 0H9a4 4 0 00-5.656 0l4-4z"></path>';
            }
        });
        
        // Form validation and submission
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        
        form.addEventListener('submit', function(e) {
            // Basic client-side validation
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                
                // Show validation feedback
                if (!email) {
                    document.getElementById('email').classList.add('border-red-500');
                    document.getElementById('email').setAttribute('aria-invalid', 'true');
                }
                if (!password) {
                    document.getElementById('password').classList.add('border-red-500');
                    document.getElementById('password').setAttribute('aria-invalid', 'true');
                }
                
                // Announce error to screen readers
                const announcement = document.createElement('div');
                announcement.setAttribute('role', 'alert');
                announcement.setAttribute('aria-live', 'polite');
                announcement.className = 'sr-only';
                announcement.textContent = 'Mohon lengkapi semua field yang diperlukan';
                document.body.appendChild(announcement);
                
                setTimeout(() => {
                    document.body.removeChild(announcement);
                }, 1000);
            } else {
                // Show loading state
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }
        });
        
        // Remove validation styles on input
        document.getElementById('email').addEventListener('input', function() {
            this.classList.remove('border-red-500');
            this.setAttribute('aria-invalid', 'false');
        });
        
        document.getElementById('password').addEventListener('input', function() {
            this.classList.remove('border-red-500');
            this.setAttribute('aria-invalid', 'false');
        });
        
        // Add focus animations
        const inputs = document.querySelectorAll('input[type="email"], input[type="password"]');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('scale-105');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('scale-105');
            });
        });
        
        // Keyboard navigation enhancement
        document.addEventListener('keydown', function(e) {
            // Escape key to close any modals or reset focus
            if (e.key === 'Escape') {
                document.activeElement.blur();
            }
            
            // Enter key on form submission
            if (e.key === 'Enter' && e.target.tagName !== 'BUTTON' && e.target.tagName !== 'TEXTAREA') {
                const form = e.target.closest('form');
                if (form) {
                    form.requestSubmit();
                }
            }
        });
        
        // Auto-focus on email field for better UX
        window.addEventListener('load', function() {
            document.getElementById('email').focus();
        });
        
        // Add smooth scroll behavior for better accessibility
        document.documentElement.style.scrollBehavior = 'smooth';
        
        // Performance optimization: Debounce resize events
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                // Handle responsive adjustments if needed
            }, 250);
        });
    </script>
</body>
</html>
