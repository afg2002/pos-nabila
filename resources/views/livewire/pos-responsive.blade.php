<div x-data="{
    showCart: false,
    customerOpen: false,
    customItemOpen: false,
    priceTypeOpen: false,
    customName: '',
    customPrice: 0,
    customQty: 1,
    customDesc: '',
    loadedCount: 20,
    showSearchResults: false,
    renamingTab: null,
    openCheckout() {
        console.log('Checkout attempt started...');
        const cartCount = this.$store.transactions.cartCount;
        console.log('Cart count (validation):', cartCount);

        if (cartCount === 0) {
            console.warn('Cart is empty, aborting checkout');
            alert('Keranjang masih kosong! Silakan tambahkan produk terlebih dahulu.');
            return;
        }

        try {
            const component = window.Livewire && window.Livewire.find('<?php echo e($_instance->getId()); ?>');
            if (!component) {
                console.error('Livewire component not available');
                alert('Sistem checkout tidak tersedia. Silakan refresh halaman.');
                return;
            }

            return component.call('openCheckout')
                .then((result) => {
                    console.log('Checkout modal opened successfully:', result);
                })
                .catch((error) => {
                    console.error('Livewire checkout error:', error);
                    alert('Gagal membuka checkout: ' + (error.message || 'Unknown error'));
                });
        } catch (error) {
            console.error('Checkout function error:', error);
            alert('Terjadi kesalahan saat membuka checkout: ' + error.message);
        }
    },
    handleCheckout() {
        console.log('Desktop checkout initiated...');
        console.log('Cart count:', this.$store.transactions.cartCount);
        return this.openCheckout();
    }
}" class="pos-responsive-wrapper">

<!-- Alpine.js Multi-Tab Transaction Store -->
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('transactions', {
        tabs: [],
        activeTabIndex: 0,
        nextId: 1,

        init() {
            // Load from localStorage
            const cached = localStorage.getItem('pos_multitab');
            if (cached) {
                try {
                    const data = JSON.parse(cached);
                    if (data.tabs && data.tabs.length > 0) {
                        this.tabs = data.tabs;
                        this.activeTabIndex = data.activeTabIndex || 0;
                        this.nextId = data.nextId || this.tabs.length + 1;

                        // Sync to Livewire after page load
                        setTimeout(() => {
                            this.syncToLivewire();
                        }, 300);
                        return;
                    }
                } catch (e) {
                    console.error('Error loading tabs:', e);
                }
            }

            // Create first tab if none exists
            this.addTab();
        },

        syncToLivewire() {
            const tab = this.activeTab;
            if (!tab) return;

            try {
                const component = window.Livewire && window.Livewire.find('<?php echo e($_instance->getId()); ?>');
                if (!component) {
                    console.log('Livewire component not found for sync');
                    return;
                }

                console.log('Syncing Alpine cart to Livewire...', tab.items.length, 'items');

                // Set active tab index
                component.call('setActiveTab', this.activeTabIndex);

                // Add each item to Livewire cart
                tab.items.forEach((item, index) => {
                    setTimeout(() => {
                        component.call('addToCart', item.id).then(() => {
                            // Update quantity if more than 1
                            if (item.qty > 1) {
                                component.call('updateQuantity', item.id, item.qty);
                            }
                            // Update price if custom
                            if (item.priceType === 'custom' || item.price !== item.basePrice) {
                                component.call('updatePrice', item.id, item.price);
                            }
                        });
                    }, index * 50); // Stagger calls to avoid race conditions
                });

                // Sync customer info
                if (tab.customer && (tab.customer.name || tab.customer.phone)) {
                    setTimeout(() => {
                        component.call('setCustomerInfo', tab.customer.name || '', tab.customer.phone || '');
                    }, tab.items.length * 50 + 100);
                }

                console.log('Cart sync completed');
            } catch (error) {
                console.error('Error syncing to Livewire:', error);
            }
        },

        addTab() {
            const newTab = {
                id: this.nextId++,
                name: `Transaksi ${this.tabs.length + 1}`,
                items: [],
                customer: {
                    name: '',
                    phone: ''
                },
                createdAt: Date.now()
            };
            this.tabs.push(newTab);
            this.activeTabIndex = this.tabs.length - 1;
            this.save();
            return newTab;
        },

        removeTab(index) {
            if (this.tabs.length === 1) {
                // Don't remove last tab, just clear it
                this.tabs[0].items = [];
                this.tabs[0].customer = { name: '', phone: '' };
            } else {
                this.tabs.splice(index, 1);
                if (this.activeTabIndex >= this.tabs.length) {
                    this.activeTabIndex = this.tabs.length - 1;
                }
            }
            this.save();
        },

        switchTab(index) {
            if (index >= 0 && index < this.tabs.length) {
                this.activeTabIndex = index;
                this.save();
            }
        },

        renameTab(index, newName) {
            if (this.tabs[index]) {
                this.tabs[index].name = newName;
                this.save();
            }
        },

        // Get active tab
        get activeTab() {
            return this.tabs[this.activeTabIndex] || null;
        },

        // Add product to active tab
        addProduct(product, $wire) {
            const tab = this.activeTab;
            if (!tab) return;

            const existing = tab.items.find(i => i.id === product.id);
            if (existing) {
                // Increment locally and sync explicit quantity to server to avoid any price/type resets
                existing.qty++;
                this.save();

                if ($wire) {
                    $wire.call('setActiveTab', this.activeTabIndex);
                    const cartKey = `product_${product.id}`;
                    $wire.call('updateQuantity', cartKey, existing.qty);
                }
                return;
            } else {
                // Normalize tier prices from provided product object
                const tierPrices = {
                    retail: typeof product.priceRetail !== 'undefined' ? product.priceRetail : (typeof product.price !== 'undefined' ? product.price : 0),
                    semi_grosir: typeof product.priceSemiGrosir !== 'undefined' ? product.priceSemiGrosir : (typeof product.price_semi_grosir !== 'undefined' ? product.price_semi_grosir : (typeof product.price !== 'undefined' ? product.price : 0)),
                    grosir: typeof product.priceGrosir !== 'undefined' ? product.priceGrosir : (typeof product.price_grosir !== 'undefined' ? product.price_grosir : (typeof product.price !== 'undefined' ? product.price : 0)),
                };

                tab.items.push({
                    id: product.id,
                    key: `product_${product.id}`,
                    name: product.name,
                    price: tierPrices.retail,
                    basePrice: tierPrices.retail,
                    tierPrices,
                    priceType: 'retail',
                    qty: 1
                });
                // Ensure UI price matches server-side rounding for default tier
                const last = tab.items[tab.items.length - 1];
                if (last) {
                    last.price = this.roundServerStyle(last.price);
                    last.basePrice = this.roundServerStyle(last.basePrice ?? last.price);
                }
            }
            this.save();

            // Sync to Livewire for new product additions
            if ($wire) {
                $wire.call('setActiveTab', this.activeTabIndex);
                $wire.call('addToCart', product.id);
            }
        },

        // Helper: get price by tier for a cart item
        getTierPrice(item, type) {
            if (!item) return 0;
            if (item.tierPrices && item.tierPrices[type] != null) {
                return item.tierPrices[type];
            }
            // Fallbacks if tierPrices are missing
            if (type === 'retail') return item.basePrice ?? item.price ?? 0;
            if (type === 'semi_grosir') return item.semiPrice ?? item.basePrice ?? item.price ?? 0;
            if (type === 'grosir') return item.grosirPrice ?? item.basePrice ?? item.price ?? 0;
            return item.price ?? 0;
        },

        updateItemPrice(productId, newPrice, priceType, $wire) {
            const tab = this.activeTab;
            if (!tab) return;

            const item = tab.items.find(i => i.id === productId);
            if (item) {
                const numericPrice = Number(newPrice);
                if (Number.isNaN(numericPrice)) {
                    // Ignore invalid input and keep current price
                    return;
                }
                const targetType = priceType || item.priceType;
                if (targetType && targetType !== 'custom') {
                    item.price = this.roundServerStyle(numericPrice);
                } else {
                    item.price = numericPrice;
                }
                if (priceType) {
                    item.priceType = priceType;
                }
                this.save();

                if ($wire) {
                    $wire.call('setActiveTab', this.activeTabIndex);
                    const cartKey = `product_${productId}`;
                    $wire.call('updateItemPriceType', cartKey, priceType || item.priceType);
                }
            }
        },

        roundItemPrice(productId, roundAmount, $wire) {
            const tab = this.activeTab;
            if (!tab) return;

            const item = tab.items.find(i => i.id === productId);
            if (item) {
                item.price = item.price + roundAmount;
                item.priceType = 'custom';
                this.save();

                if ($wire) {
                    $wire.call('setActiveTab', this.activeTabIndex);
                    const cartKey = `product_${productId}`;
                    $wire.call('updatePrice', cartKey, item.price);
                }
            }
        },

        // Mirror backend rounding behaviour to keep Alpine + Livewire totals aligned
        roundServerStyle(amount) {
            const value = Number(amount);
            if (Number.isNaN(value)) return 0;
            if (value <= 0) return 0;
            if (value < 1000) {
                return Math.ceil(value / 100) * 100;
            }
            if (value < 10000) {
                return Math.ceil(value / 500) * 500;
            }
            return Math.ceil(value / 1000) * 1000;
        },

        // Auto rounding mirroring server logic (<1000 -> 100, <10000 -> 500, >=10000 -> 1000)
        autoRoundUp(amount) {
            const val = Number(amount);
            if (Number.isNaN(val) || val <= 0) return null;
            return this.roundServerStyle(val);
        },

        // Realtime custom price update with auto-round and server sync
        updateCustomPrice(productId, rawPrice, $wire) {
            const tab = this.activeTab;
            if (!tab) return;

            const item = tab.items.find(i => i.id === productId);
            if (!item) return;

            // Preserve the raw value the user is typing to avoid disruptive input updates
            item.rawCustomPrice = rawPrice;

            const rounded = this.autoRoundUp(rawPrice);
            if (rounded === null) return; // guard against NaN/invalid

            item.price = rounded;
            item.priceType = 'custom';
            this.save();

            if ($wire) {
                $wire.call('setActiveTab', this.activeTabIndex);
                const cartKey = `product_${productId}`;
                $wire.call('updatePrice', cartKey, rounded);
            }
        },

        removeProduct(productId, $wire) {
            const tab = this.activeTab;
            if (!tab) return;

            tab.items = tab.items.filter(i => i.id !== productId);
            this.save();

            if ($wire) {
                $wire.call('setActiveTab', this.activeTabIndex);
                const cartKey = `product_${productId}`;
                $wire.call('removeFromCart', cartKey);
            }
        },

        updateQty(productId, qty, $wire) {
            const tab = this.activeTab;
            if (!tab) return;

            const item = tab.items.find(i => i.id === productId);
            if (item) {
                item.qty = Math.max(1, qty);
                this.save();

                if ($wire) {
                    $wire.call('setActiveTab', this.activeTabIndex);
                    const cartKey = `product_${productId}`;
                    $wire.call('updateQuantity', cartKey, qty);
                }
            }
        },

        updateCustomer(name, phone) {
            const tab = this.activeTab;
            if (!tab) return;

            tab.customer.name = name;
            tab.customer.phone = phone;
            this.save();
        },

        // Calculate totals for active tab
        get cartCount() {
            const tab = this.activeTab;
            return tab ? tab.items.reduce((sum, i) => sum + i.qty, 0) : 0;
        },

        get cartTotal() {
            const tab = this.activeTab;
            return tab ? tab.items.reduce((sum, i) => sum + (i.price * i.qty), 0) : 0;
        },

        // Get cart items for active tab
        get cartItems() {
            const tab = this.activeTab;
            return tab ? tab.items : [];
        },

        clearActiveTab() {
            const tab = this.activeTab;
            if (tab) {
                tab.items = [];
                tab.customer = { name: '', phone: '' };
                this.save();
            }
        },

        save() {
            localStorage.setItem('pos_multitab', JSON.stringify({
                tabs: this.tabs,
                activeTabIndex: this.activeTabIndex,
                nextId: this.nextId,
                timestamp: Date.now()
            }));
        }
    });
});

