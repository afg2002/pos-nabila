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

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl shadow-xl border-l-4 border-red-400 flex items-center backdrop-blur-sm" 
             x-data="{ show: true }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-500" 
             x-transition:enter-start="opacity-0 transform translate-y-4 scale-95" 
             x-transition:enter-end="opacity-100 transform translate-y-0 scale-100" 
             x-transition:leave="transition ease-in duration-300" 
             x-transition:leave-start="opacity-100 transform translate-y-0 scale-100" 
             x-transition:leave-end="opacity-0 transform translate-y-4 scale-95" 
             x-init="setTimeout(() => show = false, 5000)">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <span class="flex-1 font-medium">{{ session('error') }}</span>
            <button @click="show = false" class="ml-3 text-white hover:text-red-200 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endif

    <!-- Header with White Background -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-black">Manajemen Supplier</h1>
                <p class="text-gray-600 mt-1">Kelola data supplier/PT dengan mudah dan efisien</p>
            </div>
            @permission('suppliers.create')
                <button wire:click="openModal()" 
                        class="mt-4 sm:mt-0 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transform hover:scale-105 transition-all duration-200 shadow-md">
                    <i class="fas fa-plus mr-2"></i>Tambah Supplier
                </button>
            @endpermission
        </div>
    </div>

    <!-- Enhanced Search and Filter Controls -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-users mr-3 text-blue-600"></i>
                    Data Supplier
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Cari dan filter supplier sesuai kebutuhan</p>
            </div>
            <div class="mt-3 sm:mt-0">
                <span class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                    <i class="fas fa-database mr-2"></i>
                    {{ $suppliers->count() }} Supplier
                </span>
            </div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="relative">
                <label for="searchSupplier" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-search mr-1 text-gray-400"></i>Cari Supplier
                </label>
                <input type="text" id="searchSupplier"
                       class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200" 
                       placeholder="Nama, email, kontak..." 
                       wire:model.live.debounce.300ms="search">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none mt-8">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
            
            <div class="relative">
                <label for="statusFilter" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-filter mr-1 text-gray-400"></i>Status
                </label>
                <select id="statusFilter"
                        class="w-full px-4 py-3 pr-10 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200 appearance-none" 
                        wire:model.live="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak Aktif</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none mt-8">
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-700/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Active</p>
                        <p class="text-xl font-bold text-blue-900 dark:text-blue-100">{{ $suppliers->where('status', 'active')->count() }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-500 bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Table with Mobile Responsiveness -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">
        <!-- Table Header -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-table mr-2 text-gray-400"></i>
                    Daftar Supplier
                </h3>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $suppliers->count() }} hasil ditemukan
                </div>
            </div>
        </div>
        
        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Informasi Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Kontak Person</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($suppliers as $supplier)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-md">
                                        <i class="fas fa-building text-white text-lg"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $supplier->name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $supplier->address ?: 'Tidak ada alamat' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white font-medium">{{ $supplier->contact_person ?: '-' }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $supplier->phone ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white truncate max-w-xs">{{ $supplier->email ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold transition-all duration-200 transform hover:scale-105 {{ $supplier->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300' }}">
                                    <i class="fas {{ $supplier->status === 'active' ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                    {{ $supplier->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    @permission('suppliers.edit')
                                        <button class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transform hover:scale-105 transition-all duration-200 shadow-sm" 
                                                wire:click="openModal({{ $supplier->id }})" 
                                                title="Edit Supplier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endpermission
                                    @permission('suppliers.delete')
                                        <button class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transform hover:scale-105 transition-all duration-200 shadow-sm" 
                                                wire:click="delete({{ $supplier->id }})" 
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus supplier ini?')" 
                                                title="Hapus Supplier">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
                                    <p class="text-gray-500 text-lg font-medium">Belum ada data supplier</p>
                                    <p class="text-gray-400 text-sm mt-1">Tambahkan supplier baru untuk memulai</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Mobile Card View -->
        <div class="lg:hidden">
            @forelse ($suppliers as $supplier)
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center">
                            <div class="h-10 w-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-md">
                                <i class="fas fa-building text-white"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $supplier->name }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $supplier->address ?: 'Tidak ada alamat' }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $supplier->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            <i class="fas {{ $supplier->status === 'active' ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                            {{ $supplier->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        @if($supplier->contact_person)
                            <div class="flex items-center text-gray-700 dark:text-gray-300">
                                <i class="fas fa-user w-4 text-gray-400 mr-2"></i>
                                <span class="font-medium">{{ $supplier->contact_person }}</span>
                            </div>
                        @endif
                        @if($supplier->phone)
                            <div class="flex items-center text-gray-700 dark:text-gray-300">
                                <i class="fas fa-phone w-4 text-gray-400 mr-2"></i>
                                <span>{{ $supplier->phone }}</span>
                            </div>
                        @endif
                        @if($supplier->email)
                            <div class="flex items-center text-gray-700 dark:text-gray-300">
                                <i class="fas fa-envelope w-4 text-gray-400 mr-2"></i>
                                <span class="text-xs truncate">{{ $supplier->email }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex justify-end space-x-2 mt-4">
                        @permission('suppliers.edit')
                            <button class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs transform hover:scale-105 transition-all duration-200" 
                                    wire:click="openModal({{ $supplier->id }})">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                        @endpermission
                        @permission('suppliers.delete')
                            <button class="px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 text-xs transform hover:scale-105 transition-all duration-200" 
                                    wire:click="delete({{ $supplier->id }})" 
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus supplier ini?')">
                                <i class="fas fa-trash mr-1"></i>Hapus
                            </button>
                        @endpermission
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg font-medium">Belum ada data supplier</p>
                    <p class="text-gray-400 text-sm mt-1">Tambahkan supplier baru untuk memulai</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($suppliers->hasPages())
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>

    <!-- Enhanced Modal -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:click="closeModal">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full" wire:click.stop>
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r {{ $editingId ? 'from-blue-600 to-blue-700' : 'from-green-600 to-green-700' }} px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white flex items-center">
                                <i class="fas {{ $editingId ? 'fa-edit' : 'fa-plus-circle' }} mr-3"></i>
                                {{ $editingId ? 'Edit Supplier' : 'Tambah Supplier' }}
                            </h3>
                            <button @click="$wire.set('showModal', false)" 
                                    class="text-white hover:text-gray-200 transition-colors duration-200">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="bg-white dark:bg-gray-800 px-6 py-6">
                        <form wire:submit.prevent="save">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Left Column -->
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-building mr-1 text-gray-400"></i>
                                            Nama Supplier <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200" 
                                               wire:model="name" 
                                               placeholder="Masukkan nama supplier/PT"
                                               required>
                                        @error('name') 
                                            <div class="mt-2 flex items-center text-red-600 text-sm">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-envelope mr-1 text-gray-400"></i>
                                            Email
                                        </label>
                                        <input type="email" 
                                               class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200" 
                                               wire:model="email" 
                                               placeholder="email@supplier.com">
                                        @error('email') 
                                            <div class="mt-2 flex items-center text-red-600 text-sm">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-user mr-1 text-gray-400"></i>
                                            Kontak Person
                                        </label>
                                        <input type="text" 
                                               class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200" 
                                               wire:model="contact_person" 
                                               placeholder="Nama kontak person">
                                        @error('contact_person') 
                                            <div class="mt-2 flex items-center text-red-600 text-sm">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-phone mr-1 text-gray-400"></i>
                                            Telepon
                                        </label>
                                        <input type="text" 
                                               class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200" 
                                               wire:model="phone" 
                                               placeholder="Nomor telepon">
                                        @error('phone') 
                                            <div class="mt-2 flex items-center text-red-600 text-sm">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-toggle-on mr-1 text-gray-400"></i>
                                            Status
                                        </label>
                                        <select class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200" 
                                                wire:model="status">
                                            <option value="active">Aktif</option>
                                            <option value="inactive">Tidak Aktif</option>
                                        </select>
                                        @error('status') 
                                            <div class="mt-2 flex items-center text-red-600 text-sm">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>
                                            Alamat
                                        </label>
                                        <textarea class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200" 
                                                  rows="4" 
                                                  wire:model="address" 
                                                  placeholder="Alamat lengkap supplier"></textarea>
                                        @error('address') 
                                            <div class="mt-2 flex items-center text-red-600 text-sm">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 dark:border-gray-600 pt-6">
                                <button type="button" 
                                        @click="$wire.set('showModal', false)"
                                        class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transform hover:scale-105 transition-all duration-200 font-medium">
                                    <i class="fas fa-times mr-2"></i>
                                    Batal
                                </button>
                                <button type="submit" 
                                        class="px-6 py-3 bg-gradient-to-r {{ $editingId ? 'from-blue-600 to-blue-700' : 'from-green-600 to-green-700' }} text-white rounded-lg hover:{{ $editingId ? 'from-blue-700 to-blue-800' : 'from-green-700 to-green-800' }} focus:outline-none focus:ring-2 focus:ring-{{ $editingId ? 'blue' : 'green' }}-500 transform hover:scale-105 transition-all duration-200 font-medium shadow-lg">
                                    <i class="fas fa-save mr-2"></i>
                                    {{ $editingId ? 'Update' : 'Simpan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
