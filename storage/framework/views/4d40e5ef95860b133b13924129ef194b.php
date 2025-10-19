<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Login - POS Nabila</title>
    <meta name="description" content="Masuk ke sistem Point of Sale POS Nabila">
    <link href="<?php echo e(asset('css/app.css')); ?>" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%);
            min-height: 100vh;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%);
            position: relative;
            overflow-x: hidden; /* izinkan scroll vertikal */
            overflow-y: auto;   /* perbaiki masalah scroll */
        }
        
        .glass-effect {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
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
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        
        .hero-pattern {
            pointer-events: none; /* jangan blok interaksi/scroll */
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(59, 130, 246, 0.4) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(37, 99, 235, 0.4) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(29, 78, 216, 0.3) 0%, transparent 50%);
        }
        
        .input-field {
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
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
        
        .checkbox-custom {
            transition: all 0.3s ease;
        }
        
        .checkbox-custom:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
        
        .demo-card {
            transition: all 0.3s ease;
        }
        
        .demo-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .logo-animation {
            animation: logoFloat 3s ease-in-out infinite;
        }
        
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #60a5fa, #3b82f6, #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
    </style>
</head>
<body class="font-sans antialiased gradient-bg">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden hero-pattern">
        <div class="absolute top-10 left-10 w-32 h-32 bg-white opacity-10 rounded-full floating-animation"></div>
        <div class="absolute top-32 right-20 w-24 h-24 bg-blue-300 opacity-20 rounded-lg floating-animation" style="animation-delay: -2s;"></div>
        <div class="absolute bottom-20 left-1/4 w-20 h-20 bg-white opacity-15 rounded-full floating-animation" style="animation-delay: -4s;"></div>
        <div class="absolute bottom-32 right-1/3 w-36 h-36 bg-blue-200 opacity-10 rounded-lg floating-animation" style="animation-delay: -1s;"></div>
        <div class="absolute top-1/2 left-1/3 w-16 h-16 bg-blue-300 opacity-15 rounded-full floating-animation" style="animation-delay: -3s;"></div>
    </div>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative">
        <div class="max-w-md w-full space-y-8 scale-in">
            <!-- Logo and Brand -->
            <div class="text-center">
                <div class="mx-auto w-20 h-20 bg-white rounded-full shadow-xl flex items-center justify-center mb-6 logo-animation">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-white mb-3">
                    POS <span class="text-blue-200">Nabila</span>
                </h1>
                <p class="text-blue-100 text-lg">
                    Selamat datang kembali!
                </p>
                <p class="text-blue-200 text-sm mt-2">
                    Masuk ke sistem Point of Sale modern
                </p>
            </div>
            
            <!-- Login Form Card -->
            <div class="glass-effect rounded-3xl shadow-2xl p-8 fade-in">
                <?php if($errors->any()): ?>
                    <div class="rounded-xl bg-red-50 border border-red-200 p-4 mb-6 scale-in">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm text-red-700">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <p><?php echo e($error); ?></p>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form class="space-y-6" method="POST" action="<?php echo e(route('login')); ?>">
                    <?php echo csrf_field(); ?>
                    
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-3">
                            Alamat Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                   class="input-field block w-full pl-12 pr-4 py-4 border border-gray-200 rounded-2xl leading-5 bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="Masukkan email Anda" value="<?php echo e(old('email')); ?>">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-3">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input id="password" name="password" type="password" autocomplete="current-password" required 
                                   class="input-field block w-full pl-12 pr-12 py-4 border border-gray-200 rounded-2xl leading-5 bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="Masukkan kata sandi Anda">
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                <svg id="eyeIcon" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 9.879a4 4 0 115.656 0H9a4 4 0 00-5.656 0l4-4z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" 
                                   class="checkbox-custom h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                   <?php echo e(old('remember') ? 'checked' : ''); ?>>
                            <label for="remember" class="ml-2 block text-sm text-gray-700">
                                Ingat saya
                            </label>
                        </div>

                        <?php if(Route::has('password.request')): ?>
                            <div class="text-sm">
                                <a href="<?php echo e(route('password.request')); ?>" class="font-medium text-blue-600 hover:text-blue-500 transition duration-300 ease-in-out">
                                    Lupa kata sandi?
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sign In Button -->
                    <div>
                        <button type="submit" 
                                class="btn-primary group relative w-full flex justify-center py-4 px-6 border border-transparent text-base font-semibold rounded-2xl text-white transform transition duration-300 ease-in-out hover:scale-105 shadow-xl">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-6 w-6 text-blue-200 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                            </span>
                            <span class="shimmer">Masuk ke Sistem</span>
                        </button>
                    </div>
                </form>

                <!-- Demo Accounts Info -->
                <div class="mt-8 p-6 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-2xl demo-card">
                    <h3 class="text-sm font-bold text-blue-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Akun Demo
                    </h3>
                    <div class="text-sm text-blue-700 space-y-3">
                        <div class="bg-white p-3 rounded-xl border border-blue-200">
                            <div class="flex items-center mb-2">
                                <span class="text-2xl mr-2">👑</span>
                                <div>
                                    <p class="font-bold text-blue-800">Administrator</p>
                                    <p class="text-xs text-gray-500">Akses penuh sistem</p>
                                </div>
                            </div>
                            <div class="text-xs text-gray-600">
                                <p><strong>Email:</strong> admin@example.com</p>
                                <p><strong>Password:</strong> password</p>
                            </div>
                        </div>
                        
                        <div class="bg-white p-3 rounded-xl border border-blue-200">
                            <div class="flex items-center mb-2">
                                <span class="text-2xl mr-2">👨‍💼</span>
                                <div>
                                    <p class="font-bold text-blue-800">Manajer</p>
                                    <p class="text-xs text-gray-500">Kelola produk & laporan</p>
                                </div>
                            </div>
                            <div class="text-xs text-gray-600">
                                <p><strong>Email:</strong> manager@example.com</p>
                                <p><strong>Password:</strong> password</p>
                            </div>
                        </div>
                        
                        <div class="bg-white p-3 rounded-xl border border-blue-200">
                            <div class="flex items-center mb-2">
                                <span class="text-2xl mr-2">🛒️</span>
                                <div>
                                    <p class="font-bold text-blue-800">Kasir</p>
                                    <p class="text-xs text-gray-500">Operasional POS</p>
                                </div>
                            </div>
                            <div class="text-xs text-gray-600">
                                <p><strong>Email:</strong> kasir@example.com</p>
                                <p><strong>Password:</strong> password</p>
                            </div>
                        </div>
                        
                        <p class="text-blue-600 mt-4 text-center font-medium">
                            Pilih akun mana untuk menjelajahi berbagai tingk akses!
                        </p>
                    </div>
                </div>

                <!-- Back to Home -->
                <div class="text-center mt-6">
                    <a href="<?php echo e(route('welcome')); ?>" class="text-blue-200 hover:text-white text-sm transition duration-300 ease-in-out flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-10M7 15h3a1 1 0 001-1h3a1 1 0 001-1h3"></path>
                        </svg>
                        Kembali ke Halaman Utama
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="absolute bottom-0 left-0 right-0 text-center p-6">
        <p class="text-blue-100 text-sm">
            © <?php echo e(date('Y')); ?> POS Nabila. Semua hak cipta dilindungi.
        </p>
    </div>

    <!-- Scripts -->
    <script>
        // Password toggle functionality
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Change eye icon
            if (type === 'text') {
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c2.769 0 5.202-1.105 6.875-2.875L15.125 15.875A10.05 10.05 0 0112 5c0-2.769-1.105-5.202-2.875-6.875L12 2.875A10.05 10.05 0 005.125 5.125L8.875 2.875A10.05 10.05 0 0012 5c0 2.769 1.105 5.202 2.875 6.875z"></path>';
            } else {
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 9.879a4 4 0 115.656 0H9a4 4 0 00-5.656 0l4-4z"></path>';
            }
        });
        
        // Form validation enhancement
        const form = document.querySelector('form');
        const submitBtn = form.querySelector('button[type="submit"]');
        
        form.addEventListener('submit', function(e) {
            // Basic client-side validation
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                // Show a subtle validation message
                if (!email) {
                    document.getElementById('email').classList.add('border-red-500');
                }
                if (!password) {
                    document.getElementById('password').classList.add('border-red-500');
                }
            }
        });
        
        // Remove validation styles on input
        document.getElementById('email').addEventListener('input', function() {
            this.classList.remove('border-red-500');
        });
        
        document.getElementById('password').addEventListener('input', function() {
            this.classList.remove('border-red-500');
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
    </script>
</body>
</html>
<?php /**PATH C:\laragon\www\laravel_livewire_RBAC_boilerplate\resources\views/auth/login.blade.php ENDPATH**/ ?>