// LocalStorage Product Cache
const PRODUCTS_CACHE_KEY = 'pos_products';
const CACHE_TTL = 5 * 60 * 1000; // 5 minutes

function cacheProducts(products) {
    localStorage.setItem(PRODUCTS_CACHE_KEY, JSON.stringify({
        data: products,
        timestamp: Date.now()
    }));
}

function getCachedProducts() {
    const cached = localStorage.getItem(PRODUCTS_CACHE_KEY);
    if (!cached) return null;

    try {
        const { data, timestamp } = JSON.parse(cached);
        if (Date.now() - timestamp > CACHE_TTL) {
            localStorage.removeItem(PRODUCTS_CACHE_KEY);
            return null;
        }
        return data;
    } catch (e) {
        return null;
    }
}
</script>

<style>
/* ===================================
   RESPONSIVE POS - MAXIMUM PERFORMANCE
   =================================== */

/* Base Container */
.pos-responsive-wrapper {
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fafc 0%, #e8eef5 100%);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Two Column Layout: Desktop Only */
.pos-two-column {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 20px;
    max-width: 1600px;
    margin: 0 auto;
    padding: 20px;
    min-height: 100vh;
}

/* Product Section (Left) */
.pos-products-section {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 40px);
    overflow: hidden;
}

.products-scroll-area {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    margin: 0 -20px;
    padding: 0 20px;
}

/* Cart Section (Right) - Desktop Only */
.pos-cart-section {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 40px);
    position: sticky;
    top: 20px;
}

/* Cart Items */
.cart-items {
    flex: 1;
    overflow-y: auto;
    margin: 16px 0;
    padding-right: 8px;
}

.cart-item {
    background: #f9fafb;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 10px;
    transition: all 0.2s;
}

.cart-item:hover {
    background: #f3f4f6;
}

/* Quantity Controls */
.qty-control {
    display: flex;
    align-items: center;
    gap: 8px;
}

.qty-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: #3b82f6;
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.qty-btn:hover {
    background: #1d4ed8;
    transform: scale(1.1);
}

.qty-btn:active {
    transform: scale(0.95);
}

/* Dropdown Sections */
.dropdown-section {
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    margin-bottom: 12px;
    overflow: hidden;
    transition: all 0.3s;
}

.dropdown-header {
    padding: 12px 16px;
    background: #f9fafb;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    color: #374151;
    transition: all 0.2s;
}

.dropdown-header:hover {
    background: #f3f4f6;
}

.dropdown-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
}

.dropdown-content.open {
    max-height: 500px;
    padding: 16px;
    border-top: 1px solid #e5e7eb;
}

.chevron-icon {
    margin-left: auto;
    transition: transform 0.3s;
}

.chevron-icon.open {
    transform: rotate(180deg);
}

/* Form Inputs */
.input-field {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}

.input-field:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Buttons */
.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 12px 24px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
}

.btn-primary:active {
    transform: translateY(0);
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

/* Price Summary */
.price-summary {
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    border-radius: 12px;
    padding: 16px;
    margin-top: 16px;
}

.price-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 14px;
    color: #6b7280;
}

.price-total {
    display: flex;
    justify-content: space-between;
    font-size: 24px;
    font-weight: 700;
    color: #1e40af;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 2px solid #bfdbfe;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #9ca3af;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.3;
}

/* Search Bar */
.search-bar {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
}

.search-input {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 15px;
    min-height: 56px;
}

.search-input:focus {
    outline: none;
    border-color: #3b82f6;
}

/* ===================================
   MOBILE/TABLET: COMPACT HORIZONTAL CARDS
   =================================== */

/* Compact Product Cards (Horizontal Layout) */
.product-compact {
    display: flex;
    align-items: center;
    gap: 12px;
    background: white;
    border-radius: 12px;
    padding: 12px;
    margin-bottom: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    cursor: pointer;
    transition: all 0.2s;
    min-height: 80px;
    border: 2px solid transparent;
}

.product-compact:active {
    transform: scale(0.98);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    border-color: #3b82f6;
}

.product-thumb {
    width: 56px;
    height: 56px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
    background: #f3f4f6;
}

.product-info {
    flex: 1;
    min-width: 0;
}

.product-name {
    font-weight: 600;
    font-size: 14px;
    color: #1f2937;
    margin-bottom: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.product-price {
    font-weight: 700;
    font-size: 16px;
    color: #3b82f6;
    margin-bottom: 4px;
}

.product-sku {
    font-size: 10px;
    color: #9ca3af;
}

.stock-badge {
    font-size: 10px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 4px;
    display: inline-block;
}

.stock-high { background: #dcfce7; color: #166534; }
.stock-medium { background: #fef3c7; color: #92400e; }
.stock-low { background: #fee2e2; color: #991b1b; }

.quick-add-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    transition: all 0.2s;
    cursor: pointer;
}

.quick-add-btn:active,
.quick-add-btn.adding {
    transform: scale(1.2);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
}

.load-more-btn {
    width: 100%;
    padding: 16px;
    background: #f3f4f6;
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    font-weight: 600;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
    min-height: 56px;
    cursor: pointer;
}

.load-more-btn:hover {
    background: #e5e7eb;
    border-color: #9ca3af;
}

.load-more-btn:active {
    transform: scale(0.98);
}

/* ===================================
   MOBILE: BOTTOM NAVIGATION BAR
   =================================== */

.bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 64px;
    background: white;
    border-top: 1px solid #e5e7eb;
    display: none;
    justify-content: space-around;
    align-items: center;
    z-index: 100;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
}

.nav-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 8px;
    font-size: 12px;
    color: #6b7280;
    transition: all 0.2s;
    min-height: 56px;
    border: none;
    background: transparent;
    cursor: pointer;
    position: relative;
}

.nav-item:active {
    background: #f3f4f6;
    transform: scale(0.95);
}

.nav-item:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.nav-item:disabled:active {
    transform: none;
    background: transparent;
}

.nav-item i {
    font-size: 24px;
}

.cart-badge-nav {
    position: absolute;
    top: 4px;
    right: 20%;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: bold;
}

/* ===================================
   MOBILE: FULL SCREEN CART MODAL
   =================================== */

.cart-modal-fullscreen {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 200;
    display: flex;
    align-items: flex-end;
}

.cart-modal-content {
    background: white;
    border-radius: 20px 20px 0 0;
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    padding-bottom: 80px; /* Space for bottom nav */
}

.swipe-handle {
    width: 40px;
    height: 4px;
    background: #d1d5db;
    border-radius: 2px;
    margin: 12px auto;
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
}

.close-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #f3f4f6;
    border: none;
    font-size: 24px;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.cart-items-mobile {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
}

.cart-item-mobile {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 12px;
    margin-bottom: 12px;
}

.item-info {
    flex: 1;
    min-width: 0;
}

.item-info h4 {
    font-weight: 600;
    font-size: 14px;
    color: #1f2937;
    margin-bottom: 4px;
}

.item-price {
    font-weight: 700;
    font-size: 16px;
    color: #3b82f6;
}

.qty-controls-large {
    display: flex;
    align-items: center;
    gap: 12px;
}

.qty-btn-large {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #3b82f6;
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    cursor: pointer;
}

.qty-btn-large:active {
    transform: scale(0.9);
}

.qty-display {
    font-size: 20px;
    font-weight: 700;
    min-width: 40px;
    text-align: center;
}

.remove-btn-mobile {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #fee2e2;
    color: #dc2626;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
    cursor: pointer;
}

.remove-btn-mobile:active {
    transform: scale(0.9);
}

.cart-total-mobile {
    padding: 16px 20px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f9fafb;
    font-size: 18px;
    font-weight: 600;
}

.total-amount {
    font-size: 24px;
    font-weight: 700;
    color: #3b82f6;
}

.checkout-btn-large {
    margin: 16px 20px;
    padding: 18px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 700;
    border: none;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
    min-height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
}

.checkout-btn-large:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.checkout-btn-large:active:not(:disabled) {
    transform: scale(0.98);
}

/* ===================================
   MOBILE RESPONSIVE
   =================================== */

@media (max-width: 768px) {
    .pos-two-column {
        grid-template-columns: 1fr;
        padding: 12px;
        padding-bottom: 80px; /* Space for bottom nav */
        gap: 0;
    }

    .pos-cart-section {
        display: none; /* Hide desktop cart on mobile */
    }

    .pos-products-section {
        max-height: none;
        margin-bottom: 0;
        overflow: visible;
    }

    .products-scroll-area {
        overflow-y: visible;
        margin: 0;
        padding: 0;
    }

    .bottom-nav {
        display: flex; /* Show bottom nav on mobile */
    }

    .search-bar {
        flex-direction: column;
        gap: 8px;
    }
}

/* Tablet Responsive */
@media (min-width: 769px) and (max-width: 1024px) {
    .pos-two-column {
        grid-template-columns: 1fr;
        padding: 16px;
        padding-bottom: 80px;
    }

    .pos-cart-section {
        display: none;
    }

    .bottom-nav {
        display: flex;
    }
}

/* Desktop */
@media (min-width: 1025px) {
    .product-compact {
        display: none; /* Hide compact cards on desktop */
    }

    /* Show desktop grid */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 16px;
        margin-top: 16px;
    }

    .product-card {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        min-height: 140px;
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        transform: translateY(-2px);
    }

    .product-card:active {
        transform: translateY(0) scale(0.98);
    }

    .product-image {
        width: 100%;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 8px;
        background: #f3f4f6;
    }

    .stock-badge-desktop {
        position: absolute;
        top: 8px;
        right: 8px;
        padding: 3px 8px;
        border-radius: 8px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }
}

