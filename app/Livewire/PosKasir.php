<?php

namespace App\Livewire;

use App\Product;
use App\ProductWarehouseStock;
use App\Sale;
use App\SaleItem;
use App\Shared\Traits\WithAlerts;
use App\StockMovement;
use App\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PosKasir extends Component
{
    use WithAlerts;

    public $barcode = '';

    public $productSearch = '';
    public $mobileMenuOpen = false; // Kontrol tampilan bottom sheet mobile
    public $transactionFullWidth = false; // Mode desktop: panel transaksi memenuhi lebar penuh

    // Mobile UI state: show/hide floating bottom sheet menu

    public function resetProductSearch()
    {
        $this->productSearch = '';
    }

    public $cart = []; // Current active cart (reference to active tab's cart)

    public $carts = []; // Multiple carts structure

    public $activeTabId = 1; // ID of the currently active tab

    public $nextTabId = 2; // Next available tab ID
    
    public $tabName = ''; // For tab name editing

    public $warehouses = [];

    public $warehouseId = '';

    public $selectedWarehouse = '';

    public $supplierName = '';

    public $supplierPhone = '';

    public $paymentMethod = 'cash';

    public $pricingTier = 'retail'; // retail, semi_grosir, grosir, custom

    public $amountPaid = 0;
    public $paymentStatus = 'PAID';

    public $discount = 0;

    public $discountType = 'amount'; // amount or percentage

    public $notes = '';

    public $paymentNotes = '';

    // Computed properties (will be calculated for active tab)
    public $subtotal = 0;

    public $discountAmount = 0;

    public $total = 0;

    public $change = 0;

    // UI State
    public $showCheckoutModal = false;

    public $showReceiptModal = false;

    public $showCustomItemModal = false;

    public $lastSale = null;

    // Custom Item Properties
    public $customItemName = '';

    public $customItemDescription = '';

    public $customItemPrice = 0;

    public $customItemQuantity = 1;

    // Optional customer fields (used in checkout modal)
    public $customerName = '';
    public $customerPhone = '';

    protected $rules = [
        'supplierName' => 'nullable|string|max:255',
        'supplierPhone' => 'nullable|string|max:20',
        'customerName' => 'nullable|string|max:255',
        'customerPhone' => 'nullable|string|max:20',
        'paymentMethod' => 'required|in:cash,transfer,edc,qr',
        'warehouseId' => 'required|exists:warehouses,id',
        'pricingTier' => 'required|in:retail,semi_grosir,grosir,custom',
        'paymentNotes' => 'nullable|string|max:255',
        'amountPaid' => 'required|numeric|min:0',
        'notes' => 'nullable|string|max:500',
        'customItemName' => 'required|string|max:255',
        'customItemDescription' => 'nullable|string|max:500',
        'customItemPrice' => 'required|numeric|min:0.01',
        'customItemQuantity' => 'required|integer|min:1',
    ];

    protected function getCheckoutRules()
    {
        return [
            'supplierName' => 'nullable|string|max:255',
            'supplierPhone' => 'nullable|string|max:20',
            'customerName' => 'nullable|string|max:255',
            'customerPhone' => 'nullable|string|max:20',
            'paymentMethod' => 'required|in:cash,transfer,edc,qr',
            'warehouseId' => 'required|exists:warehouses,id',
            'amountPaid' => 'required|numeric|min:0',
            'paymentStatus' => 'required|in:PAID,PARTIAL,UNPAID',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function mount(): void
    {
        if (! auth()->user()->hasPermission('pos.access')) {
            abort(403, 'You do not have permission to access POS system.');
        }

        // Ambil semua gudang terurut (default terlebih dahulu)
        $this->warehouses = Warehouse::ordered()->get();

        // Tentukan gudang default (Toko Utama)
        $defaultWarehouse = Warehouse::getDefault();
        if (! $defaultWarehouse) {
            // Fallback ke gudang bertipe store, jika tidak ada ambil gudang pertama
            $defaultWarehouse = Warehouse::where('type', 'store')->ordered()->first()
                ?? Warehouse::ordered()->first();
        }
        
        if (! $defaultWarehouse) {
            // Tidak ada gudang sama sekali
            session()->flash('error', 'Gudang belum dikonfigurasi. Tambahkan gudang terlebih dahulu.');
            $this->warehouseId = '';
            $this->selectedWarehouse = '';
        } else {
            $this->warehouseId = $defaultWarehouse->id;
            $this->selectedWarehouse = $defaultWarehouse->id;
        }

        // Initialize first tab
        $this->initializeTabs();
        $this->updateCurrentPropertiesFromTab();
        $this->calculateTotals();
        $this->refreshCartForWarehouse();
    }

    public function updatedBarcode()
    {
        if (! empty($this->barcode)) {
            // Clean and validate barcode format
            $this->barcode = $this->cleanBarcode($this->barcode);

            if ($this->isValidBarcodeFormat($this->barcode)) {
                $this->addProductByBarcode();
            } else {
                session()->flash('error', 'Format barcode tidak valid: "'.$this->barcode.'"');
                $this->barcode = '';
            }
        }
    }

    // Discount features removed from calculation; watchers no longer needed

    public function updatedAmountPaid()
    {
        $this->updateActiveTabFromCurrentProperties();
        $this->change = max(0, $this->amountPaid - $this->total);
    }

    public function updatedPaymentStatus()
    {
        // Adjust amountPaid to align with selected status for better UX
        if ($this->paymentStatus === 'UNPAID') {
            $this->amountPaid = 0;
        } elseif ($this->paymentStatus === 'PAID') {
            $this->amountPaid = max($this->amountPaid, $this->total);
        } elseif ($this->paymentStatus === 'PARTIAL') {
            // Ensure amountPaid is between 0 and total
            if ($this->total > 0 && ($this->amountPaid <= 0 || $this->amountPaid >= $this->total)) {
                $this->amountPaid = max(0.01, $this->total - 0.01);
            }
        }

        $this->change = max(0, $this->amountPaid - $this->total);
        $this->updateActiveTabFromCurrentProperties();
    }

    public function updatedSupplierName()
    {
        $this->updateActiveTabFromCurrentProperties();
    }

    public function updatedSupplierPhone()
    {
        $this->updateActiveTabFromCurrentProperties();
    }

    public function updatedPaymentMethod()
    {
        $this->updateActiveTabFromCurrentProperties();
    }

    public function updatedNotes()
    {
        $this->updateActiveTabFromCurrentProperties();
    }

    public function updatedPaymentNotes()
    {
        $this->updateActiveTabFromCurrentProperties();
    }

    private function enforceDefaultWarehouse(): void
    {
        $defaultWarehouse = Warehouse::getDefault();
        if (! $defaultWarehouse) {
            return;
        }

        $defaultId = $defaultWarehouse->id;
        $changed = false;

        if ($this->warehouseId !== $defaultId) {
            $this->warehouseId = $defaultId;
            $changed = true;
        }

        if ($this->selectedWarehouse !== $defaultId) {
            $this->selectedWarehouse = $defaultId;
            $changed = true;
        }

        if ($changed) {
            // Pastikan keranjang disegarkan untuk gudang default
            $this->refreshCartForWarehouse();
        }
    }

    public function updatedWarehouseId(): void
    {
        // Selalu pakai gudang default (Toko Utama)
        $this->enforceDefaultWarehouse();
    }

    public function updatedSelectedWarehouse(): void
    {
        // Selalu pakai gudang default (Toko Utama)
        $this->enforceDefaultWarehouse();
    }

    private function getAvailableStock(Product $product): int
    {
        if (! $this->warehouseId) {
            return (int) $product->current_stock;
        }

        return (int) (ProductWarehouseStock::query()
            ->where('product_id', $product->id)
            ->where('warehouse_id', $this->warehouseId)
            ->value('stock_on_hand') ?? 0);
    }

    private function refreshCartForWarehouse(): void
    {
        foreach ($this->carts as $tabId => &$tabData) {
            foreach ($tabData['cart'] as $cartKey => $item) {
                if (! isset($item['product_id'])) {
                    continue;
                }

                $product = Product::find($item['product_id']);

                if (! $product) {
                    unset($tabData['cart'][$cartKey]);
                    continue;
                }

                $availableStock = $this->getAvailableStock($product);

                if ($availableStock <= 0) {
                    unset($tabData['cart'][$cartKey]);
                    session()->flash('error', 'Stok di lokasi terpilih habis untuk produk "'.$product->name.'".');

                    continue;
                }

                $tabData['cart'][$cartKey]['available_stock'] = $availableStock;
                $tabData['cart'][$cartKey]['warehouse_id'] = $this->warehouseId;

                if ($tabData['cart'][$cartKey]['quantity'] > $availableStock) {
                    $tabData['cart'][$cartKey]['quantity'] = $availableStock;
                    session()->flash('error', 'Jumlah di keranjang dikurangi karena stok terbatas untuk produk "'.$product->name.'".');
                }
            }
        }

        // Update current cart from active tab
        $this->updateCurrentPropertiesFromTab();
        $this->calculateTotals();
    }

    public function addProductByBarcode()
    {
        if (empty($this->barcode)) {
            return;
        }

        $scannedCode = $this->barcode;

        $product = Product::where('status', 'active')
            ->whereNull('deleted_at')
            ->where('barcode', $scannedCode)
            ->whereHas('warehouseStocks', function ($q) {
                if ($this->warehouseId) {
                    $q->where('warehouse_id', $this->warehouseId)
                      ->where('stock_on_hand', '>', 0);
                }
            })
            ->first();

        if (! $product) {
            $product = Product::where('status', 'active')
                ->whereNull('deleted_at')
                ->where('sku', $scannedCode)
                ->whereHas('warehouseStocks', function ($q) {
                    if ($this->warehouseId) {
                        $q->where('warehouse_id', $this->warehouseId)
                          ->where('stock_on_hand', '>', 0);
                    }
                })
                ->first();
        }

        if (! $product) {
            session()->flash('error', 'Produk dengan barcode/SKU "'.$scannedCode.'" tidak ditemukan!');
            $this->barcode = '';

            return;
        }

        $availableStock = $this->getAvailableStock($product);

        if ($availableStock <= 0) {
            session()->flash('error', 'Stok produk "'.$product->name.'" di lokasi terpilih habis!');
            $this->barcode = '';

            return;
        }

        $this->addToCart($product->id, $product);
        $this->updateActiveTabFromCurrentProperties();
        $this->barcode = '';

        \App\Shared\Services\LoggerService::logUserAction(
            'barcode_scan',
            'product',
            $product->id,
            ['barcode' => $scannedCode, 'product_name' => $product->name]
        );
    }

    public function addToCart($productId, $product = null)
    {
        $product = $product ?? Product::where('status', 'active')
            ->whereNull('deleted_at')
            ->whereHas('warehouseStocks', function ($q) {
                if ($this->warehouseId) {
                    $q->where('warehouse_id', $this->warehouseId)
                      ->where('stock_on_hand', '>', 0);
                }
            })
            ->find($productId);

        if (! $product) {
            session()->flash('error', 'Produk tidak ditemukan atau tidak tersedia untuk dijual!');

            return;
        }

        $availableStock = $this->getAvailableStock($product);
        
        // Check if stock is 0 or less
        if ($availableStock <= 0) {
            session()->flash('error', 'Stok tidak tersedia untuk produk "'.$product->name.'"!');
            return;
        }
        
        $cartKey = 'product_'.$productId;

        if (isset($this->cart[$cartKey])) {
            if ($this->cart[$cartKey]['quantity'] >= $availableStock) {
                session()->flash('error', 'Stok tidak mencukupi untuk produk "'.$product->name.'"!');

                return;
            }

            $this->cart[$cartKey]['quantity']++;
        } else {
            $effectivePricingTier = $this->pricingTier === 'retail'
                ? $product->default_price_type
                : $this->pricingTier;

            $this->cart[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'price' => $this->getPriceByTier($product, $effectivePricingTier),
                'base_cost' => $product->base_cost,
                'quantity' => 1,
                'available_stock' => $availableStock,
                'pricing_tier' => $effectivePricingTier,
                'warehouse_id' => $this->warehouseId,
            ];
        }

        $this->cart[$cartKey]['available_stock'] = $availableStock;

        $this->updateActiveTabFromCurrentProperties();
        $this->calculateTotals();
        session()->flash('success', 'Produk "'.$product->name.'" ditambahkan ke keranjang!');
    }

    public function updateQuantity($cartKey, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($cartKey);

            return;
        }

        if (isset($this->cart[$cartKey])) {
            // Skip stock checks for custom items
            if (!isset($this->cart[$cartKey]['is_custom']) || !$this->cart[$cartKey]['is_custom']) {
                $availableStock = $this->cart[$cartKey]['available_stock'];
                if ($quantity > $availableStock) {
                    session()->flash('error', 'Stok tidak mencukupi! Stok tersedia: '.$availableStock);
                    return;
                }
            }

            $this->cart[$cartKey]['quantity'] = $quantity;
            $this->updateActiveTabFromCurrentProperties();
            $this->calculateTotals();
        }
    }

    public function updatePrice($cartKey, $price)
    {
        if (isset($this->cart[$cartKey])) {
            $baseCost = $this->cart[$cartKey]['base_cost'];
            $minPrice = $baseCost * 1.1; // Minimum 10% margin

            if ($price < $minPrice) {
                session()->flash('error', 'Harga tidak boleh kurang dari Rp '.number_format($minPrice, 0, ',', '.').' (margin minimum 10%)');

                return;
            }

            $this->cart[$cartKey]['price'] = $price;
            $this->cart[$cartKey]['pricing_tier'] = 'custom';
            $this->updateActiveTabFromCurrentProperties();
            $this->calculateTotals();
        }
    }

    public function updateItemPriceType($cartKey, $priceType)
    {
        if (isset($this->cart[$cartKey])) {
            $product = Product::find($this->cart[$cartKey]['product_id']);
            if ($product) {
                $this->cart[$cartKey]['price'] = $this->getPriceByTier($product, $priceType);
                $this->cart[$cartKey]['pricing_tier'] = $priceType;
                $this->updateActiveTabFromCurrentProperties();
                $this->calculateTotals();

                $priceTypeName = Product::getPriceTypes()[$priceType];
                session()->flash('success', "Harga produk {$product->name} diubah ke {$priceTypeName}!");
            }
        }
    }

    public function bulkSetCartPriceType($priceType)
    {
        $updatedCount = 0;
        foreach ($this->cart as $cartKey => $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $this->cart[$cartKey]['price'] = $this->getPriceByTier($product, $priceType);
                $this->cart[$cartKey]['pricing_tier'] = $priceType;
                $updatedCount++;
            }
        }

        if ($updatedCount > 0) {
            $this->updateActiveTabFromCurrentProperties();
            $this->calculateTotals();
            $priceTypeName = Product::getPriceTypes()[$priceType];
            session()->flash('success', "{$updatedCount} produk di keranjang diubah ke jenis harga {$priceTypeName}!");
        }
    }

    // Toggle desktop full-width mode for transaction panel
    public function toggleTransactionFullWidth(): void
    {
        $this->transactionFullWidth = ! $this->transactionFullWidth;
    }

    // Wrapper untuk mengubah semua jenis harga dari dropdown Blade
    public function updateAllItemsPriceType($priceType): void
    {
        $validTypes = array_keys(Product::getPriceTypes());
        if (! in_array($priceType, $validTypes, true)) {
            session()->flash('error', 'Jenis harga tidak valid.');
            return;
        }
        $this->bulkSetCartPriceType($priceType);
    }

    public function removeFromCart($cartKey)
    {
        if (isset($this->cart[$cartKey])) {
            unset($this->cart[$cartKey]);
            $this->updateActiveTabFromCurrentProperties();
            $this->calculateTotals();
            session()->flash('success', 'Produk dihapus dari keranjang!');
        }
    }

    public function confirmClearCart()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang sudah kosong!');

            return;
        }

        $this->showConfirm(
            'Konfirmasi Kosongkan Keranjang',
            'Apakah Anda yakin ingin mengosongkan semua item di keranjang?',
            'clearCart',
            []
        );
    }

    public function clearCart($params = [])
    {
        $this->cart = [];
        $this->updateActiveTabFromCurrentProperties();
        $this->calculateTotals();
        session()->flash('success', 'Keranjang dikosongkan!');
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;

        foreach ($this->cart as $item) {
            $this->subtotal += $item['price'] * $item['quantity'];
        }

        // Discounts removed: total equals subtotal
        $this->discountAmount = 0;
        $this->total = max(0, $this->subtotal);
        $this->change = max(0, $this->amountPaid - $this->total);
    }

    public function openCheckout()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang masih kosong!');

            return;
        }

        $this->updateActiveTabFromCurrentProperties();
        $this->amountPaid = $this->total;
        $this->paymentStatus = 'PAID';
        $this->showCheckoutModal = true;
    }

    public function closeCheckout()
    {
        $this->updateActiveTabFromCurrentProperties();
        $this->showCheckoutModal = false;
    }

    public function processCheckout()
    {
        // Check if user has permission to process sales
        if (! auth()->user()->hasPermission('pos.create_sales')) {
            session()->flash('error', 'Anda tidak memiliki izin untuk memproses penjualan!');

            return;
        }

        $this->validate($this->getCheckoutRules());

        // Allow partial/unpaid payments; status will be determined below

        try {
            DB::beginTransaction();
            \Log::info('POS Checkout: Transaction started');

            // Use selected payment status from UI (validated via getCheckoutRules)
            $status = $this->paymentStatus;

            // Create sale record
            $saleData = [
                'sale_number' => 'POS-'.date('Ymd').'-'.str_pad(Sale::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
                'cashier_id' => Auth::id(),
                'subtotal' => $this->subtotal,
                // Newly added columns in migrations
                'tax_amount' => 0,
                'total_amount' => $this->total,
                'payment_amount' => $this->amountPaid,
                'discount_total' => 0,
                'final_total' => $this->total,
                'payment_method' => $this->paymentMethod,
                'payment_notes' => $this->paymentNotes,
                // Persist payment status to the dedicated column to avoid enum truncation on `status`
                'payment_status' => $status,
                // Keep transactional status as 'PAID' (completed) for now; cancellations use 'CANCELLED'
                'status' => 'PAID',
                // Optional notes if column exists
                'notes' => $this->notes,
            ];
            
            \Log::info('POS Checkout: Creating sale with data', $saleData);
            $sale = Sale::create($saleData);
            \Log::info('POS Checkout: Sale created with ID: ' . $sale->id);

            $warehouse = Warehouse::find($this->warehouseId);

            if (! $warehouse) {
                throw new \RuntimeException('Gudang tidak ditemukan.');
            }
            
            \Log::info('POS Checkout: Warehouse found: ' . $warehouse->name);

            foreach ($this->cart as $item) {
                \Log::info('POS Checkout: Processing cart item', $item);
                
                $saleItemData = [
                    'sale_id' => $sale->id,
                    'product_id' => $item['is_custom'] ?? false ? null : $item['product_id'],
                    'qty' => $item['quantity'],
                    'unit_price' => $item['price'],
                    // Persist the item's pricing tier from the cart (custom items will carry 'custom')
                    'price_tier' => $item['pricing_tier'] ?? ($this->pricingTier ?? 'retail'),
                    'margin_pct_at_sale' => 0.00,
                    'below_margin_flag' => false,
                    'is_custom' => $item['is_custom'] ?? false,
                    'custom_item_name' => $item['is_custom'] ?? false ? $item['name'] : null,
                    'custom_item_description' => $item['is_custom'] ?? false ? ($item['description'] ?? null) : null,
                ];
                
                \Log::info('POS Checkout: Creating sale item with data', $saleItemData);
                $saleItem = SaleItem::create($saleItemData);
                \Log::info('POS Checkout: Sale item created with ID: ' . $saleItem->id);

                if (! isset($item['is_custom']) || ! $item['is_custom']) {
                    $product = Product::find($item['product_id']);

                    if (! $product) {
                        throw new \RuntimeException('Produk tidak ditemukan saat memproses penjualan.');
                    }

                    $stockRow = ProductWarehouseStock::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'warehouse_id' => $warehouse->id,
                        ],
                        [
                            'stock_on_hand' => 0,
                            'reserved_stock' => 0,
                            'safety_stock' => 0,
                        ]
                    );

                    if ($stockRow->stock_on_hand < $item['quantity']) {
                        throw new \RuntimeException('Stok tidak mencukupi untuk produk "'.$product->name.'" di gudang '.$warehouse->name.'.');
                    }

                    $stockBefore = $stockRow->stock_on_hand;
                    $stockAfter = $stockBefore - $item['quantity'];

                    \Log::info('POS Checkout: Creating stock movement', [
                        'product_id' => $product->id,
                        'qty' => -$item['quantity'],
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter
                    ]);

                    StockMovement::createMovement($product->id, -$item['quantity'], 'OUT', [
                        'ref_type' => 'sale',
                        'ref_id' => $sale->id,
                        'note' => 'Penjualan #'.$sale->sale_number,
                        'performed_by' => Auth::id(),
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'warehouse_id' => $warehouse->id,
                        'warehouse' => $warehouse->code,
                    ]);
                    
                    \Log::info('POS Checkout: Stock movement created');
                }
            }

            // Save payment breakdown to sale if columns exist
            try {
                // Map payment method to columns
                $cashAmount = 0; $qrAmount = 0; $edcAmount = 0;
                switch ($this->paymentMethod) {
                    case 'cash':
                        $cashAmount = $this->amountPaid;
                        break;
                    case 'qr':
                        $qrAmount = $this->amountPaid;
                        break;
                    case 'edc':
                        $edcAmount = $this->amountPaid;
                        break;
                    case 'transfer':
                        // transfer disimpan sebagai metode utama; breakdown tetap 0
                        break;
                }
                $sale->cash_amount = $cashAmount;
                $sale->qr_amount = $qrAmount;
                $sale->edc_amount = $edcAmount;
                $sale->change_amount = max(0, $this->amountPaid - $this->total);
                $sale->save();
            } catch (\Throwable $e) {
                \Log::warning('POS Checkout: Could not save payment breakdown fields', ['error' => $e->getMessage()]);
            }

            DB::commit();
            \Log::info('POS Checkout: Transaction committed successfully');

            $this->lastSale = $sale->load('saleItems.product');
            $this->resetForm();
            $this->showCheckoutModal = false;
            $this->showReceiptModal = true;

            session()->flash('success', 'Transaksi berhasil! Nomor: '.$sale->sale_number);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('POS Checkout: Exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function closeReceipt()
    {
        $this->showReceiptModal = false;
        $this->lastSale = null;
    }

    public function printReceipt()
    {
        // This would trigger JavaScript to print
        $this->dispatch('print-receipt');
    }

    private function resetForm()
    {
        // Reset only the active tab's cart
        $this->carts[$this->activeTabId]['cart'] = [];
        $this->carts[$this->activeTabId]['supplierName'] = '';
        $this->carts[$this->activeTabId]['supplierPhone'] = '';
        $this->carts[$this->activeTabId]['customerName'] = '';
        $this->carts[$this->activeTabId]['customerPhone'] = '';
        $this->carts[$this->activeTabId]['paymentMethod'] = 'cash';
        $this->carts[$this->activeTabId]['amountPaid'] = 0;
        $this->carts[$this->activeTabId]['notes'] = '';
        
        // Update current properties from active tab
        $this->updateCurrentPropertiesFromTab();
        $this->calculateTotals();
    }

    /**
     * Clean barcode input by removing unwanted characters
     */
    private function cleanBarcode($barcode)
    {
        // Remove whitespace and convert to uppercase
        $cleaned = strtoupper(trim($barcode));

        // Remove non-alphanumeric characters except hyphens
        $cleaned = preg_replace('/[^A-Z0-9\-]/', '', $cleaned);

        return $cleaned;
    }

    /**
     * Validate barcode format
     */
    private function isValidBarcodeFormat($barcode)
    {
        if (empty($barcode)) {
            return false;
        }

        // Check minimum length
        if (strlen($barcode) < 3) {
            return false;
        }

        // Check maximum length
        if (strlen($barcode) > 50) {
            return false;
        }

        // Common barcode formats validation
        $patterns = [
            '/^\d{8}$/',           // EAN-8
            '/^\d{12}$/',          // UPC-A
            '/^\d{13}$/',          // EAN-13
            '/^[A-Z0-9\-]{3,20}$/', // Code 128, Code 39, SKU
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $barcode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get price based on selected pricing tier with support for product's default price type
     */
    public function getPriceByTier($product, $tier = null)
    {
        $effectiveTier = $tier ?: $this->pricingTier;

        switch ($effectiveTier) {
            case 'retail':
                return $product->price_retail;
            case 'semi_grosir':
                return $product->price_semi_grosir ?? $product->price_retail;
            case 'grosir':
                return $product->price_grosir;
            case 'custom':
                return $product->price_retail; // Default to retail, will be manually adjusted
            default:
                return $product->price_retail;
        }
    }

    /**
     * Update all cart prices when pricing tier changes
     */
    public function updatedPricingTier()
    {
        // Dispatch event to prevent auto-focus during pricing tier update
        $this->dispatch('pricing-tier-updating');

        foreach ($this->cart as $cartKey => $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                // Use product's default price type if switching to 'retail', otherwise use selected tier
                $effectivePricingTier = $this->pricingTier === 'retail' ? $product->default_price_type : $this->pricingTier;

                $this->cart[$cartKey]['price'] = $this->getPriceByTier($product, $effectivePricingTier);
                $this->cart[$cartKey]['pricing_tier'] = $effectivePricingTier;
            }
        }
        $this->updateActiveTabFromCurrentProperties();
        $this->calculateTotals();

        // Dispatch event after update is complete
        $this->dispatch('pricing-tier-updated');
    }

    /**
     * Get barcode scan statistics for current session
     */
    public function getBarcodeStats()
    {
        return [
            'total_scans' => session('barcode_scans', 0),
            'successful_scans' => session('successful_scans', 0),
            'failed_scans' => session('failed_scans', 0),
        ];
    }

    /**
     * Get cart products with eager loading to avoid N+1 queries
     */
    public function getCartProducts()
    {
        if (empty($this->cart)) {
            return collect();
        }

        $productIds = array_column($this->cart, 'product_id');

        return Product::whereIn('id', $productIds)->get()->keyBy('id');
    }

    /**
     * Show custom item modal - DISABLED
     */
    public function showCustomItemModal()
    {
        $this->resetCustomItemForm();
        $this->showCustomItemModal = true;
    }

    /**
     * Hide custom item modal - DISABLED
     */
    public function hideCustomItemModal()
    {
        $this->showCustomItemModal = false;
        $this->resetCustomItemForm();
    }

    /**
     * Reset custom item form - DISABLED
     */
    public function resetCustomItemForm()
    {
        $this->customItemName = '';
        $this->customItemDescription = '';
        $this->customItemPrice = 0;
        $this->customItemQuantity = 1;
        $this->resetErrorBag(['customItemName', 'customItemDescription', 'customItemPrice', 'customItemQuantity']);
    }

    /**
     * Add custom item to cart - DISABLED
     */
    public function addCustomItem()
    {
        $this->validate([
            'customItemName' => 'required|string|max:255',
            'customItemDescription' => 'nullable|string|max:500',
            'customItemPrice' => 'required|numeric|min:0.01',
            'customItemQuantity' => 'required|integer|min:1',
        ]);

        $cartKey = 'custom_' . uniqid();
        $this->cart[$cartKey] = [
            'is_custom' => true,
            'name' => $this->customItemName,
            'description' => $this->customItemDescription ?: null,
            'price' => (float) $this->customItemPrice,
            'base_cost' => 0,
            'quantity' => (int) $this->customItemQuantity,
            'available_stock' => PHP_INT_MAX, // not applicable
            'pricing_tier' => 'custom',
            'warehouse_id' => $this->warehouseId,
        ];

        $this->updateActiveTabFromCurrentProperties();
        $this->calculateTotals();
        $this->showCustomItemModal = false;
        $this->resetCustomItemForm();
        session()->flash('success', 'Item custom ditambahkan ke keranjang!');
    }

    /**
     * Initialize tabs with first tab
     */
    private function initializeTabs()
    {
        $this->carts = [
            1 => [
                'id' => 1,
                'name' => 'Transaksi 1',
                'cart' => [],
                'supplierName' => '',
                'supplierPhone' => '',
                'customerName' => '',
                'customerPhone' => '',
                'paymentMethod' => 'cash',
                'amountPaid' => 0,
                'notes' => '',
                'paymentNotes' => '',
            ]
        ];
        $this->activeTabId = 1;
        $this->nextTabId = 2;
        
        // Initialize current cart from active tab
        $this->cart = &$this->carts[1]['cart'];
    }

    /**
     * Create a new tab
     */
    public function createNewTab()
    {
        // Save current tab state before creating new tab
        $this->updateActiveTabFromCurrentProperties();
        
        $newTabId = $this->nextTabId++;
        
        $this->carts[$newTabId] = [
            'id' => $newTabId,
            'name' => 'Transaksi ' . $newTabId,
            'cart' => [],
            'supplierName' => '',
            'supplierPhone' => '',
            'customerName' => '',
            'customerPhone' => '',
            'paymentMethod' => 'cash',
            'amountPaid' => 0,
            'notes' => '',
            'paymentNotes' => '',
        ];
        
        $this->switchToTab($newTabId);
        session()->flash('success', 'Tab baru berhasil dibuat!');
    }

    /**
     * Switch to a specific tab
     */
    public function switchToTab($tabId)
    {
        if (!isset($this->carts[$tabId])) {
            return;
        }
        
        // Save current tab state before switching
        $this->updateActiveTabFromCurrentProperties();
        
        $this->activeTabId = $tabId;
        $this->updateCurrentPropertiesFromTab();
        $this->calculateTotals();
    }
    
    /**
     * Update tab name from input
     */
    public function updateTabName()
    {
        if (!empty($this->tabName) && isset($this->carts[$this->activeTabId])) {
            $this->carts[$this->activeTabId]['name'] = $this->tabName;
            session()->flash('success', 'Nama tab berhasil diubah!');
        }
    }
    
    /**
     * Handle tab name input changes
     */
    public function updatedTabName()
    {
        if (isset($this->carts[$this->activeTabId])) {
            $this->carts[$this->activeTabId]['name'] = $this->tabName;
        }
    }

    /**
     * Close a tab
     */
    public function closeTab($tabId)
    {
        if (!isset($this->carts[$tabId])) {
            return;
        }
        
        // Don't allow closing if there's only one tab
        if (count($this->carts) <= 1) {
            session()->flash('error', 'Tidak dapat menutup tab. Minimal harus ada satu tab.');
            return;
        }
        
        // Check if tab has items
        if (!empty($this->carts[$tabId]['cart'])) {
            $this->showConfirm(
                'Konfirmasi Tutup Tab',
                'Tab ini memiliki item di keranjang. Apakah Anda yakin ingin menutupnya?',
                'confirmCloseTab',
                ['tabId' => $tabId]
            );
            return;
        }
        
        $this->confirmCloseTab(['tabId' => $tabId]);
    }

    /**
     * Confirm and close tab
     */
    public function confirmCloseTab($params)
    {
        $tabId = $params['tabId'];
        
        if (!isset($this->carts[$tabId])) {
            return;
        }
        
        // Save current tab state before closing
        $this->updateActiveTabFromCurrentProperties();
        
        unset($this->carts[$tabId]);
        
        // If closing the active tab, switch to another tab
        if ($this->activeTabId == $tabId) {
            $this->activeTabId = min(array_keys($this->carts));
            $this->updateCurrentPropertiesFromTab();
            $this->calculateTotals();
        }
        
        session()->flash('success', 'Tab berhasil ditutup!');
    }

    /**
     * Update current properties from active tab
     */
    private function updateCurrentPropertiesFromTab()
    {
        // Ensure the active tab exists
        if (!isset($this->carts[$this->activeTabId])) {
            return;
        }
        
        $activeTab = &$this->carts[$this->activeTabId];
        
        // Ensure cart key exists
        if (!isset($activeTab['cart'])) {
            $activeTab['cart'] = [];
        }
        
        $this->cart = &$activeTab['cart']; // Use reference to sync changes
        $this->supplierName = $activeTab['supplierName'] ?? '';
        $this->supplierPhone = $activeTab['supplierPhone'] ?? '';
        $this->customerName = $activeTab['customerName'] ?? '';
        $this->customerPhone = $activeTab['customerPhone'] ?? '';
        $this->paymentMethod = $activeTab['paymentMethod'] ?? 'cash';
        $this->amountPaid = $activeTab['amountPaid'] ?? 0;
        $this->notes = $activeTab['notes'] ?? '';
        $this->paymentNotes = $activeTab['paymentNotes'] ?? '';
        $this->tabName = $activeTab['name'] ?? 'Tab ' . $this->activeTabId;
    }

    /**
     * Update active tab from current properties
     */
    private function updateActiveTabFromCurrentProperties()
    {
        // Ensure the active tab exists
        if (!isset($this->carts[$this->activeTabId])) {
            return;
        }
        
        // Ensure cart key exists
        if (!isset($this->carts[$this->activeTabId]['cart'])) {
            $this->carts[$this->activeTabId]['cart'] = [];
        }
        
        $this->carts[$this->activeTabId]['cart'] = $this->cart;
        $this->carts[$this->activeTabId]['supplierName'] = $this->supplierName ?? '';
        $this->carts[$this->activeTabId]['supplierPhone'] = $this->supplierPhone ?? '';
        $this->carts[$this->activeTabId]['customerName'] = $this->customerName ?? '';
        $this->carts[$this->activeTabId]['customerPhone'] = $this->customerPhone ?? '';
        $this->carts[$this->activeTabId]['paymentMethod'] = $this->paymentMethod ?? 'cash';
        $this->carts[$this->activeTabId]['amountPaid'] = $this->amountPaid ?? 0;
        $this->carts[$this->activeTabId]['notes'] = $this->notes ?? '';
        $this->carts[$this->activeTabId]['paymentNotes'] = $this->paymentNotes ?? '';
    }

    /**
     * Get item count for a tab
     */
    public function getTabItemCount($tabId)
    {
        if (!isset($this->carts[$tabId])) {
            return 0;
        }
        
        return count($this->carts[$tabId]['cart']);
    }

    /**
     * Get tab total amount
     */
    public function getTabTotal($tabId)
    {
        if (!isset($this->carts[$tabId])) {
            return 0;
        }
        
        $total = 0;
        foreach ($this->carts[$tabId]['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        // Discounts removed
        return max(0, $total);
    }

    
    /**
     * Handle live update of tab name from wire:model
     */
    public function updatedCarts($value, $key)
    {
        // Extract tab ID and property from the key
        // Key format: "1.name", "2.customerName", etc.
        if (preg_match('/^(\d+)\.(.+)$/', $key, $matches)) {
            $tabId = $matches[1];
            $property = $matches[2];
            
            // Ensure the tab exists
            if (!isset($this->carts[$tabId])) {
                return;
            }
            
            // Update the property
            $this->carts[$tabId][$property] = $value;
            
            // If this is the active tab, also update the current properties
            if ($tabId == $this->activeTabId) {
                switch ($property) {
                    case 'name':
                        // Tab name doesn't need to sync with current properties
                        break;
                    case 'supplierName':
                        $this->supplierName = $value;
                        break;
                    case 'supplierPhone':
                        $this->supplierPhone = $value;
                        break;
                    case 'paymentMethod':
                        $this->paymentMethod = $value;
                        break;
                    case 'amountPaid':
                        $this->amountPaid = $value;
                        break;
                    case 'discount':
                        $this->discount = $value;
                        break;
                    case 'discountType':
                        $this->discountType = $value;
                        break;
                    case 'notes':
                        $this->notes = $value;
                        break;
                    case 'customerName':
                        $this->customerName = $value;
                        break;
                    case 'customerPhone':
                        $this->customerPhone = $value;
                        break;
                    case 'paymentNotes':
                        $this->paymentNotes = $value;
                        break;
                    case 'customItemName':
                        $this->customItemName = $value;
                        break;
                    case 'customItemDescription':
                        $this->customItemDescription = $value;
                        break;
                    case 'customItemPrice':
                        $this->customItemPrice = $value;
                        break;
                    case 'customItemQuantity':
                        $this->customItemQuantity = $value;
                        break;
                }
            }
        }
    }

    public function render()
    {
        $query = Product::where('status', 'active')
            ->whereNull('deleted_at')
            ->with(['warehouseStocks' => function ($query) {
                if ($this->warehouseId) {
                    $query->where('warehouse_id', $this->warehouseId);
                }
            }]);

        // Only show products that have stock in the store warehouse
        if ($this->warehouseId) {
            $query->whereHas('warehouseStocks', function ($q) {
                $q->where('warehouse_id', $this->warehouseId)
                  ->where('stock_on_hand', '>', 0);
            });
        }

        if (! empty($this->productSearch)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->productSearch.'%')
                    ->orWhere('sku', 'like', '%'.$this->productSearch.'%')
                    ->orWhere('barcode', 'like', '%'.$this->productSearch.'%');
            });
        }

        $products = $query->orderBy('name')->get();
        $cartProducts = $this->getCartProducts();

        return view('livewire.pos-kasir', [
            'products' => $products,
            'cartProducts' => $cartProducts,
            'selectedWarehouseId' => $this->warehouseId,
            'warehouses' => $this->warehouses,
            'carts' => $this->carts,
            'activeTabId' => $this->activeTabId,
        ]);
    }
}
