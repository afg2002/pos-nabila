<div class="min-h-screen bg-gray-50">
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex h-screen">
        <!-- Left Panel - Product Selection -->
        <div class="w-1/2 bg-white border-r border-gray-200 flex flex-col">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Pilih Produk</h2>
                
                <!-- Barcode Scanner -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-barcode mr-2"></i>Scan Barcode
                        <span class="text-xs text-gray-500 ml-2">(F3 untuk focus)</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="barcode-input"
                               wire:model.live="barcode" 
                               placeholder="Scan atau ketik barcode..."
                               class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               autofocus
                               autocomplete="off"
                               maxlength="50">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-qrcode text-gray-400"></i>
                        </div>
                    </div>
                    <div class="mt-1 text-xs text-gray-500">
                        Mendukung: EAN-13, UPC-A, Code 128, QR Code
                    </div>
                </div>
                
                <!-- Product Search -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Produk</label>
                    <input type="text" 
                           wire:model.live="productSearch" 
                           placeholder="Cari nama produk atau SKU..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <!-- Product Grid -->
            <div class="flex-1 overflow-y-auto p-6">
                <div class="grid grid-cols-2 gap-4">
                    @foreach($products as $product)
                        <div class="product-card relative bg-gray-50 rounded-lg p-4 hover:bg-gray-100 cursor-pointer transition-colors"
                             wire:click="addToCart({{ $product->id }})">
                            <div class="absolute top-2 right-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                {{ $product->barcode }}
                            </div>
                            <div class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $product->sku }}</div>
                            <div class="text-sm font-semibold text-blue-600 mt-2">
                                Rp {{ number_format($product->price_retail, 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Stok: {{ $product->current_stock }} {{ $product->unit }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Panel - Cart & Checkout -->
        <div class="w-1/2 bg-white flex flex-col">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Keranjang Belanja</h2>
                    @if(!empty($cart))
                        <button wire:click="clearCart" 
                                class="text-red-600 hover:text-red-800 text-sm font-medium"
                                title="Shortcut: F2">
                            Kosongkan
                        </button>
                    @endif
                </div>
                
                <!-- Keyboard Shortcuts Info -->
                <div class="mt-2 text-xs text-gray-500">
                    <span class="mr-3">F1: Checkout</span>
                    <span class="mr-3">F2: Kosongkan</span>
                    <span class="mr-3">F3: Focus Barcode</span>
                    <span class="mr-3">F4: Cari Produk</span>
                    <span>ESC: Batal/Tutup</span>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto">
                @if(empty($cart))
                    <div class="flex items-center justify-center h-full text-gray-500">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5M7 13l-1.1 5m0 0h9.1M6 18a2 2 0 100 4 2 2 0 000-4zm12 0a2 2 0 100 4 2 2 0 000-4z"></path>
                            </svg>
                            <p class="mt-2">Keranjang masih kosong</p>
                            <p class="text-sm">Scan barcode atau pilih produk</p>
                        </div>
                    </div>
                @else
                    <div class="p-6 space-y-4">
                        @foreach($cart as $key => $item)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">{{ $item['name'] }}</h4>
                                        <p class="text-sm text-gray-500">{{ $item['sku'] }}</p>
                                    </div>
                                    <button wire:click="removeFromCart('{{ $key }}')"
                                            class="text-red-500 hover:text-red-700">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="mt-3 grid grid-cols-2 gap-3">
                                    <!-- Quantity -->
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Jumlah</label>
                                        <input type="number" 
                                               wire:change="updateQuantity('{{ $key }}', $event.target.value)"
                                               value="{{ $item['quantity'] }}"
                                               min="1" 
                                               max="{{ $item['available_stock'] }}"
                                               class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    </div>
                                    
                                    <!-- Price -->
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Harga</label>
                                        <input type="number" 
                                               wire:change="updatePrice('{{ $key }}', $event.target.value)"
                                               value="{{ $item['price'] }}"
                                               min="{{ $item['base_cost'] * 1.1 }}"
                                               class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    </div>
                                </div>
                                
                                <div class="mt-2 flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Stok: {{ $item['available_stock'] }}</span>
                                    <span class="font-semibold text-blue-600">
                                        Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Cart Summary & Checkout -->
            @if(!empty($cart))
                <div class="border-t border-gray-200 p-6">
                    <!-- Discount -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Diskon</label>
                        <div class="flex space-x-2">
                            <select wire:model.live="discountType" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="amount">Rp</option>
                                <option value="percentage">%</option>
                            </select>
                            <input type="number" 
                                   wire:model.live="discount" 
                                   placeholder="0"
                                   min="0"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span>Subtotal:</span>
                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($discountAmount > 0)
                            <div class="flex justify-between text-sm text-red-600">
                                <span>Diskon:</span>
                                <span>-Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-lg font-semibold border-t pt-2">
                            <span>Total:</span>
                            <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <button wire:click="openCheckout" 
                            class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 transition-colors"
                            title="Shortcut: F1">
                        Checkout (F1)
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Checkout Modal -->
    @if($showCheckoutModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold mb-4">Checkout</h3>
                
                <form wire:submit="processCheckout">
                    <!-- Customer Info -->
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pelanggan</label>
                            <input type="text" wire:model="customerName" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                            <input type="text" wire:model="customerPhone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                            <select wire:model="paymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="cash">Tunai</option>
                                <option value="card">Kartu</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Bayar</label>
                            <input type="number" wire:model.live="amountPaid" min="{{ $total }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                            <textarea wire:model="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                    
                    <!-- Summary -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="flex justify-between text-sm mb-2">
                            <span>Total:</span>
                            <span class="font-semibold">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm mb-2">
                            <span>Bayar:</span>
                            <span>Rp {{ number_format($amountPaid, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-semibold border-t pt-2">
                            <span>Kembalian:</span>
                            <span class="{{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                Rp {{ number_format($change, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Buttons -->
                    <div class="flex space-x-3">
                        <button type="button" wire:click="closeCheckout" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" {{ $amountPaid < $total ? 'disabled' : '' }}>
                            Proses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Receipt Modal -->
    @if($showReceiptModal && $lastSale)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4" id="receipt">
                <div class="text-center mb-6">
                    <h3 class="text-lg font-bold">STRUK PENJUALAN</h3>
                    <p class="text-sm text-gray-600">{{ $lastSale->sale_number }}</p>
                    <p class="text-xs text-gray-500">{{ $lastSale->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
                
                <!-- Customer Info -->
                @if($lastSale->customer_name)
                    <div class="mb-4 text-sm">
                        <p><strong>Pelanggan:</strong> {{ $lastSale->customer_name }}</p>
                        @if($lastSale->customer_phone)
                            <p><strong>Telepon:</strong> {{ $lastSale->customer_phone }}</p>
                        @endif
                    </div>
                @endif
                
                <!-- Items -->
                <div class="border-t border-b border-gray-300 py-3 mb-4">
                    @foreach($lastSale->items as $item)
                        <div class="flex justify-between text-sm mb-1">
                            <div class="flex-1">
                                <div>{{ $item->product->name }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="text-right">
                                Rp {{ number_format($item->total_price, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Summary -->
                <div class="text-sm space-y-1 mb-4">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>Rp {{ number_format($lastSale->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($lastSale->discount_amount > 0)
                        <div class="flex justify-between text-red-600">
                            <span>Diskon:</span>
                            <span>-Rp {{ number_format($lastSale->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-semibold border-t pt-1">
                        <span>Total:</span>
                        <span>Rp {{ number_format($lastSale->final_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Bayar ({{ ucfirst($lastSale->payment_method) }}):</span>
                        <span>Rp {{ number_format($lastSale->amount_paid, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-semibold">
                        <span>Kembalian:</span>
                        <span>Rp {{ number_format($lastSale->change_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
                
                @if($lastSale->notes)
                    <div class="text-xs text-gray-600 mb-4">
                        <strong>Catatan:</strong> {{ $lastSale->notes }}
                    </div>
                @endif
                
                <div class="text-center text-xs text-gray-500 mb-6">
                    <p>Terima kasih atas kunjungan Anda!</p>
                    <p>Kasir: {{ $lastSale->cashier->name ?? 'System' }}</p>
                </div>
                
                <!-- Buttons -->
                <div class="flex space-x-3">
                    <button wire:click="closeReceipt" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                        Tutup
                    </button>
                    <button wire:click="printReceipt" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Print
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('print-receipt', () => {
            window.print();
        });
        
        // Auto-focus barcode input after page load
        setTimeout(() => {
            const barcodeInput = document.getElementById('barcode-input');
            if (barcodeInput) {
                barcodeInput.focus();
            }
        }, 100);
        
        // Re-focus barcode input after Livewire updates
        Livewire.hook('morph.updated', () => {
            setTimeout(() => {
                const barcodeInput = document.getElementById('barcode-input');
                if (barcodeInput && !document.querySelector('.modal')) {
                    barcodeInput.focus();
                }
            }, 50);
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Don't trigger shortcuts if user is typing in input fields (except barcode)
            if (e.target.tagName === 'INPUT' && e.target.id !== 'barcode-input') {
                return;
            }
            
            // F1 - Open Checkout (if cart not empty)
            if (e.key === 'F1') {
                e.preventDefault();
                @this.call('openCheckout');
            }
            
            // F2 - Clear Cart
            if (e.key === 'F2') {
                e.preventDefault();
                if (confirm('Yakin ingin mengosongkan keranjang?')) {
                    @this.call('clearCart');
                }
            }
            
            // F3 - Focus barcode input
            if (e.key === 'F3') {
                e.preventDefault();
                const barcodeInput = document.getElementById('barcode-input');
                if (barcodeInput) {
                    barcodeInput.focus();
                    barcodeInput.select();
                }
            }
            
            // F4 - Focus product search
            if (e.key === 'F4') {
                e.preventDefault();
                const searchInput = document.querySelector('input[wire\\:model\\.live="productSearch"]');
                if (searchInput) {
                    searchInput.focus();
                }
            }
            
            // ESC - Close modals or clear search
            if (e.key === 'Escape') {
                if (document.querySelector('.modal')) {
                    @this.call('closeCheckout');
                    @this.call('closeReceipt');
                } else {
                    // Clear search and focus barcode
                    @this.set('productSearch', '');
                    setTimeout(() => {
                        const barcodeInput = document.getElementById('barcode-input');
                        if (barcodeInput) {
                            barcodeInput.focus();
                        }
                    }, 50);
                }
            }
            
            // Enter - Quick add first product from search results
            if (e.key === 'Enter' && e.target.id !== 'barcode-input') {
                const firstProduct = document.querySelector('.product-card');
                if (firstProduct) {
                    firstProduct.click();
                }
            }
        });
        
        // Barcode validation and formatting
        const barcodeInput = document.getElementById('barcode-input');
        if (barcodeInput) {
            barcodeInput.addEventListener('input', function(e) {
                // Remove non-alphanumeric characters
                let value = e.target.value.replace(/[^a-zA-Z0-9]/g, '');
                
                // Auto-submit when barcode length is typical (8, 12, 13 digits)
                if (value.length >= 8 && /^\d+$/.test(value)) {
                    // Typical barcode lengths: EAN-8 (8), UPC-A (12), EAN-13 (13)
                    if ([8, 12, 13].includes(value.length)) {
                        setTimeout(() => {
                            @this.call('addProductByBarcode');
                        }, 100);
                    }
                }
            });
            
            // Clear barcode input after successful scan
            barcodeInput.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    setTimeout(() => {
                        this.focus();
                    }, 100);
                }
            });
        }
    });
    
    // Flash message auto-hide
    document.addEventListener('DOMContentLoaded', function() {
        const flashMessages = document.querySelectorAll('.fixed.top-4.right-4');
        flashMessages.forEach(message => {
            setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => {
                    message.remove();
                }, 300);
            }, 3000);
        });
    });
</script>