/* Scrollbar Custom */
.cart-items::-webkit-scrollbar,
.products-scroll-area::-webkit-scrollbar,
.cart-items-mobile::-webkit-scrollbar {
    width: 6px;
}

.cart-items::-webkit-scrollbar-track,
.products-scroll-area::-webkit-scrollbar-track,
.cart-items-mobile::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 10px;
}

.cart-items::-webkit-scrollbar-thumb,
.products-scroll-area::-webkit-scrollbar-thumb,
.cart-items-mobile::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.cart-items::-webkit-scrollbar-thumb:hover,
.products-scroll-area::-webkit-scrollbar-thumb:hover,
.cart-items-mobile::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Loading State */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.spinner {
    width: 60px;
    height: 60px;
    border: 5px solid #f3f4f6;
    border-top: 5px solid #3b82f6;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Alpine.js cloak */
[x-cloak] {
    display: none !important;
}

/* ===================================
   MULTI-TAB TRANSACTION SYSTEM
   =================================== */

.tab-navigation {
    background: white;
    border-bottom: 2px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 8px 12px;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    position: sticky;
    top: 0;
    z-index: 50;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.tab-navigation::-webkit-scrollbar {
    height: 4px;
}

.tab-navigation::-webkit-scrollbar-track {
    background: #f3f4f6;
}

.tab-navigation::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.tab-item {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 8px;
    border: 2px solid #e5e7eb;
    background: #f9fafb;
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
    white-space: nowrap;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 120px;
    max-width: 200px;
}

.tab-item:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.tab-item.active {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border-color: #3b82f6;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.tab-name {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    min-width: 60px;
}

.tab-name-input {
    flex: 1;
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 4px;
    padding: 2px 6px;
    color: white;
    font-size: 13px;
    font-weight: 500;
    outline: none;
}

.tab-name-input::placeholder {
    color: rgba(255,255,255,0.6);
}

.tab-badge {
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
    min-width: 20px;
    text-align: center;
}

.tab-item:not(.active) .tab-badge {
    background: #3b82f6;
    color: white;
}

.tab-close {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: all 0.2s;
    flex-shrink: 0;
}

.tab-close:hover {
    background: rgba(255,255,255,0.3);
    transform: scale(1.1);
}

.tab-item:not(.active) .tab-close {
    background: #fee2e2;
    color: #dc2626;
}

.tab-item:not(.active) .tab-close:hover {
    background: #fecaca;
}

.add-tab-btn {
    padding: 8px 12px;
    border-radius: 8px;
    border: 2px dashed #cbd5e1;
    background: transparent;
    color: #6b7280;
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    flex-shrink: 0;
}

.add-tab-btn:hover {
    border-color: #3b82f6;
    color: #3b82f6;
    background: #eff6ff;
}

.add-tab-btn:active {
    transform: scale(0.95);
}

/* Search Results Dropdown (Mobile/Tablet) */
.search-results-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid #3b82f6;
    border-top: none;
    border-radius: 0 0 12px 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    max-height: 300px;
    overflow-y: auto;
    z-index: 200;
    margin-top: -1px;
}

.search-result-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    transition: all 0.2s;
}

.search-result-item:hover {
    background: #f9fafb;
}

.search-result-item:active {
    background: #eff6ff;
}

.search-result-item.highlighted {
    background: #eff6ff;
    border-left: 3px solid #3b82f6;
}

.search-result-thumb {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    object-fit: cover;
    background: #f3f4f6;
    flex-shrink: 0;
}

.search-result-info {
    flex: 1;
    min-width: 0;
}

