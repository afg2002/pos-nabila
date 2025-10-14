<nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-blue-600">
                        <i class="fas fa-pills mr-2"></i>Apotek
                    </a>
                </div>

                <!-- Main Navigation -->
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('dashboard') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Dashboard
                    </a>

                    <!-- POS -->
                    @can('pos.access')
                    <a href="{{ route('pos.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('pos*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium">
                        <i class="fas fa-cash-register mr-2"></i>
                        POS Kasir
                    </a>
                    @endcan

                    <!-- Agenda Management (Enhanced) -->
                    @can('agenda.view')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('agenda-management*') || request()->routeIs('cashflow-agenda*') || request()->routeIs('incoming-goods-agenda*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Agenda
                            <svg class="ml-1 h-5 w-5" x-show="!open" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            <svg class="ml-1 h-5 w-5" x-show="open" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute z-10 mt-1 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                            <div class="py-1">
                                <a href="{{ route('incoming-goods-agenda.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-calendar-check mr-2"></i>
                                    Agenda Management
                                </a>
                                @can('cashflow_agenda.view')
                                <a href="{{ route('cashflow-agenda.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-money-bill-wave mr-2"></i>
                                    Cashflow Agenda
                                </a>
                                @endcan
                                @can('incoming_goods_agenda.view')
                                <a href="{{ route('incoming-goods-agenda.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-box mr-2"></i>
                                    Barang Datang
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endcan

                    <!-- Inventory -->
                    @can('inventory.view')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('inventory*') || request()->routeIs('products*') || request()->routeIs('product-units*') || request()->routeIs('warehouses*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium">
                            <i class="fas fa-warehouse mr-2"></i>
                            Inventory
                            <svg class="ml-1 h-5 w-5" x-show="!open" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            <svg class="ml-1 h-5 w-5" x-show="open" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute z-10 mt-1 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                            <div class="py-1">
                                <a href="{{ route('inventory.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-boxes mr-2"></i>
                                    Stok Barang
                                </a>
                                @can('products.view')
                                <a href="{{ route('products.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-pills mr-2"></i>
                                    Produk
                                </a>
                                @endcan
                                @can('product_units.view')
                                <a href="{{ route('product-units.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-weight mr-2"></i>
                                    Satuan
                                </a>
                                @endcan
                                @can('warehouses.view')
                                <a href="{{ route('warehouses.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-warehouse mr-2"></i>
                                    Gudang
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endcan

                    <!-- Financial -->
                    @canany(['capital_tracking.view', 'cash_ledger.view', 'financial.view'])
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('capital-tracking*') || request()->routeIs('cash-ledger*') || request()->routeIs('financial*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium">
                            <i class="fas fa-chart-line mr-2"></i>
                            Financial
                            <svg class="ml-1 h-5 w-5" x-show="!open" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            <svg class="ml-1 h-5 w-5" x-show="open" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute z-10 mt-1 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                            <div class="py-1">
                                @can('capital_tracking.view')
                                <a href="{{ route('capital-tracking.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-coins mr-2"></i>
                                    Modal
                                </a>
                                @endcan
                                @can('cash_ledger.view')
                                <a href="{{ route('cash-ledger.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-money-check-alt mr-2"></i>
                                    Buku Kas
                                </a>
                                @endcan
                                @can('financial.view')
                                <a href="{{ route('financial.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-chart-pie mr-2"></i>
                                    Laporan Keuangan
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endcan

                    <!-- Suppliers -->
                    @can('suppliers.view')
                    <a href="{{ route('suppliers.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('suppliers*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium">
                        <i class="fas fa-truck mr-2"></i>
                        Suppliers
                    </a>
                    @endcan

                    <!-- Reports -->
                    @can('reports.view')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('reports*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium">
                            <i class="fas fa-file-alt mr-2"></i>
                            Reports
                            <svg class="ml-1 h-5 w-5" x-show="!open" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            <svg class="ml-1 h-5 w-5" x-show="open" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute z-10 mt-1 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                            <div class="py-1">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-chart-bar mr-2"></i>
                                    Laporan Penjualan
                                </a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-box-open mr-2"></i>
                                    Laporan Stok
                                </a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-dollar-sign mr-2"></i>
                                    Laporan Keuangan
                                </a>
                            </div>
                        </div>
                    </div>
                    @endcan
                </div>
            </div>

            <!-- Right side buttons -->
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span class="sr-only">View notifications</span>
                        <i class="fas fa-bell h-6 w-6"></i>
                        <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400 ring-2 ring-white"></span>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-2">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <h3 class="text-sm font-medium text-gray-900">Notifications</h3>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <!-- Sample notifications -->
                                <div class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-triangle text-yellow-400 mt-0.5"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-gray-900">3 produk akan kadaluarsa dalam 7 hari</p>
                                            <p class="text-xs text-gray-500 mt-1">2 jam yang lalu</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-money-bill-wave text-green-400 mt-0.5"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-gray-900">Pembayaran PO #PO-202410-0001 jatuh tempo</p>
                                            <p class="text-xs text-gray-500 mt-1">5 jam yang lalu</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="px-4 py-2 border-t border-gray-100">
                                <a href="#" class="text-sm text-blue-600 hover:text-blue-800">View all notifications</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span class="sr-only">Open user menu</span>
                        <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                            <span class="text-white font-medium">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        </div>
                    </button>

                    <!-- Profile dropdown -->
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>
                                Profile
                            </a>
                            <a href="{{ route('profile.index') }}#settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i>
                                Settings
                            </a>
                            @can('users.view')
                            <a href="{{ route('users.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-users mr-2"></i>
                                Users
                            </a>
                            @endcan
                            @can('roles.view')
                            <a href="{{ route('roles.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user-shield mr-2"></i>
                                Roles
                            </a>
                            @endcan
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>