.search-result-name {
    font-weight: 600;
    font-size: 14px;
    color: #1f2937;
    margin-bottom: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.search-result-price {
    font-weight: 700;
    font-size: 14px;
    color: #3b82f6;
}

.search-result-sku {
    font-size: 11px;
    color: #9ca3af;
}

.search-result-add {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #3b82f6;
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    transition: all 0.2s;
}

.search-result-add:active {
    transform: scale(1.15);
    background: #1d4ed8;
}

.search-no-results {
    padding: 24px;
    text-align: center;
    color: #9ca3af;
    font-size: 14px;
}
</style>

<!-- Offline Indicator -->
<div wire:offline class="fixed top-4 left-1/2 transform -translate-x-1/2 z-[9999] bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 animate-pulse">
    <i class="fas fa-wifi-slash text-xl"></i>
    <span class="font-semibold">Koneksi Terputus - Mode Offline</span>
</div>

<!-- Multi-Tab Navigation -->
<div class="tab-navigation" x-init="$store.transactions.init()">
    <!-- Tab Items -->
    <template x-for="(tab, index) in $store.transactions.tabs" :key="tab.id">
        <div class="tab-item"
             :class="{ 'active': index === $store.transactions.activeTabIndex }"
             @click="$store.transactions.switchTab(index); $wire.call('setActiveTab', index)">

            <!-- Tab Name (Editable on double-click) -->
            <template x-if="renamingTab === index">
                <input type="text"
                       x-model="tab.name"
                       @blur="renamingTab = null; $store.transactions.save()"
                       @keydown.enter="renamingTab = null; $store.transactions.save()"
                       @keydown.escape="renamingTab = null"
                       @click.stop
                       x-init="$el.focus(); $el.select()"
                       class="tab-name-input"
                       placeholder="Nama transaksi">
            </template>
            <template x-if="renamingTab !== index">
                <span class="tab-name" @dblclick.stop="renamingTab = index" x-text="tab.name"></span>
            </template>

            <!-- Item Count Badge -->
            <span class="tab-badge" x-show="tab.items.length > 0" x-text="tab.items.reduce((sum, i) => sum + i.qty, 0)"></span>

            <!-- Close Button -->
            <button class="tab-close"
                    @click.stop="
                        if(confirm('Hapus transaksi ' + tab.name + '?')) {
                            $store.transactions.removeTab(index);
                            $wire.call('setActiveTab', $store.transactions.activeTabIndex);
                        }
                    ">
                ×
            </button>
        </div>
    </template>

    <!-- Add New Tab Button -->
    <button class="add-tab-btn"
            @click="$store.transactions.addTab(); $wire.call('setActiveTab', $store.transactions.activeTabIndex)">
        <i class="fas fa-plus"></i>
        <span>Transaksi Baru</span>
    </button>
</div>

<!-- Two Column Layout -->
<div class="pos-two-column">

    <!-- LEFT: Products Section -->
    <div class="pos-products-section">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-store text-blue-600 mr-2"></i>
                POS Kasir
            </h2>
            <div class="flex items-center gap-3">
                <div class="text-sm text-gray-600 flex items-center gap-2">
                    <i class="fas fa-warehouse mr-1"></i>
                    {{ $activeWarehouseName ?? 'Gudang' }}
                    <!-- Online Indicator -->
                    <span wire:online class="flex items-center gap-1 text-green-600 text-xs">
                        <i class="fas fa-circle text-[6px]"></i>
                        Online
                    </span>
                </div>
                <button type="button"
                        wire:click="goToSalesHistory"
                        class="hidden md:flex btn-secondary items-center gap-2 px-4 py-2 text-sm font-semibold">
                    <i class="fas fa-history text-blue-600"></i>
                    <span>Riwayat</span>
                </button>
            </div>
        </div>

        <!-- Search & Barcode -->
        <div class="search-bar mb-4">
            <!-- Search Input with Inline Results (Mobile/Tablet) -->
            <div class="relative flex-1 md:block lg:block"
                 x-data="{
                      highlightedIndex: 0,
                      searchHasFocus: false,
                      get shouldShowResults() {
                          return this.searchHasFocus && '{{ $productSearch }}' !== '' && window.innerWidth <= 1024;
                      },
                      handleKeydown(e) {
                          const productsData = @js($products->take(5)->map(function($p) {
                              return [
                                  'id' => $p->id,
                                  'name' => $p->name,
                                  'price' => $p->price_retail,
                                  'price_semi_grosir' => $p->price_semi_grosir ?? $p->price_retail,
                                  'price_grosir' => $p->price_grosir ?? $p->price_retail,
                                  'stock' => $p->warehouseStocks->first()->stock_on_hand ?? 0
                              ];
                          })->values()->toArray());

                          if (e.key === 'ArrowDown') {
                              e.preventDefault();
                              if (productsData.length > 0) {
                                  this.highlightedIndex = Math.min(this.highlightedIndex + 1, productsData.length - 1);
                              }
                          } else if (e.key === 'ArrowUp') {
                              e.preventDefault();
                              this.highlightedIndex = Math.max(this.highlightedIndex - 1, 0);
                          } else if (e.key === 'Enter') {
                              e.preventDefault();
                              const product = productsData[this.highlightedIndex];
                              if (product) {
                                  $store.transactions.addProduct({
                                      id: product.id,
                                      name: product.name,
                                      priceRetail: product.price,
                                      priceSemiGrosir: product.price_semi_grosir,
                                      priceGrosir: product.price_grosir,
                                      stock: product.stock
                                  }, $wire);
                                  $refs.searchInput.value = '';
                                  $wire.set('productSearch', '');
                                  this.searchHasFocus = false;
                                  this.highlightedIndex = 0;
                              }
                          } else if (e.key === 'Escape') {
                              this.searchHasFocus = false;
                              $refs.searchInput.blur();
                          }
                      },
                      focusSearch() {
                          this.$nextTick(() => {
                              const searchInput = this.$refs.searchInput;
                              if (searchInput) {
                                  searchInput.focus();
                                  // Auto-scroll ke search input
                                  searchInput.scrollIntoView({
                                      behavior: 'smooth',
                                      block: 'center',
                                      inline: 'nearest'
                                  });
                              }
                          });
                      }
                  }">
                <input type="text"
                       x-ref="searchInput"
                       wire:model.live.debounce.300ms="productSearch"
                       @focus="searchHasFocus = true; highlightedIndex = 0"
                       @blur="setTimeout(() => searchHasFocus = false, 200)"
                       @keydown="handleKeydown($event)"
                       @input="highlightedIndex = 0"
                       placeholder="Cari produk..."
                       class="search-input"
                       autocomplete="off">

                <!-- Inline Search Results (Mobile/Tablet Only) -->
                <div x-show="shouldShowResults"
                     x-cloak
                     class="search-results-dropdown">
                    @if($products->count() > 0)
                        @foreach($products->take(5) as $index => $product)
                            @php
                                $stock = $product->warehouseStocks->first()?->stock_on_hand ?? 0;
                            @endphp
                            <div class="search-result-item"
                                 :class="{ 'highlighted': highlightedIndex === {{ $index }} }"
                                 @mouseenter="highlightedIndex = {{ $index }}"
                                 @click="
                                     $store.transactions.addProduct({
                                         id: {{ $product->id }},
                                         name: '{{ addslashes($product->name) }}',
                                         priceRetail: {{ $product->price_retail }},
                                         priceSemiGrosir: {{ $product->price_semi_grosir ?? $product->price_retail }},
                                         priceGrosir: {{ $product->price_grosir ?? $product->price_retail }},
                                         stock: {{ $stock }}
                                     }, $wire);
                                     $refs.searchInput.value = '';
                                     $wire.set('productSearch', '');
                                     searchHasFocus = false;
                                     highlightedIndex = 0;
                                 ">
                                <img src="{{ $product->getPhotoUrl() }}"
                                     class="search-result-thumb"
                                     alt="{{ $product->name }}"
                                     onerror="this.src='{{ asset('storage/placeholders/no-image.svg') }}'">
                                <div class="search-result-info">
                                    <div class="search-result-name">{{ $product->name }}</div>
                                    <div class="search-result-price">Rp {{ number_format($product->price_retail, 0, ',', '.') }}</div>
                                    <div class="search-result-sku">SKU: {{ $product->sku }} • Stok: {{ $stock }}</div>
                                </div>
                                <button class="search-result-add" @click.stop="
                                    $store.transactions.addProduct({
                                        id: {{ $product->id }},
                                        name: '{{ addslashes($product->name) }}',
                                        priceRetail: {{ $product->price_retail }},
                                        priceSemiGrosir: {{ $product->price_semi_grosir ?? $product->price_retail }},
                                        priceGrosir: {{ $product->price_grosir ?? $product->price_retail }},
                                        stock: {{ $stock }}
                                    }, $wire);
                                    $refs.searchInput.value = '';
                                    $wire.set('productSearch', '');
                                ">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        @endforeach
                    @else
                        <div class="search-no-results">
                            <i class="fas fa-search text-2xl mb-2"></i>
                            <p>Tidak ada produk ditemukan</p>
                        </div>
                    @endif
                </div>
            </div>

            <input type="text"
                   x-ref="barcodeInput"
                   wire:model.live="barcode"
                   placeholder="Scan barcode..."
                   class="search-input"
                   style="max-width: 200px;">
        </div>

        <!-- Products Scrollable Area -->
        <div class="products-scroll-area">
            <!-- Mobile/Tablet: Compact Horizontal Cards -->
            <div class="md:hidden lg:hidden">
            @forelse($products as $product)
                @php
                    $stock = $product->warehouseStocks->first()?->stock_on_hand ?? 0;
                    $stockClass = $stock > 10 ? 'stock-high' : ($stock > 0 ? 'stock-medium' : 'stock-low');
                    $stockText = $stock > 10 ? 'Stok: ' . $stock : ($stock > 0 ? 'Low: ' . $stock : 'Habis');
                @endphp

                <div class="product-compact"
                     x-data="{
                         adding: false,
                         product: {
                             id: {{ $product->id }},
                             name: '{{ addslashes($product->name) }}',
                             priceRetail: {{ $product->price_retail }},
                             priceSemiGrosir: {{ $product->price_semi_grosir ?? $product->price_retail }},
                             priceGrosir: {{ $product->price_grosir ?? $product->price_retail }},
                             stock: {{ $stock }}
                         }
                     }"
                     @click="adding = true; setTimeout(() => adding = false, 500); $store.transactions.addProduct(product, $wire)">
                    <img src="{{ $product->getPhotoUrl() }}"
                         class="product-thumb"
                         alt="{{ $product->name }}"
                         onerror="this.src='{{ asset('storage/placeholders/no-image.svg') }}'">
                    <div class="product-info">
                        <h3 class="product-name">{{ $product->name }}</h3>
                        <p class="product-price">Rp {{ number_format($product->price_retail, 0, ',', '.') }}</p>
                        <span class="stock-badge {{ $stockClass }}">{{ $stockText }}</span>
                    </div>
                    <button @click.stop="adding = true; setTimeout(() => adding = false, 500); $store.transactions.addProduct(product, $wire)"
                            class="quick-add-btn"
                            :class="{ 'adding': adding }">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p class="text-lg font-medium">Tidak ada produk</p>
                    <p class="text-sm">Coba ubah filter pencarian</p>
                </div>
            @endforelse

            <!-- Load More Button (Mobile) -->
            @if($hasMoreProducts && $products->count() > 0)
                <div x-intersect="$wire.loadMore()" x-data>
                    <button wire:click="loadMore"
                            wire:loading.attr="disabled"
                            class="load-more-btn">
                        <span wire:loading.remove wire:target="loadMore">
                            Muat Lebih Banyak
                            <i class="fas fa-arrow-down"></i>
                        </span>
                        <span wire:loading wire:target="loadMore">
                            <i class="fas fa-spinner fa-spin"></i>
                            Memuat...
                        </span>
                    </button>
                </div>
            @endif
        </div>

        <!-- Desktop: Product Grid -->
        <div class="product-grid hidden lg:grid">
            @forelse($products as $product)
                @php
                    $stock = $product->warehouseStocks->first()?->stock_on_hand ?? 0;
                    $stockClass = $stock > 10 ? 'stock-high' : ($stock > 0 ? 'stock-medium' : 'stock-low');
                    $stockText = $stock > 10 ? 'Stok' : ($stock > 0 ? 'Low' : 'Habis');
                @endphp

                <div x-data="{
                         product: {
                             id: {{ $product->id }},
                             name: '{{ addslashes($product->name) }}',
                             priceRetail: {{ $product->price_retail }},
                             priceSemiGrosir: {{ $product->price_semi_grosir ?? $product->price_retail }},
                             priceGrosir: {{ $product->price_grosir ?? $product->price_retail }},
                             stock: {{ $stock }}
                         }
                     }"
                     @click="$store.transactions.addProduct(product, $wire)"
                     class="product-card">
                    <!-- Stock Badge -->
                    <span class="stock-badge-desktop {{ $stockClass }}">{{ $stockText }}</span>

                    <!-- Product Image -->
                    <img src="{{ $product->getPhotoUrl() }}"
                         alt="{{ $product->name }}"
                         class="product-image"
                         onerror="this.src='{{ asset('storage/placeholders/no-image.svg') }}'">

                    <!-- Product Info -->
                    <div class="flex-1">
                        <h3 class="font-semibold text-sm text-gray-900 mb-1 line-clamp-2">
                            {{ $product->name }}
                        </h3>
                        <p class="text-xs text-gray-500 mb-2">SKU: {{ $product->sku }}</p>
                        <p class="text-lg font-bold text-blue-600">
                            Rp {{ number_format($product->price_retail, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="col-span-full empty-state">
                    <i class="fas fa-box-open"></i>
                    <p class="text-lg font-medium">Tidak ada produk</p>
                    <p class="text-sm">Coba ubah filter pencarian</p>
                </div>
            @endforelse

            <!-- Load More Button -->
            @if($hasMoreProducts && $products->count() > 0)
                <div class="col-span-full flex justify-center py-4"
                     x-intersect="$wire.loadMore()"
                     x-data>
                    <button wire:click="loadMore"
                            wire:loading.attr="disabled"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <span wire:loading.remove wire:target="loadMore">
                            <i class="fas fa-arrow-down"></i>
                            Muat Lebih Banyak
                        </span>
                        <span wire:loading wire:target="loadMore">
                            <i class="fas fa-spinner fa-spin"></i>
                            Memuat...
                        </span>
                    </button>
                </div>
            @endif
        </div>
        </div> <!-- End products-scroll-area -->
    </div>

    <!-- RIGHT: Cart Section (Desktop Only) -->
    <div class="pos-cart-section hidden lg:flex">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-800">
                <i class="fas fa-shopping-cart text-blue-600 mr-2"></i>
                Keranjang
                <span class="text-sm font-normal text-gray-500" x-text="'(' + $store.transactions.cartCount + ' item)'"></span>
            </h3>
        </div>

        <!-- Active Tab Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg px-3 py-2 mb-3 text-xs">
            <div class="flex items-center gap-2">
                <i class="fas fa-receipt text-blue-600"></i>
                <span class="font-semibold text-blue-900" x-text="$store.transactions.activeTab?.name || 'Transaksi'"></span>
            </div>
        </div>

        <!-- Cart Items (Alpine-driven) -->
        <div class="cart-items">
            <!-- Alpine Template for Cart Items -->
            <template x-if="$store.transactions.cartItems.length === 0">
                <div class="empty-state">
                    <i class="fas fa-shopping-basket"></i>
                    <p class="text-base font-medium">Keranjang Kosong</p>
                    <p class="text-sm">Pilih produk untuk mulai</p>
                </div>
            </template>

            <template x-for="(item, index) in $store.transactions.cartItems" :key="item.key || item.id || index">
                <div class="cart-item" x-data="{ showCustomPrice: false }">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1">
                            <h4 class="font-semibold text-sm text-gray-900 mb-1" x-text="item.name"></h4>

                            <!-- Price Type Dropdown -->
                            <select @change="
                                const priceType = $event.target.value;
                                showCustomPrice = (priceType === 'custom');
                                if (priceType !== 'custom') {
                                    const newPrice = $store.transactions.getTierPrice(item, priceType);
                                    $store.transactions.updateItemPrice(item.id, newPrice, priceType, $wire);
                                } else {
                                    // Switch to custom: initialize rawCustomPrice with current price so input shows it
                                    item.priceType = 'custom';
                                    item.rawCustomPrice = item.price;
                                    // Also mark on backend as custom pricing tier
                                    if ($wire) {
                                        $wire.call('setActiveTab', $store.transactions.activeTabIndex);
                                        const cartKey = `product_${item.id}`;
                                        $wire.call('updatePrice', cartKey, item.price);
                                    }
                                }
                            "
                                    :value="item.priceType || 'retail'"
                                    class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:border-blue-500 mb-1"
                                    style="max-width: 150px;">
                                <option value="retail">Retail</option>
                                <option value="semi_grosir">Semi Grosir</option>
                                <option value="grosir">Grosir</option>
                                <option value="custom">Custom</option>
                            </select>

                            <!-- Custom Price Input -->
                            <div x-show="showCustomPrice || item.priceType === 'custom'"
                                 x-transition
                                 class="mt-1">
                                <input type="number"
                                       :value="item.rawCustomPrice ?? item.price"
                                       @input="$store.transactions.updateCustomPrice(item.id, $event.target.value, $wire)"
                                       placeholder="Harga custom"
                                       class="text-xs border border-blue-300 rounded px-2 py-1 w-full focus:outline-none focus:border-blue-500"
                                       min="0"
                                       step="100">
                            </div>
                        </div>
                        <button @click="$store.transactions.removeProduct(item.id, $wire)"
                                class="text-red-500 hover:text-red-700 ml-2">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="flex justify-between items-center mt-2">
                        <!-- Quantity Control -->
                        <div class="qty-control">
                            <button @click="$store.transactions.updateQty(item.id, item.qty - 1, $wire)"
                                    class="qty-btn"
                                    :disabled="item.qty <= 1">
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <span class="font-semibold text-gray-900 min-w-[30px] text-center" x-text="item.qty"></span>
                            <button @click="$store.transactions.updateQty(item.id, item.qty + 1, $wire)"
                                    class="qty-btn">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </div>

                        <!-- Price -->
                        <div class="text-right">
                            <p class="text-xs text-gray-500">
                                @ Rp <span x-text="item.price.toLocaleString('id-ID')"></span>
                            </p>
                            <p class="font-bold text-blue-600">
                                Rp <span x-text="(item.price * item.qty).toLocaleString('id-ID')"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Dropdown Forms -->
        <template x-if="$store.transactions.cartCount > 0">
            <div>
                <!-- Customer Info (Per-Tab) -->
                <div class="dropdown-section">
                    <div class="dropdown-header" @click="customerOpen = !customerOpen">
                        <i class="fas fa-user text-blue-600"></i>
                        <span>Info Pelanggan</span>
                        <i class="fas fa-chevron-down chevron-icon text-gray-400" :class="{'open': customerOpen}"></i>
                    </div>
                    <div class="dropdown-content" :class="{'open': customerOpen}">
                        <input type="text"
                               :value="$store.transactions.activeTab?.customer.name || ''"
                               @input="$store.transactions.updateCustomer($event.target.value, $store.transactions.activeTab?.customer.phone || '')"
                               @blur="$wire.call('setCustomerInfo', $store.transactions.activeTab?.customer.name, $store.transactions.activeTab?.customer.phone)"
                               placeholder="Nama pelanggan"
                               class="input-field mb-2">
                        <input type="text"
                               :value="$store.transactions.activeTab?.customer.phone || ''"
                               @input="$store.transactions.updateCustomer($store.transactions.activeTab?.customer.name || '', $event.target.value)"
                               @blur="$wire.call('setCustomerInfo', $store.transactions.activeTab?.customer.name, $store.transactions.activeTab?.customer.phone)"
                               placeholder="No. telepon"
                               class="input-field">
                    </div>
                </div>

            <!-- Custom Item -->
            <div class="dropdown-section">
                <div class="dropdown-header" @click="customItemOpen = !customItemOpen">
                    <i class="fas fa-plus-circle text-green-600"></i>
                    <span>Tambah Item Custom</span>
                    <i class="fas fa-chevron-down chevron-icon text-gray-400" :class="{'open': customItemOpen}"></i>
                </div>
                <div class="dropdown-content" :class="{'open': customItemOpen}">
                    <input type="text"
                           x-model="customName"
                           placeholder="Nama item/jasa"
                           class="input-field mb-2">
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <input type="number"
                               x-model="customPrice"
                               placeholder="Harga"
                               class="input-field"
                               min="0">
                        <input type="number"
                               x-model="customQty"
                               placeholder="Qty"
                               class="input-field"
                               min="1"
                               value="1">
                    </div>
                    <button @click="
                        if(customName && customPrice > 0) {
                            $wire.call('addCustomItem', {
                                customItemName: customName,
                                customItemPrice: customPrice,
                                customItemQuantity: customQty
                            });
                            customName = '';
                            customPrice = 0;
                            customQty = 1;
                            customItemOpen = false;
                        }
                    " class="btn-primary w-full">
                        <i class="fas fa-plus-circle"></i>
                        Tambahkan
                    </button>
                </div>
            </div>

            </div>
        </template>

        <!-- Price Summary (Always visible) -->
        <div class="price-summary">
            <div class="price-row">
                <span>Subtotal</span>
                <span class="font-semibold">Rp <span x-text="$store.transactions.cartTotal.toLocaleString('id-ID')"></span></span>
            </div>
            <div class="price-total">
                <span>TOTAL</span>
                <span>Rp <span x-text="$store.transactions.cartTotal.toLocaleString('id-ID')"></span></span>
            </div>
        </div>

        <!-- Checkout Button -->
        <button @click="handleCheckout()"
                :disabled="$store.transactions.cartCount === 0"
                class="btn-primary w-full mt-4"
                :class="{ 'opacity-50 cursor-not-allowed': $store.transactions.cartCount === 0 }">
            <i class="fas fa-credit-card"></i>
            Proses Pembayaran
        </button>
    </div>

</div>

<!-- MOBILE/TABLET: Bottom Navigation Bar -->
<nav class="bottom-nav">
    <button @click="$el.closest('.pos-responsive-wrapper').querySelector('[x-ref=barcodeInput]').focus()" class="nav-item">
        <i class="fas fa-barcode"></i>
        <span>Scan</span>
    </button>
    <button @click="$el.closest('.pos-responsive-wrapper').querySelector('[x-ref=searchInput]').focus()" class="nav-item">
        <i class="fas fa-search"></i>
        <span>Search</span>
    </button>
    <button @click="$el.closest('.pos-responsive-wrapper').__x.$data.showCart = true" class="nav-item">
        <i class="fas fa-shopping-cart"></i>
        <span>Cart</span>
        <span x-show="$store.transactions.cartCount > 0" class="cart-badge-nav" x-text="$store.transactions.cartCount"></span>
    </button>
    <a href="{{ route('kasir.management') }}" class="nav-item">
        <i class="fas fa-history"></i>
        <span>Riwayat</span>
    </a>
    <button @click="$el.closest('.pos-responsive-wrapper').__x.$data.openCheckout()"
            :disabled="$store.transactions.cartCount === 0"
            class="nav-item">
        <i class="fas fa-credit-card"></i>
        <span>Checkout</span>
    </button>
</nav>

<!-- MOBILE: Full Screen Cart Modal -->
<div x-show="showCart"
     x-cloak
     x-transition
     @click.self="showCart = false"
     class="cart-modal-fullscreen md:hidden lg:hidden">

    <div class="cart-modal-content" @click.stop>
        <!-- Swipe Handle -->
        <div class="swipe-handle"></div>

        <!-- Header -->
        <div class="cart-header">
            <h2 class="text-lg font-bold">
                <span x-text="$store.transactions.activeTab?.name || 'Keranjang'"></span>
                (<span x-text="$store.transactions.cartCount"></span> item)
            </h2>
            <button @click="showCart = false" class="close-btn">×</button>
        </div>

        <!-- Items (Alpine-driven) -->
        <div class="cart-items-mobile">
            <template x-if="$store.transactions.cartItems.length === 0">
                <div class="empty-state">
                    <i class="fas fa-shopping-basket"></i>
                    <p>Keranjang Kosong</p>
                </div>
            </template>

            <template x-for="item in $store.transactions.cartItems" :key="item.key || item.id">
                <div class="cart-item-mobile" x-data="{ showCustomPrice: false, expandItem: false }">
                    <div style="display: flex; flex-direction: column; flex: 1; gap: 8px;">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <div class="item-info">
                                <h4 x-text="item.name"></h4>
                                <p class="item-price">
                                    Rp <span x-text="(item.price * item.qty).toLocaleString('id-ID')"></span>
                                </p>

                                <!-- Price Type Dropdown -->
                                <select @change="
                                    const priceType = $event.target.value;
                                    showCustomPrice = (priceType === 'custom');
                                    if (priceType !== 'custom') {
                                        const newPrice = $store.transactions.getTierPrice(item, priceType);
                                        $store.transactions.updateItemPrice(item.id, newPrice, priceType, $wire);
                                    } else {
                                        item.priceType = 'custom';
                                        item.rawCustomPrice = item.price;
                                        if ($wire) {
                                            $wire.call('setActiveTab', $store.transactions.activeTabIndex);
                                            const cartKey = `product_${item.id}`;
                                            $wire.call('updatePrice', cartKey, item.price);
                                        }
                                    }
                                "
                                        :value="item.priceType || 'retail'"
                                        class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:border-blue-500 mt-1"
                                        style="max-width: 140px;">
                                    <option value="retail">Retail</option>
                                    <option value="semi_grosir">Semi Grosir</option>
                                    <option value="grosir">Grosir</option>
                                    <option value="custom">Custom</option>
                                </select>

                                <!-- Custom Price Input -->
                                <div x-show="showCustomPrice || item.priceType === 'custom'"
                                     x-transition
                                     class="mt-1">
                                    <input type="number"
                                           :value="item.rawCustomPrice ?? item.price"
                                           @input="$store.transactions.updateCustomPrice(item.id, $event.target.value, $wire)"
                                           placeholder="Harga custom"
                                           class="text-xs border border-blue-300 rounded px-2 py-1 w-full focus:outline-none focus:border-blue-500"
                                           min="0"
                                           step="100">
                                </div>
                            </div>

                            <button @click="$store.transactions.removeProduct(item.id, $wire)" class="remove-btn-mobile">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>

                        <!-- Large Qty Controls -->
                        <div class="qty-controls-large" style="justify-content: center;">
                            <button @click="$store.transactions.updateQty(item.id, item.qty - 1, $wire)"
                                    :disabled="item.qty <= 1"
                                    class="qty-btn-large">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="qty-display" x-text="item.qty"></span>
                            <button @click="$store.transactions.updateQty(item.id, item.qty + 1, $wire)"
                                    class="qty-btn-large">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Total -->
        <div class="cart-total-mobile">
            <span>Total</span>
            <span class="total-amount">Rp <span x-text="$store.transactions.cartTotal.toLocaleString('id-ID')"></span></span>
        </div>

        <!-- Checkout Button (Sticky) -->
        <button @click="$el.closest('.pos-responsive-wrapper').__x.$data.openCheckout()"
                :disabled="$store.transactions.cartCount === 0"
                class="checkout-btn-large"
                :class="{ 'opacity-50': $store.transactions.cartCount === 0 }">
            <i class="fas fa-credit-card mr-2"></i>
            Checkout - Rp <span x-text="$store.transactions.cartTotal.toLocaleString('id-ID')"></span>
        </button>
    </div>
</div>

<!-- Checkout Modal -->
@if($showCheckoutModal)
<div class="loading-overlay" wire:click.self="closeCheckout">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800">
                <i class="fas fa-credit-card text-blue-600 mr-2"></i>
                Pembayaran
            </h3>
            <button wire:click="closeCheckout" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Payment Method -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
            <div class="grid grid-cols-2 gap-2">
                <button wire:click="$set('paymentMethod', 'cash')"
                        class="p-3 border-2 rounded-lg text-center {{ $paymentMethod == 'cash' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                    <i class="fas fa-money-bill-wave text-2xl mb-1 {{ $paymentMethod == 'cash' ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    <p class="text-sm font-medium">Tunai</p>
                </button>
                <button wire:click="$set('paymentMethod', 'transfer')"
                        class="p-3 border-2 rounded-lg text-center {{ $paymentMethod == 'transfer' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                    <i class="fas fa-exchange-alt text-2xl mb-1 {{ $paymentMethod == 'transfer' ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    <p class="text-sm font-medium">Transfer</p>
                </button>
                <button wire:click="$set('paymentMethod', 'edc')"
                        class="p-3 border-2 rounded-lg text-center {{ $paymentMethod == 'edc' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                    <i class="fas fa-credit-card text-2xl mb-1 {{ $paymentMethod == 'edc' ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    <p class="text-sm font-medium">EDC/Kartu</p>
                </button>
                <button wire:click="$set('paymentMethod', 'qr')"
                        class="p-3 border-2 rounded-lg text-center {{ $paymentMethod == 'qr' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                    <i class="fas fa-qrcode text-2xl mb-1 {{ $paymentMethod == 'qr' ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    <p class="text-sm font-medium">QRIS</p>
                </button>
            </div>
        </div>

        <!-- Amount Paid -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Dibayar</label>
            <input type="number"
                   wire:model.live="amountPaid"
                   class="input-field"
                   min="0"
                   step="1000">
        </div>

        <!-- Total & Change -->
        <div class="bg-blue-50 rounded-lg p-4 mb-4">
            <div class="flex justify-between mb-2">
                <span class="text-gray-700">Total Belanja</span>
                <span class="font-bold text-gray-900">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-700">Kembalian</span>
                <span class="font-bold text-{{ $change >= 0 ? 'green' : 'red' }}-600">
                    Rp {{ number_format($change, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3">
            <button wire:click="closeCheckout" class="btn-secondary flex-1">
                Batal
            </button>
            <button wire:click="processCheckout" class="btn-primary flex-1">
                <i class="fas fa-check-circle"></i>
                Bayar
            </button>
        </div>
    </div>
</div>
@endif

<!-- Receipt Modal -->
@if($lastSale)
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60]"
     x-data="{ showReceiptModal: @entangle('showReceiptModal') }"
     x-show="showReceiptModal"
     x-cloak
     style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Transaksi Berhasil</h3>
                    <p class="text-sm text-gray-600">{{ $lastSale->sale_number }}</p>
                </div>
            </div>
            <button wire:click="$set('showReceiptModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Transaction Summary -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-4 mb-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600 font-medium">Total Belanja</span>
                <span class="text-2xl font-bold text-blue-600">
                    Rp {{ number_format($lastSale->final_total, 0, ',', '.') }}
                </span>
            </div>
            <div class="flex justify-between text-sm text-gray-600">
                <span>{{ $lastSale->saleItems->count() }} item</span>
                <span>{{ $lastSale->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-2 gap-2 mb-3">
            <button wire:click="printReceiptThermal({{ $lastSale->id }})"
                    class="p-3 border-2 border-blue-200 rounded-lg text-center hover:border-blue-500 hover:bg-blue-50 transition-all">
                <i class="fas fa-print text-2xl mb-1 text-blue-600"></i>
                <p class="text-xs font-medium text-gray-700">Thermal Print</p>
            </button>
            <button wire:click="exportReceiptPNG({{ $lastSale->id }})"
                    class="p-3 border-2 border-green-200 rounded-lg text-center hover:border-green-500 hover:bg-green-50 transition-all">
                <i class="fas fa-image text-2xl mb-1 text-green-600"></i>
                <p class="text-xs font-medium text-gray-700">Export PNG</p>
            </button>
            <button wire:click="exportReceiptPDFThermal({{ $lastSale->id }})"
                    class="p-3 border-2 border-purple-200 rounded-lg text-center hover:border-purple-500 hover:bg-purple-50 transition-all">
                <i class="fas fa-file-pdf text-2xl mb-1 text-purple-600"></i>
                <p class="text-xs font-medium text-gray-700">PDF Thermal</p>
            </button>
            <button wire:click="exportInvoiceA4({{ $lastSale->id }})"
                    class="p-3 border-2 border-orange-200 rounded-lg text-center hover:border-orange-500 hover:bg-orange-50 transition-all">
                <i class="fas fa-file-invoice text-2xl mb-1 text-orange-600"></i>
                <p class="text-xs font-medium text-gray-700">Invoice A4</p>
            </button>
        </div>

        <!-- History Shortcut -->
        <button wire:click="goToSalesHistory"
                class="w-full btn-primary py-3 mb-2 flex items-center justify-center gap-2">
            <i class="fas fa-history"></i>
            Lihat Riwayat Kasir
        </button>

        <!-- Close Button -->
        <button wire:click="$set('showReceiptModal', false)"
                class="w-full btn-secondary py-3">
            <i class="fas fa-times mr-2"></i>
            Tutup
        </button>
    </div>
</div>
@endif

<!-- Hidden Print Receipt Area (Thermal Style - 80mm) -->
@if($lastSale)
    @php
        $itemsPerPage = 16;
        $itemChunks = $lastSale->saleItems->chunk($itemsPerPage);
        if ($itemChunks->isEmpty()) {
            $itemChunks = collect([$lastSale->saleItems]);
        }
        $totalPages = $itemChunks->count();
    @endphp
    <div id="print-receipt-area" class="hidden">
        <div class="receipt-paper">
            @foreach($itemChunks as $pageIndex => $chunk)
                <div class="receipt-page">
                    <div class="text-center mb-4">
                        <h2 class="text-xl font-bold">{{ config('app.name', 'TOKO') }}</h2>
                        <p class="text-sm">
                            Struk Pembayaran
                            @if($totalPages > 1)
                                &middot; Hal {{ $pageIndex + 1 }}/{{ $totalPages }}
                            @endif
                        </p>
                        <p class="text-xs">{{ $lastSale->created_at->format('d/m/Y H:i:s') }}</p>
                    </div>

                    @if($pageIndex === 0)
                        <div class="border-t border-b border-dashed py-2 mb-2">
                            <table class="w-full text-sm">
                                <tr>
                                    <td>No. Transaksi</td>
                                    <td class="text-right">{{ $lastSale->sale_number }}</td>
                                </tr>
                                <tr>
                                    <td>Kasir</td>
                                    <td class="text-right">{{ $lastSale->cashier->name ?? 'System' }}</td>
                                </tr>
                                @if($lastSale->customer_name)
                                    <tr>
                                        <td>Pelanggan</td>
                                        <td class="text-right">{{ $lastSale->customer_name }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    @else
                        <div class="border-t border-b border-dashed py-2 mb-2 text-xs text-center text-gray-500">
                            Halaman {{ $pageIndex + 1 }} / {{ $totalPages }}
                        </div>
                    @endif

                    <div class="mb-2 space-y-1">
                        @forelse($chunk as $item)
                            <div>
                                <div class="font-medium">{{ optional($item->product)->name ?? ($item->custom_item_name ?? 'Item') }}</div>
                                <div class="flex justify-between text-sm">
                                    <span>{{ number_format($item->qty) }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                    <span class="font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-center text-gray-500">Tidak ada item</p>
                        @endforelse
                    </div>

                    @if($pageIndex === $totalPages - 1)
                        <div class="border-t border-dashed pt-2 mt-3">
                            <table class="w-full text-sm">
                                <tr>
                                    <td>Subtotal</td>
                                    <td class="text-right">Rp {{ number_format($lastSale->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @if($lastSale->discount_total > 0)
                                    <tr>
                                        <td>Diskon</td>
                                        <td class="text-right text-red-600">-Rp {{ number_format($lastSale->discount_total, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                                <tr class="font-bold text-base">
                                    <td>TOTAL</td>
                                    <td class="text-right">Rp {{ number_format($lastSale->final_total, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td>Bayar ({{ ucfirst($lastSale->payment_method) }})</td>
                                    <td class="text-right">Rp {{ number_format(($lastSale->cash_amount ?? 0) + ($lastSale->qr_amount ?? 0) + ($lastSale->edc_amount ?? 0), 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td>Kembalian</td>
                                    <td class="text-right">Rp {{ number_format($lastSale->change_amount, 0, ',', '.') }}</td>
                                </tr>
                            </table>

                            @if($lastSale->notes)
                                <div class="text-xs mt-2 border-t border-dashed pt-2">
                                    <strong>Catatan:</strong> {{ $lastSale->notes }}
                                </div>
                            @endif

                            <div class="text-center text-xs mt-4">
                                <p>Terima Kasih</p>
                                <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif


<!-- Receipt Print Styles -->
<style>
/* Hide everything except receipt when printing */
@media print {
    body * {
        visibility: hidden;
    }
    #print-receipt-area,
    #print-receipt-area * {
        visibility: visible;
    }
    #print-receipt-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        display: block !important;
    }

    /* Remove browser header/footer */
    @page {
        margin: 0;
        size: 80mm auto;
    }

    .receipt-paper {
        width: 80mm;
        padding: 10mm 8mm;
        margin: 0 auto;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        line-height: 1.4;
        page-break-inside: avoid;
        page-break-before: auto;
        page-break-after: auto;
    }

    .receipt-page {
        padding: 0;
        box-sizing: border-box;
        page-break-inside: avoid;
        border: none;
        border-radius: 0;
        background: transparent;
    }

    .receipt-page:last-child {
        page-break-after: auto;
        break-after: auto;
    }
}

/* Screen styling for receipt */
.receipt-paper {
    font-family: 'Courier New', monospace;
    max-width: 80mm;
    padding: 12mm 10mm;
    margin: 0 auto;
    background: white;
}

.receipt-page {
    padding: 0;
    border: none;
    border-radius: 0;
    background: transparent;
}
</style>

<!-- External Libraries for Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- Print & Export Receipt Scripts -->
<script>
document.addEventListener('livewire:init', () => {
    const CART_CACHE_KEY = 'pos_cart_cache';
    const MULTI_TAB_KEY = 'pos_multitab';
    const componentId = '<?php echo e($_instance->getId()); ?>';

    const getComponent = () => window.Livewire && window.Livewire.find(componentId);

    const safeParseJSON = (raw) => {
        if (!raw) return null;
        try {
            return JSON.parse(raw);
        } catch (error) {
            console.warn('POS cache parse error:', error);
            return null;
        }
    };

    const normalizeNumber = (value, fallback = 0) => {
        const numeric = Number(value);
        return Number.isFinite(numeric) ? numeric : fallback;
    };

    const serverCartToTabs = (cartData) => {
        if (!cartData || typeof cartData !== 'object' || !cartData.carts) {
            return null;
        }

        const carts = cartData.carts;
        const tabs = Object.values(carts).map((cart) => {
            const items = Object.entries(cart.cart || {}).map(([key, item]) => {
                const quantity = normalizeNumber(item.quantity, 0);
                const price = normalizeNumber(item.price, 0);
                return {
                    id: item.product_id ?? null,
                    key,
                    name: item.name ?? 'Item',
                    price,
                    basePrice: normalizeNumber(item.base_price ?? price, price),
                    priceType: item.pricing_tier ?? 'retail',
                    qty: quantity,
                    isCustom: !!item.is_custom,
                    tierPrices: {
                        retail: price,
                        semi_grosir: price,
                        grosir: price,
                    },
                };
            });

            return {
                id: cart.id ?? null,
                name: cart.name ?? `Transaksi ${cart.id ?? ''}`.trim(),
                items,
                customer: {
                    name: cart.customerName ?? '',
                    phone: cart.customerPhone ?? '',
                },
                paymentMethod: cart.paymentMethod ?? 'cash',
                amountPaid: normalizeNumber(cart.amountPaid ?? cart.payment_amount, 0),
                notes: cart.notes ?? '',
                paymentNotes: cart.paymentNotes ?? '',
            };
        });

        const activeTabId = cartData.activeTabId ?? (tabs[0]?.id ?? 1);
        let activeIndex = tabs.findIndex((tab) => tab.id === activeTabId);
        if (activeIndex < 0) activeIndex = 0;

        const nextId = tabs.reduce((max, tab) => {
            const id = tab.id ?? 0;
            return id > max ? id : max;
        }, 0) + 1;

        return {
            tabs,
            activeTabIndex: activeIndex,
            nextId,
        };
    };

    const tabsStateToServerCart = (state) => {
        if (!state || !Array.isArray(state.tabs)) {
            return null;
        }

        const carts = {};

        state.tabs.forEach((tab, index) => {
            const cartId = tab.id ?? index + 1;
            const items = tab.items ?? [];
            const cartItems = {};

            items.forEach((item, itemIndex) => {
                const quantity = normalizeNumber(item.qty, 0);
                const price = normalizeNumber(item.price, 0);
                const keyBase = item.key ?? (item.id ? `product_${item.id}` : `custom_${cartId}_${itemIndex}`);

                cartItems[keyBase] = {
                    product_id: item.id ?? null,
                    name: item.name ?? 'Item',
                    price,
                    base_price: normalizeNumber(item.basePrice ?? item.price, price),
                    quantity,
                    base_qty: quantity,
                    pricing_tier: item.priceType ?? 'retail',
                    available_stock: item.available_stock ?? null,
                    warehouse_id: item.warehouse_id ?? null,
                    sku: item.sku ?? '',
                    barcode: item.barcode ?? '',
                    base_cost: normalizeNumber(item.base_cost, 0),
                    selected_unit_id: item.selected_unit_id ?? null,
                    selected_unit_to_base_qty: normalizeNumber(item.selected_unit_to_base_qty, 1) || 1,
                    unit_label: item.unit_label ?? '',
                    is_custom: !item.id,
                };
            });

            carts[cartId] = {
                id: cartId,
                name: tab.name ?? `Transaksi ${cartId}`,
                cart: cartItems,
                supplierName: tab.supplierName ?? '',
                supplierPhone: tab.supplierPhone ?? '',
                customerName: tab.customer?.name ?? '',
                customerPhone: tab.customer?.phone ?? '',
                paymentMethod: tab.paymentMethod ?? 'cash',
                amountPaid: normalizeNumber(tab.amountPaid, 0),
                notes: tab.notes ?? '',
                paymentNotes: tab.paymentNotes ?? '',
            };
        });

        const activeIndex = typeof state.activeTabIndex === 'number' ? state.activeTabIndex : 0;
        const activeTab = state.tabs[activeIndex] ?? state.tabs[0];
        const activeTabId = activeTab?.id ?? (activeIndex + 1);

        return {
            carts,
            activeTabId,
        };
    };

    const updateAlpineStore = (multiTabState) => {
        if (!multiTabState || !window.Alpine || !Alpine.store('transactions')) return;

        const store = Alpine.store('transactions');
        if (!Array.isArray(store.tabs)) {
            store.tabs = [];
        }

        const existingTabIndex = new Map();
        store.tabs.forEach((tab, index) => {
            existingTabIndex.set(tab.id ?? (index + 1), index);
        });

        const mergedTabs = [];

        (multiTabState.tabs ?? []).forEach((incomingTab, index) => {
            const tabId = incomingTab.id ?? (index + 1);
            const existingIndex = existingTabIndex.get(tabId);

            if (existingIndex != null) {
                const existingTab = store.tabs[existingIndex] ?? {};
                existingTab.id = tabId;
                existingTab.name = incomingTab.name ?? existingTab.name ?? `Transaksi ${tabId}`;
                existingTab.customer = incomingTab.customer ?? existingTab.customer ?? { name: '', phone: '' };
                existingTab.paymentMethod = incomingTab.paymentMethod ?? existingTab.paymentMethod ?? 'cash';
                existingTab.amountPaid = incomingTab.amountPaid ?? existingTab.amountPaid ?? 0;
                existingTab.notes = incomingTab.notes ?? existingTab.notes ?? '';
                existingTab.paymentNotes = incomingTab.paymentNotes ?? existingTab.paymentNotes ?? '';

                const existingItems = Array.isArray(existingTab.items) ? existingTab.items : [];
                const existingItemIndex = new Map();
                existingItems.forEach((item, idx) => {
                    const key = item.key ?? item.id ?? idx;
                    existingItemIndex.set(key, idx);
                });

                const mergedItems = [];
                (incomingTab.items ?? []).forEach((incomingItem, itemIdx) => {
                    const key = incomingItem.key ?? incomingItem.id ?? `${incomingItem.name}-${itemIdx}`;
                    if (existingItemIndex.has(key)) {
                        const existingItem = existingItems[existingItemIndex.get(key)];
                        Object.assign(existingItem, incomingItem);
                        mergedItems.push(existingItem);
                    } else {
                        mergedItems.push(incomingItem);
                    }
                });

                existingTab.items = mergedItems;
                mergedTabs.push(existingTab);
            } else {
                mergedTabs.push({
                    ...incomingTab,
                    id: tabId,
                });
            }
        });

        store.tabs = mergedTabs;
        store.activeTabIndex = Math.min(
            typeof multiTabState.activeTabIndex === 'number' ? multiTabState.activeTabIndex : 0,
            Math.max(mergedTabs.length - 1, 0)
        );
        if (store.activeTabIndex < 0) {
            store.activeTabIndex = 0;
        }
        store.nextId = multiTabState.nextId ?? (mergedTabs.length + 1);

        if (typeof store.save === 'function') {
            store.save();
        }
    };

    const persistMultiTab = (multiTabState) => {
        if (!multiTabState) return;

        localStorage.setItem(MULTI_TAB_KEY, JSON.stringify({
            ...multiTabState,
            timestamp: Date.now(),
        }));
        updateAlpineStore(multiTabState);
    };

    Livewire.on('save-cart-to-cache', (payload = {}) => {
        const cartData = payload?.cartData ?? payload;
        if (!cartData || typeof cartData !== 'object') {
            return;
        }

        try {
            localStorage.setItem(CART_CACHE_KEY, JSON.stringify(cartData));
        } catch (error) {
            console.error('Failed to save POS cart cache:', error);
        }
    });

    Livewire.on('request-cart-restore', () => {
        const component = getComponent();
        if (!component) return;

        let cartData = safeParseJSON(localStorage.getItem(CART_CACHE_KEY));
        if (!cartData || !cartData.carts) {
            const multiTabState = safeParseJSON(localStorage.getItem(MULTI_TAB_KEY));
            cartData = tabsStateToServerCart(multiTabState);
        }

        if (cartData && cartData.carts) {
            component.call('restoreCartFromCache', cartData);
            localStorage.setItem(CART_CACHE_KEY, JSON.stringify(cartData));

            const multiTabState = serverCartToTabs(cartData);
            if (multiTabState) {
                persistMultiTab(multiTabState);
            }
        }
    });

    // Print Receipt - Thermal
    Livewire.on('print-receipt-thermal', () => {
        setTimeout(() => {
            window.print();
        }, 100);
    });

    // Export to PNG
    Livewire.on('export-receipt-png', () => {
        setTimeout(() => {
            exportReceiptToImage();
        }, 100);
    });

    // Export to PDF - Thermal
    Livewire.on('export-receipt-pdf-thermal', () => {
        setTimeout(() => {
            exportReceiptToPDF();
        }, 100);
    });

    // Export Invoice A4
    Livewire.on('export-invoice-a4', () => {
        setTimeout(() => {
            exportInvoiceA4();
        }, 100);
    });
});

// Helper: prepare receipt DOM for accurate capture (fix cropping)
function prepareReceiptForCapture(receiptElement, targetPixelWidth = 302) {
    const receiptPaper = receiptElement.querySelector('.receipt-paper');
    const prev = {
        display: receiptElement.style.display,
        position: receiptElement.style.position,
        left: receiptElement.style.left,
        width: receiptPaper ? receiptPaper.style.width : undefined,
        padding: receiptPaper ? receiptPaper.style.padding : undefined,
        boxSizing: receiptPaper ? receiptPaper.style.boxSizing : undefined,
        background: receiptPaper ? receiptPaper.style.backgroundColor : undefined,
        gap: receiptPaper ? receiptPaper.style.gap : undefined,
    };

    // Ensure element is rendered off-screen for capture
    receiptElement.style.display = 'block';
    receiptElement.style.position = 'absolute';
    receiptElement.style.left = '-9999px';

    if (receiptPaper) {
        receiptPaper.style.width = targetPixelWidth + 'px';
        receiptPaper.style.padding = '30px 25px';
        receiptPaper.style.boxSizing = 'border-box';
        receiptPaper.style.backgroundColor = '#ffffff';
        receiptPaper.style.gap = '0';
    }

    // Return cleanup function
    return () => {
        receiptElement.style.display = prev.display || 'none';
        receiptElement.style.position = prev.position || '';
        receiptElement.style.left = prev.left || '';
        if (receiptPaper) {
            receiptPaper.style.width = prev.width || '';
            receiptPaper.style.padding = prev.padding || '';
            receiptPaper.style.boxSizing = prev.boxSizing || '';
            receiptPaper.style.backgroundColor = prev.background || '';
            receiptPaper.style.gap = prev.gap || '';
        }
    };
}

function exportReceiptToImage() {
    const receiptElement = document.getElementById('print-receipt-area');
    if (!receiptElement) {
        alert('Struk tidak ditemukan');
        return;
    }

    const cleanup = prepareReceiptForCapture(receiptElement, 302);

    html2canvas(receiptElement, {
        scale: 2,
        backgroundColor: '#ffffff',
        logging: false,
        scrollX: 0,
        scrollY: 0,
    }).then(canvas => {
        cleanup();

        // Download as PNG
        const link = document.createElement('a');
        const saleNumber = receiptElement.querySelector('.receipt-paper h2')?.nextElementSibling?.nextElementSibling?.textContent || 'struk';
        link.download = `Struk-${saleNumber.trim()}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    }).catch(error => {
        cleanup();
        console.error('Error exporting to PNG:', error);
        alert('Gagal export ke PNG');
    });
}

function exportReceiptToPDF() {
    const receiptElement = document.getElementById('print-receipt-area');
    if (!receiptElement) {
        alert('Struk tidak ditemukan');
        return;
    }

    const cleanup = prepareReceiptForCapture(receiptElement, 302);

    html2canvas(receiptElement, {
        scale: 2,
        backgroundColor: '#ffffff',
        logging: false,
        scrollX: 0,
        scrollY: 0,
    }).then(canvas => {
        cleanup();

        const imgData = canvas.toDataURL('image/png');
        const { jsPDF } = window.jspdf;

        const pageWidth = 80;
        const margin = 2;
        const imageWidth = pageWidth - margin * 2;
        const imageHeight = canvas.height * imageWidth / canvas.width;
        const pageHeight = imageHeight + margin * 2;

        // Create PDF with thermal receipt size (80mm width) and soft margins
        const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: [pageWidth, pageHeight]
        });

        pdf.addImage(imgData, 'PNG', margin, margin, imageWidth, imageHeight);

        const saleNumber = receiptElement.querySelector('.receipt-paper h2')?.nextElementSibling?.nextElementSibling?.textContent || 'struk';
        pdf.save(`Struk-${saleNumber.trim()}.pdf`);
    }).catch(error => {
        cleanup();
        console.error('Error exporting to PDF:', error);
        alert('Gagal export ke PDF');
    });
}

function exportInvoiceA4() {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4'
    });

    // Get receipt data from DOM
    const receiptElement = document.getElementById('print-receipt-area');
    if (!receiptElement) {
        alert('Data transaksi tidak ditemukan');
        return;
    }

    // Extract data from receipt
    const storeName = receiptElement.querySelector('.receipt-paper h2')?.textContent || 'TOKO';
    const dateText = receiptElement.querySelector('.receipt-paper .text-xs')?.textContent || '';
    const saleNumber = receiptElement.querySelectorAll('.receipt-paper table tr')?.[0]?.querySelectorAll('td')?.[1]?.textContent || '';
    const cashier = receiptElement.querySelectorAll('.receipt-paper table tr')?.[1]?.querySelectorAll('td')?.[1]?.textContent || '';

    // PDF Header
    pdf.setFontSize(20);
    pdf.setFont(undefined, 'bold');
    pdf.text(storeName, 105, 20, { align: 'center' });

    pdf.setFontSize(16);
    pdf.text('INVOICE', 105, 30, { align: 'center' });

    pdf.setFontSize(10);
    pdf.setFont(undefined, 'normal');
    pdf.text(dateText, 105, 37, { align: 'center' });

    // Transaction Info
    let yPos = 50;
    pdf.setFontSize(11);
    pdf.setFont(undefined, 'bold');
    pdf.text('INFORMASI TRANSAKSI', 15, yPos);

    yPos += 7;
    pdf.setFont(undefined, 'normal');
    pdf.setFontSize(10);
    pdf.text(`No. Transaksi: ${saleNumber}`, 15, yPos);

    yPos += 6;
    pdf.text(`Kasir: ${cashier}`, 15, yPos);

    // Items Table Header
    yPos += 12;
    pdf.setFont(undefined, 'bold');
    pdf.setFillColor(59, 130, 246);
    pdf.rect(15, yPos - 5, 180, 8, 'F');
    pdf.setTextColor(255, 255, 255);
    pdf.text('Produk', 17, yPos);
    pdf.text('Qty', 130, yPos);
    pdf.text('Harga', 150, yPos);
    pdf.text('Subtotal', 175, yPos, { align: 'right' });

    // Items
    yPos += 8;
    pdf.setTextColor(0, 0, 0);
    pdf.setFont(undefined, 'normal');

    const items = receiptElement.querySelectorAll('.receipt-paper .mb-2 > div.mb-1');
    items.forEach((item, index) => {
        const itemName = item.querySelector('.font-medium')?.textContent || '';
        const itemDetails = item.querySelector('.flex.justify-between')?.textContent || '';

        // Parse qty, price, and subtotal from text
        const match = itemDetails.match(/(\d+(?:,\d{3})*)\s*x\s*Rp\s*([\d,.]+)\s*Rp\s*([\d,.]+)/);

        if (match) {
            const qty = match[1];
            const price = match[2];
            const subtotal = match[3];

            pdf.text(itemName.substring(0, 40), 17, yPos);
            pdf.text(qty, 130, yPos);
            pdf.text(`Rp ${price}`, 150, yPos);
            pdf.text(`Rp ${subtotal}`, 193, yPos, { align: 'right' });

            yPos += 6;

            // Add new page if needed
            if (yPos > 270) {
                pdf.addPage();
                yPos = 20;
            }
        }
    });

    // Summary
    yPos += 5;
    pdf.setDrawColor(200, 200, 200);
    pdf.line(15, yPos, 195, yPos);

    yPos += 8;
    const summaryRows = receiptElement.querySelectorAll('.border-t.border-dashed table tr');
    summaryRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length === 2) {
            const label = cells[0].textContent.trim();
            const value = cells[1].textContent.trim();

            if (label.includes('TOTAL')) {
                pdf.setFont(undefined, 'bold');
                pdf.setFontSize(12);
            } else {
                pdf.setFont(undefined, 'normal');
                pdf.setFontSize(10);
            }

            pdf.text(label, 17, yPos);
            pdf.text(value, 193, yPos, { align: 'right' });
            yPos += 7;
        }
    });

    // Footer
    yPos += 10;
    pdf.setFontSize(9);
    pdf.setFont(undefined, 'italic');
    pdf.text('Terima kasih atas pembelian Anda', 105, yPos, { align: 'center' });
    pdf.text('Barang yang sudah dibeli tidak dapat dikembalikan', 105, yPos + 5, { align: 'center' });

    // Save PDF
    pdf.save(`Invoice-${saleNumber.trim()}.pdf`);
}
</script>

</div>
