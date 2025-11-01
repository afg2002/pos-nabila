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

#[Layout("layouts.app")]
class PosResponsive extends Component
{
    use WithAlerts;

    public $barcode = "";
    public $productSearch = "";

    // Filter and Sort
    public $showFilterModal = false;
    public $filterCategory = "";
    public $filterStockStatus = "all"; // all, available, low, out
    public $sortBy = "most_purchased"; // most_purchased, name, price_low, price_high, stock

    // Infinite Scroll / Lazy Load
    public $productsPerPage = 20;
    public $currentPage = 1;
    public $hasMoreProducts = true;

    // Responsive UI state
    public $mobileCartOpen = false;
    public $sidebarCollapsed = false;
    public $viewMode = 'auto'; // auto, mobile, tablet, desktop
    public $screenWidth = 0;

    // Cart and transaction management
    public $cart = [];
    public $carts = [];
    public $activeTabId = 1;
    public $nextTabId = 2;
    public $tabName = "";

    // Warehouse management
    public $warehouses = [];
    public $warehouseId = "";
    public $selectedWarehouse = "";
    public $activeWarehouseName = "";
    public $activeWarehouseCode = "";
    public $activeWarehouseTypeLabel = "";
    public $activeWarehouseAddress = "";

    // Customer and supplier info
    public $supplierName = "";
    public $supplierPhone = "";
    public $customerName = "";
    public $customerPhone = "";

    // Payment processing
    public $paymentMethod = "cash";
    public $pricingTier = "retail";
    public $amountPaid = 0;
    public $paymentStatus = Sale::PAYMENT_STATUS_PAID;
    public $discount = 0;
    public $discountType = "amount";
    public $notes = "";
    public $paymentNotes = "";

    // Rounding settings
    public $roundingEnabled = false;
    public $roundingStep = 100;
    public $roundingMode = "nearest";
    public $roundedTotal = 0;
    public $roundingAdjustment = 0;

    // Custom items
    public $customItemName = "";
    public $customItemDescription = "";
    public $customItemPrice = 0;
    public $customItemQuantity = 1;

    // Computed properties
    public $subtotal = 0;
    public $discountAmount = 0;
    public $total = 0;
    public $change = 0;

    // UI State
    public $showCheckoutModal = false;
    public $showReceiptModal = false;
    public $lastSale = null;

    protected $rules = [
        "supplierName" => "nullable|string|max:255",
        "supplierPhone" => "nullable|string|max:20",
        "customerName" => "nullable|string|max:255",
        "customerPhone" => "nullable|string|max:20",
        "paymentMethod" => "required|in:cash,transfer,edc,qr",
        "warehouseId" => "required|exists:warehouses,id",
        "pricingTier" => "required|in:retail,semi_grosir,grosir,custom",
        "paymentNotes" => "nullable|string|max:255",
        "amountPaid" => "required|numeric|min:0",
        "notes" => "nullable|string|max:500",
    ];

    protected function getCheckoutRules()
    {
        return [
            "supplierName" => "nullable|string|max:255",
            "supplierPhone" => "nullable|string|max:20",
            "customerName" => "nullable|string|max:255",
            "customerPhone" => "nullable|string|max:20",
            "paymentMethod" => "required|in:cash,transfer,edc,qr",
            "warehouseId" => "required|exists:warehouses,id",
            "amountPaid" => "required|numeric|min:0",
            "paymentStatus" =>
                "required|in:" .
                implode(",", [
                    Sale::PAYMENT_STATUS_PAID,
                    Sale::PAYMENT_STATUS_PARTIAL,
                    Sale::PAYMENT_STATUS_UNPAID,
                ]),
            "notes" => "nullable|string|max:500",
        ];
    }

    /**
     * Round price with granular steps
     * < 1000: round to 100
     * 1000-10000: round to 500
     * >= 10000: round to 1000
     */
    private function roundPrice($price)
    {
        // Harga < 1000 → Round ke 100
        if ($price < 1000) {
            return ceil($price / 100) * 100;
        }
        // Harga 1000-10000 → Round ke 500
        elseif ($price < 10000) {
            return ceil($price / 500) * 500;
        }
        // Harga >= 10000 → Round ke 1000
        else {
            return ceil($price / 1000) * 1000;
        }
    }

    public function mount(): void
    {
        if (!auth()->user()->hasPermission("pos.access")) {
            abort(403, "You do not have permission to access POS system.");
        }

        $this->warehouses = Warehouse::ordered()->get();
        $defaultWarehouse = Warehouse::getDefault();

        if (!$defaultWarehouse) {
            $defaultWarehouse =
                Warehouse::where("type", "store")->ordered()->first() ??
                Warehouse::ordered()->first();
        }

        if ($defaultWarehouse) {
            $this->warehouseId = $defaultWarehouse->id;
            $this->selectedWarehouse = $defaultWarehouse->id;
            $this->updateActiveWarehouseInfo();
        }

        $this->initializeTabs();
        $this->updateCurrentPropertiesFromTab();
        $this->calculateTotals();
        $this->refreshCartForWarehouse();

        // Request cart restoration from localStorage
        $this->dispatch('request-cart-restore');
    }

    public function restoreCartFromCache($cachedData)
    {
        if (empty($cachedData) || !is_array($cachedData)) {
            return;
        }

        // Restore carts data
        if (isset($cachedData['carts'])) {
            $this->carts = $cachedData['carts'];
        }

        if (isset($cachedData['activeTabId'])) {
            $this->activeTabId = (int) $cachedData['activeTabId'];
        }

        if (!empty($this->carts)) {
            $cartIds = array_map('intval', array_keys($this->carts));
            $maxId = !empty($cartIds) ? max($cartIds) : 1;
            $this->nextTabId = $maxId + 1;

            if (!isset($this->carts[$this->activeTabId])) {
                $this->activeTabId = reset($cartIds) ?: 1;
            }
        } else {
            $this->initializeTabs();
        }

        // Update current properties from restored tab
        $this->updateCurrentPropertiesFromTab();
        $this->calculateTotals();
        $this->saveCartToCache();

        session()->flash('success', 'Keranjang berhasil dipulihkan dari cache!');
    }

    private function saveCartToCache()
    {
        $cartData = [
            'carts' => $this->carts,
            'activeTabId' => $this->activeTabId,
        ];

        $this->dispatch('save-cart-to-cache', cartData: $cartData);
    }

    public function updatedBarcode()
    {
        if (!empty($this->barcode)) {
            $this->barcode = $this->cleanBarcode($this->barcode);
            if ($this->isValidBarcodeFormat($this->barcode)) {
                $this->addProductByBarcode();
            } else {
                session()->flash("error", 'Format barcode tidak valid: "' . $this->barcode . '"');
                $this->barcode = "";
            }
        }
    }

    public function updatedAmountPaid()
    {
        $this->updateActiveTabFromCurrentProperties();
        $this->calculateTotals();
    }

    public function updatedPaymentStatus()
    {
        if ($this->paymentStatus === Sale::PAYMENT_STATUS_UNPAID) {
            $this->amountPaid = 0;
        } elseif ($this->paymentStatus === Sale::PAYMENT_STATUS_PAID) {
            $this->amountPaid = max($this->amountPaid, $this->total);
        } elseif ($this->paymentStatus === Sale::PAYMENT_STATUS_PARTIAL) {
            if ($this->total > 0 && ($this->amountPaid <= 0 || $this->amountPaid >= $this->total)) {
                $this->amountPaid = max(0.01, $this->total - 0.01);
            }
        }
        $this->calculateTotals();
        $this->updateActiveTabFromCurrentProperties();
    }

    private function updateActiveWarehouseInfo(): void
    {
        $warehouse = null;
        if (!empty($this->selectedWarehouse)) {
            $warehouse = Warehouse::find($this->selectedWarehouse);
        }

        if (!$warehouse) {
            $warehouse =
                Warehouse::getDefault() ??
                (Warehouse::where("type", "store")->ordered()->first() ??
                    Warehouse::ordered()->first());
        }

        if ($warehouse) {
            $this->activeWarehouseName = $warehouse->name ?? "Toko Utama";
            $this->activeWarehouseCode = $warehouse->code ?? "";
            $type = $warehouse->type ?? "store";
            $this->activeWarehouseTypeLabel = $type === "store" ? "Toko" : ucfirst($type);
            $this->activeWarehouseAddress = $warehouse->address ?? "";
        } else {
            $this->activeWarehouseName = "Toko Utama";
            $this->activeWarehouseCode = "";
            $this->activeWarehouseTypeLabel = "Toko";
            $this->activeWarehouseAddress = "";
        }
    }

    public function updatedWarehouseId(): void
    {
        $this->enforceDefaultWarehouse();
        $this->updateActiveWarehouseInfo();
    }

    public function updatedSelectedWarehouse(): void
    {
        $this->enforceDefaultWarehouse();
        $this->updateActiveWarehouseInfo();
    }

    private function enforceDefaultWarehouse(): void
    {
        $defaultWarehouse = Warehouse::getDefault();
        if (!$defaultWarehouse) return;

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
            $this->refreshCartForWarehouse();
        }
    }

    private function getAvailableStock(Product $product): int
    {
        if (!$this->warehouseId) {
            return (int) $product->current_stock;
        }

        return (int) (ProductWarehouseStock::query()
            ->where("product_id", $product->id)
            ->where("warehouse_id", $this->warehouseId)
            ->value("stock_on_hand") ?? 0);
    }

    private function refreshCartForWarehouse(): void
    {
        foreach ($this->carts as $tabId => &$tabData) {
            foreach ($tabData["cart"] as $cartKey => $item) {
                if (!isset($item["product_id"])) continue;

                $product = Product::find($item["product_id"]);
                if (!$product) {
                    unset($tabData["cart"][$cartKey]);
                    continue;
                }

                $availableStock = $this->getAvailableStock($product);
                if ($availableStock <= 0) {
                    unset($tabData["cart"][$cartKey]);
                    session()->flash("error", 'Stok di lokasi terpilih habis untuk produk "' . $product->name . '".');
                    continue;
                }

                $tabData["cart"][$cartKey]["available_stock"] = $availableStock;
                $tabData["cart"][$cartKey]["warehouse_id"] = $this->warehouseId;

                if ($tabData["cart"][$cartKey]["quantity"] > $availableStock) {
                    $tabData["cart"][$cartKey]["quantity"] = $availableStock;
                    session()->flash("error", 'Jumlah di keranjang dikurangi karena stok terbatas untuk produk "' . $product->name . '".');
                }
            }
        }
        $this->updateCurrentPropertiesFromTab();
        $this->calculateTotals();
    }

    public function addProductByBarcode()
    {
        if (empty($this->barcode)) return;

        $scannedCode = trim($this->barcode);
        $scannedCode = preg_replace("/[^a-zA-Z0-9]/", "", $scannedCode);
        $scannedCode = strtoupper($scannedCode);

        if (strlen($scannedCode) < 3 || strlen($scannedCode) > 50) {
            session()->flash("error", 'Barcode tidak valid: "' . $scannedCode . '"');
            $this->barcode = "";
            return;
        }

        $product = Product::where("status", "active")
            ->whereNull("deleted_at")
            ->where("barcode", $scannedCode)
            ->whereHas("warehouseStocks", function ($q) {
                if ($this->warehouseId) {
                    $q->where("warehouse_id", $this->warehouseId)->where("stock_on_hand", ">", 0);
                }
            })
            ->first();

        if (!$product) {
            $product = Product::where("status", "active")
                ->whereNull("deleted_at")
                ->where("sku", $scannedCode)
                ->whereHas("warehouseStocks", function ($q) {
                    if ($this->warehouseId) {
                        $q->where("warehouse_id", $this->warehouseId)->where("stock_on_hand", ">", 0);
                    }
                })
                ->first();
        }

        if (!$product) {
            session()->flash("error", 'Produk dengan barcode/SKU "' . $scannedCode . '" tidak ditemukan!');
            $this->barcode = "";
            return;
        }

        $availableStock = $this->getAvailableStock($product);
        if ($availableStock <= 0) {
            session()->flash("error", 'Stok produk "' . $product->name . '" di lokasi terpilih habis!');
            $this->barcode = "";
            return;
        }

        $this->addToCart($product->id, $product);
        $this->updateActiveTabFromCurrentProperties();
        $this->barcode = "";
    }

    public function addToCart($productId, $product = null)
    {
        $product = $product ?? Product::where("status", "active")
            ->whereNull("deleted_at")
            ->whereHas("warehouseStocks", function ($q) {
                if ($this->warehouseId) {
                    $q->where("warehouse_id", $this->warehouseId)->where("stock_on_hand", ">", 0);
                }
            })
            ->find($productId);

        if (!$product) {
            session()->flash("error", "Produk tidak ditemukan atau tidak tersedia untuk dijual!");
            return;
        }

        $availableStock = $this->getAvailableStock($product);
        if ($availableStock <= 0) {
            session()->flash("error", 'Stok tidak tersedia untuk produk "' . $product->name . '"!');
            return;
        }

        $cartKey = "product_" . $productId;
        if (isset($this->cart[$cartKey])) {
            $factor = $this->cart[$cartKey]["selected_unit_to_base_qty"] ?? 1;
            $newBaseQty = (int) ceil(($this->cart[$cartKey]["quantity"] + 1) * $factor);
            if ($newBaseQty > $availableStock) {
                session()->flash("error", 'Stok tidak mencukupi untuk produk "' . $product->name . '"!');
                return;
            }
            $this->cart[$cartKey]["quantity"]++;
            $this->cart[$cartKey]["base_qty"] = (int) ceil($this->cart[$cartKey]["quantity"] * $factor);
        } else {
            $effectivePricingTier = $this->pricingTier === "retail" ? $product->default_price_type : $this->pricingTier;
            $basePrice = $this->getPriceByTier($product, $effectivePricingTier);
            $roundedPrice = $this->roundPrice($basePrice);

            $this->cart[$cartKey] = [
                "product_id" => $product->id,
                "name" => $product->name,
                "sku" => $product->sku,
                "barcode" => $product->barcode,
                "price" => $roundedPrice,
                "base_cost" => $product->base_cost,
                "quantity" => 1,
                "available_stock" => $availableStock,
                "pricing_tier" => $effectivePricingTier,
                "warehouse_id" => $this->warehouseId,
                "selected_unit_id" => $product->unit_id,
                "selected_unit_to_base_qty" => 1,
                "base_qty" => 1,
            ];
        }

        $this->cart[$cartKey]["available_stock"] = $availableStock;
        $this->updateActiveTabFromCurrentProperties();
        $this->calculateTotals();
        $this->saveCartToCache();
        session()->flash("success", 'Produk "' . $product->name . '" ditambahkan ke keranjang!');
    }

    public function setActiveTab($tabIndex)
    {
        // Switch active tab by index (used by Alpine.js multi-tab)
        if ($tabIndex >= 0 && isset($this->carts[$tabIndex + 1])) {
            $this->activeTabId = $tabIndex + 1;
            $this->updateCurrentPropertiesFromTab();
            $this->calculateTotals();
            $this->saveCartToCache();
        }
    }

    public function setCustomerInfo($name, $phone)
    {
        // Update customer info for active tab
        $this->customerName = $name ?? '';
        $this->customerPhone = $phone ?? '';
        $this->updateActiveTabFromCurrentProperties();
        $this->saveCartToCache();
    }

    public function updateQuantity($cartKey, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($cartKey);
            return;
        }

        if (!isset($this->cart[$cartKey])) return;

        $item = $this->cart[$cartKey];
        if (empty($item["product_id"])) return;

        $product = Product::find($item["product_id"]);
        if (!$product) return;

        $availableStock = $this->getAvailableStock($product);
        $factor = $item["selected_unit_to_base_qty"] ?? 1;
        $baseQty = (int) ceil($quantity * $factor);

        if ($baseQty > $availableStock) {
            session()->flash("error", 'Stok tidak mencukupi untuk produk "' . $product->name . '"!');
            return;
        }

        $this->cart[$cartKey]["quantity"] = $quantity;
        $this->cart[$cartKey]["base_qty"] = $baseQty;
        $this->cart[$cartKey]["available_stock"] = $availableStock;

        $this->updateActiveTabFromCurrentProperties();
        $this->calculateTotals();
        $this->saveCartToCache();
    }

    public function removeFromCart($cartKey)
    {
        if (isset($this->cart[$cartKey])) {
            unset($this->cart[$cartKey]);
            $this->updateActiveTabFromCurrentProperties();
            $this->calculateTotals();
            $this->saveCartToCache();
            session()->flash("success", "Produk dihapus dari keranjang!");
        }
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;
        foreach ($this->cart as $item) {
            $lineTotal = ($item["price"] ?? 0) * ($item["quantity"] ?? 0);
            $this->subtotal += $lineTotal;
        }

        $this->discountAmount = 0;
        $baseTotal = max(0, $this->subtotal);

        // Automatic system rounding for cash payments: ALWAYS round UP to nearest 1000
        if ($this->roundingEnabled && $this->paymentMethod === "cash") {
            $autoStep = 1000; // enforce thousand-step rounding for consistency
            $this->roundedTotal = $this->roundCash($baseTotal, $autoStep, "up");
            $this->roundingAdjustment = $this->roundedTotal - $baseTotal;
            $this->total = $this->roundedTotal;
        } else {
            $this->roundedTotal = $baseTotal;
            $this->roundingAdjustment = 0;
            $this->total = $baseTotal;
        }

        $this->change = max(0, $this->amountPaid - $this->total);
    }

    private function roundCash($amount, $step = 100, $mode = "nearest")
    {
        if ($step <= 0) return $amount;
        $ratio = $amount / $step;

        switch ($mode) {
            case "up": return ceil($ratio) * $step;
            case "down": return floor($ratio) * $step;
            case "nearest":
            default: return round($ratio) * $step;
        }
    }

    /**
     * Determine automatic rounding step based on total amount
     * < 1000 => 100, 1000-9999 => 500, >= 10000 => 1000
     */
    private function getAutoRoundingStep($amount): int
    {
        if ($amount < 1000) {
            return 100;
        } elseif ($amount < 10000) {
            return 500;
        }
        return 1000;
    }

    public function openCheckout()
    {
        // Update from active tab first (in case sync from Alpine store)
        $this->updateActiveTabFromCurrentProperties();

        // Allow checkout even if cart appears empty momentarily
        // (Alpine store may have data that's being synced)
        if (empty($this->cart)) {
            // Give warning but still open modal if total > 0
            if ($this->total <= 0) {
                session()->flash("error", "Keranjang masih kosong! Silakan tambahkan produk terlebih dahulu.");
                return;
            }
        }

        $this->amountPaid = $this->total;
        $this->paymentStatus = Sale::PAYMENT_STATUS_PAID;
        $this->showCheckoutModal = true;
    }

    public function closeCheckout()
    {
        $this->updateActiveTabFromCurrentProperties();
        $this->showCheckoutModal = false;
    }

    public function processCheckout()
    {
        if (!auth()->user()->hasPermission("pos.create_sales")) {
            session()->flash("error", "Anda tidak memiliki izin untuk memproses penjualan!");
            return;
        }

        // Check if cart is empty
        if (empty($this->cart)) {
            session()->flash("error", "Tidak dapat melakukan checkout dengan keranjang kosong! Silakan tambahkan produk terlebih dahulu.");
            return;
        }

        // Auto-set payment status based on amount paid
        if ($this->amountPaid >= $this->total) {
            $this->paymentStatus = Sale::PAYMENT_STATUS_PAID;
        } elseif ($this->amountPaid > 0) {
            $this->paymentStatus = Sale::PAYMENT_STATUS_PARTIAL;
        } else {
            $this->paymentStatus = Sale::PAYMENT_STATUS_UNPAID;
        }

        $this->validate($this->getCheckoutRules());

        try {
            DB::beginTransaction();

            $status = $this->paymentStatus;
            $saleData = [
                "sale_number" => "POS-" . date("Ymd") . "-" . str_pad(Sale::whereDate("created_at", today())->count() + 1, 4, "0", STR_PAD_LEFT),
                "cashier_id" => Auth::id(),
                "subtotal" => $this->subtotal,
                "tax_amount" => 0,
                "total_amount" => $this->total,
                "payment_amount" => $this->amountPaid,
                "discount_total" => 0,
                "final_total" => $this->total,
                "payment_method" => $this->paymentMethod,
                "payment_notes" => $this->paymentNotes,
                "payment_status" => $status,
                "status" => "PAID",
                "notes" => $this->notes,
            ];

            $sale = Sale::create($saleData);
            $warehouse = Warehouse::find($this->warehouseId);

            if (!$warehouse) {
                throw new \RuntimeException("Gudang tidak ditemukan.");
            }

            foreach ($this->cart as $item) {
                if (!empty($item["is_custom"]) || empty($item["product_id"])) {
                    $saleItemData = [
                        "sale_id" => $sale->id,
                        "product_id" => null,
                        "qty" => $item["quantity"],
                        "unit_price" => $item["price"],
                        "price_tier" => "custom",
                        "margin_pct_at_sale" => 0.0,
                        "below_margin_flag" => false,
                        "is_custom" => true,
                        "custom_item_name" => $item["custom_item_name"] ?? ($item["name"] ?? "Custom Item"),
                        "custom_item_description" => $item["custom_item_description"] ?? null,
                    ];
                    SaleItem::create($saleItemData);
                    continue;
                }

                $saleItemData = [
                    "sale_id" => $sale->id,
                    "product_id" => $item["product_id"],
                    "qty" => $item["quantity"],
                    "unit_price" => $item["price"],
                    "price_tier" => $item["pricing_tier"] ?? ($this->pricingTier ?? "retail"),
                    "margin_pct_at_sale" => 0.0,
                    "below_margin_flag" => false,
                ];
                $saleItem = SaleItem::create($saleItemData);

                $product = Product::find($item["product_id"]);
                if (!$product) {
                    throw new \RuntimeException("Produk tidak ditemukan saat memproses penjualan.");
                }

                $stockRow = ProductWarehouseStock::firstOrCreate([
                    "product_id" => $product->id,
                    "warehouse_id" => $warehouse->id,
                ], [
                    "stock_on_hand" => 0,
                    "reserved_stock" => 0,
                    "safety_stock" => 0,
                ]);

                $deductQty = isset($item["base_qty"]) ? (int) $item["base_qty"] : (int) ceil(($item["selected_unit_to_base_qty"] ?? 1) * $item["quantity"]);
                
                if ($stockRow->stock_on_hand < $deductQty) {
                    throw new \RuntimeException('Stok tidak mencukupi untuk produk "' . $product->name . '" di gudang ' . $warehouse->name . ".");
                }

                $stockBefore = $stockRow->stock_on_hand;
                $stockAfter = $stockBefore - $deductQty;

                StockMovement::createMovement($product->id, -$deductQty, "OUT", [
                    "ref_type" => "sale",
                    "ref_id" => $sale->id,
                    "note" => "Penjualan #" . $sale->sale_number,
                    "performed_by" => Auth::id(),
                    "stock_before" => $stockBefore,
                    "stock_after" => $stockAfter,
                    "warehouse_id" => $warehouse->id,
                    "warehouse" => $warehouse->code,
                ]);
            }

            $cashAmount = 0;
            $qrAmount = 0;
            $edcAmount = 0;
            switch ($this->paymentMethod) {
                case "cash": $cashAmount = $this->amountPaid; break;
                case "qr": $qrAmount = $this->amountPaid; break;
                case "edc": $edcAmount = $this->amountPaid; break;
                case "transfer": break;
            }
            
            $sale->cash_amount = $cashAmount;
            $sale->qr_amount = $qrAmount;
            $sale->edc_amount = $edcAmount;
            $sale->change_amount = max(0, $this->amountPaid - $this->total);
            $sale->save();

            DB::commit();

            $this->lastSale = $sale->load("saleItems.product");
            $this->resetForm();
            $this->showCheckoutModal = false;
            $this->showReceiptModal = true;

            // Auto-print receipt after successful checkout
            $this->dispatch('auto-print-receipt', saleId: $sale->id);

            session()->flash("success", "Transaksi berhasil! Nomor: " . $sale->sale_number);
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash("error", "Terjadi kesalahan: " . $e->getMessage());
        }
    }

    private function resetForm()
    {
        $checkedOutTabId = $this->activeTabId;

        // If only one tab exists, reset it instead of deleting
        if (count($this->carts) <= 1) {
            $this->carts[$this->activeTabId]["cart"] = [];
            $this->carts[$this->activeTabId]["supplierName"] = "";
            $this->carts[$this->activeTabId]["supplierPhone"] = "";
            $this->carts[$this->activeTabId]["customerName"] = "";
            $this->carts[$this->activeTabId]["customerPhone"] = "";
            $this->carts[$this->activeTabId]["paymentMethod"] = "cash";
            $this->carts[$this->activeTabId]["amountPaid"] = 0;
            $this->carts[$this->activeTabId]["notes"] = "";

            $this->updateCurrentPropertiesFromTab();
            $this->calculateTotals();
            $this->dispatch('clear-cart-cache');
            return;
        }

        // Multiple tabs exist - delete the checked out tab
        // First, switch to another tab
        $remainingTabs = array_keys($this->carts);
        $nextTabId = null;

        foreach ($remainingTabs as $tabId) {
            if ($tabId != $checkedOutTabId) {
                $nextTabId = $tabId;
                break;
            }
        }

        if ($nextTabId) {
            // Switch to the next available tab
            $this->activeTabId = $nextTabId;
            $this->updateCurrentPropertiesFromTab();
            $this->calculateTotals();
        }

        // Delete the checked out tab
        unset($this->carts[$checkedOutTabId]);

        // Dispatch event to update Alpine state
        $this->dispatch('tab-closed', tabId: $checkedOutTabId, newActiveTabId: $this->activeTabId);
        $this->saveCartToCache();
    }

    private function cleanBarcode($barcode)
    {
        $cleaned = strtoupper(trim($barcode));
        $cleaned = preg_replace("/[^A-Z0-9\-]/", "", $cleaned);
        return $cleaned;
    }

    private function isValidBarcodeFormat($barcode)
    {
        if (empty($barcode)) return false;
        if (strlen($barcode) < 3 || strlen($barcode) > 50) return false;

        $patterns = [
            '/^\d{8}$/', // EAN-8
            '/^\d{12}$/', // UPC-A
            '/^\d{13}$/', // EAN-13
            '/^[A-Z0-9\-]{3,20}$/', // Code 128, Code 39, SKU
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $barcode)) return true;
        }
        return false;
    }

    public function getPriceByTier($product, $tier = null)
    {
        $effectiveTier = $tier ?: $this->pricingTier;
        switch ($effectiveTier) {
            case "retail": return $product->price_retail;
            case "semi_grosir": return $product->price_semi_grosir ?? $product->price_retail;
            case "grosir": return $product->price_grosir;
            case "custom": return $product->price_retail;
            default: return $product->price_retail;
        }
    }

    public function updateItemPriceType($cartKey, $priceType)
    {
        if (isset($this->cart[$cartKey])) {
            $product = Product::find($this->cart[$cartKey]["product_id"]);
            if ($product) {
                // Only update price if not switching to custom (preserve custom price)
                if ($priceType !== 'custom') {
                    $factor = $this->cart[$cartKey]["selected_unit_to_base_qty"] ?? 1;
                    $basePrice = $this->getPriceByTier($product, $priceType) * $factor;
                    $this->cart[$cartKey]["price"] = $this->roundPrice($basePrice);
                }

                $this->cart[$cartKey]["pricing_tier"] = $priceType;
                $this->updateActiveTabFromCurrentProperties();
                $this->calculateTotals();
                $this->saveCartToCache();

                $priceTypeName = Product::getPriceTypes()[$priceType];
                session()->flash("success", "Harga produk {$product->name} diubah ke {$priceTypeName}!");
            }
        }
    }

    public function bulkSetCartPriceType($priceType)
    {
        $updatedCount = 0;
        foreach ($this->cart as $cartKey => $item) {
            $product = Product::find($item["product_id"]);
            if ($product) {
                $factor = $item["selected_unit_to_base_qty"] ?? 1;
                $basePrice = $this->getPriceByTier($product, $priceType) * $factor;
                $this->cart[$cartKey]["price"] = $this->roundPrice($basePrice);
                $this->cart[$cartKey]["pricing_tier"] = $priceType;
                $updatedCount++;
            }
        }

        if ($updatedCount > 0) {
            $this->updateActiveTabFromCurrentProperties();
            $this->calculateTotals();
            $this->saveCartToCache();
            $priceTypeName = Product::getPriceTypes()[$priceType];
            session()->flash("success", "{$updatedCount} produk di keranjang diubah ke jenis harga {$priceTypeName}!");
        }
    }

    public function updatePrice($cartKey, $price)
    {
        if (isset($this->cart[$cartKey])) {
            $baseCost = $this->cart[$cartKey]["base_cost"];
            $minPrice = $baseCost * 1.1;

            if ($price < $minPrice) {
                session()->flash("error", "Harga tidak boleh kurang dari Rp " . number_format($minPrice, 0, ",", ".") . " (margin minimum 10%)");
                return;
            }

            $this->cart[$cartKey]["price"] = $price;
            $this->cart[$cartKey]["pricing_tier"] = "custom";
            $this->updateActiveTabFromCurrentProperties();
            $this->calculateTotals();
            $this->saveCartToCache();
            session()->flash("success", "Harga custom berhasil diterapkan!");
        }
    }

    public function addCustomItem()
    {
        $validated = $this->validate([
            "customItemName" => "required|string|max:255",
            "customItemDescription" => "nullable|string|max:500",
            "customItemPrice" => "required|numeric|min:0",
            "customItemQuantity" => "required|integer|min:1",
        ]);

        $cartKey = "custom_" . uniqid();

        $this->cart[$cartKey] = [
            "product_id" => null,
            "name" => $validated["customItemName"],
            "custom_item_name" => $validated["customItemName"],
            "custom_item_description" => $validated["customItemDescription"] ?? null,
            "price" => $validated["customItemPrice"],
            "base_cost" => 0,
            "quantity" => $validated["customItemQuantity"],
            "available_stock" => null,
            "pricing_tier" => "custom",
            "warehouse_id" => $this->warehouseId,
            "is_custom" => true,
        ];

        $this->updateActiveTabFromCurrentProperties();
        $this->calculateTotals();
        $this->saveCartToCache();

        // Reset custom item form inputs
        $this->customItemName = "";
        $this->customItemDescription = "";
        $this->customItemPrice = 0;
        $this->customItemQuantity = 1;

        session()->flash("success", "Item/Jasa custom ditambahkan ke keranjang!");
    }

    public function addCustomItemFromJS($name, $price, $quantity)
    {
        if (empty($name) || $price <= 0 || $quantity <= 0) {
            session()->flash("error", "Data item custom tidak valid!");
            return;
        }

        $cartKey = "custom_" . uniqid();

        $this->cart[$cartKey] = [
            "product_id" => null,
            "name" => $name,
            "custom_item_name" => $name,
            "custom_item_description" => null,
            "price" => $price,
            "base_cost" => 0,
            "quantity" => $quantity,
            "available_stock" => null,
            "pricing_tier" => "custom",
            "warehouse_id" => $this->warehouseId,
            "is_custom" => true,
        ];

        $this->updateActiveTabFromCurrentProperties();
        $this->calculateTotals();
        $this->saveCartToCache();

        session()->flash("success", "Item/Jasa custom '$name' ditambahkan ke keranjang!");
    }

    public function goToSalesHistory()
    {
        return redirect()->route('kasir.management');
    }

    private function initializeTabs()
    {
        $this->carts = [
            1 => [
                "id" => 1,
                "name" => "Transaksi 1",
                "cart" => [],
                "supplierName" => "",
                "supplierPhone" => "",
                "customerName" => "",
                "customerPhone" => "",
                "paymentMethod" => "cash",
                "amountPaid" => 0,
                "notes" => "",
                "paymentNotes" => "",
            ],
        ];
        $this->activeTabId = 1;
        $this->nextTabId = 2;
        $this->cart = &$this->carts[1]["cart"];
    }

    public function createNewTab()
    {
        $this->updateActiveTabFromCurrentProperties();
        $newTabId = $this->nextTabId++;

        $this->carts[$newTabId] = [
            "id" => $newTabId,
            "name" => "Transaksi " . $newTabId,
            "cart" => [],
            "supplierName" => "",
            "supplierPhone" => "",
            "customerName" => "",
            "customerPhone" => "",
            "paymentMethod" => "cash",
            "amountPaid" => 0,
            "notes" => "",
            "paymentNotes" => "",
        ];

        $this->switchToTab($newTabId);
        $this->saveCartToCache();
        session()->flash("success", "Tab baru berhasil dibuat!");
    }

    public function switchToTab($tabId)
    {
        if (!isset($this->carts[$tabId])) return;

        $this->updateActiveTabFromCurrentProperties();
        $this->activeTabId = $tabId;
        $this->updateCurrentPropertiesFromTab();
        $this->calculateTotals();
        $this->saveCartToCache();
    }

    public function closeTab($tabId)
    {
        if (!isset($this->carts[$tabId])) return;
        if (count($this->carts) <= 1) {
            session()->flash("error", "Tidak dapat menutup tab. Minimal harus ada satu tab.");
            return;
        }

        if (!empty($this->carts[$tabId]["cart"])) {
            $this->showConfirm(
                "Konfirmasi Tutup Tab",
                "Tab ini memiliki item di keranjang. Apakah Anda yakin ingin menutupnya?",
                "confirmCloseTab",
                ["tabId" => $tabId],
            );
            return;
        }

        $this->confirmCloseTab(["tabId" => $tabId]);
    }

    public function confirmCloseTab($params)
    {
        $tabId = $params["tabId"];
        if (!isset($this->carts[$tabId])) return;

        $this->updateActiveTabFromCurrentProperties();
        unset($this->carts[$tabId]);

        if ($this->activeTabId == $tabId) {
            $this->activeTabId = min(array_keys($this->carts));
            $this->updateCurrentPropertiesFromTab();
            $this->calculateTotals();
        }

        $this->saveCartToCache();
        session()->flash("success", "Tab berhasil ditutup!");
    }

    private function updateCurrentPropertiesFromTab()
    {
        if (!isset($this->carts[$this->activeTabId])) return;

        $activeTab = &$this->carts[$this->activeTabId];
        if (!isset($activeTab["cart"])) {
            $activeTab["cart"] = [];
        }

        $this->cart = &$activeTab["cart"];
        $this->supplierName = $activeTab["supplierName"] ?? "";
        $this->supplierPhone = $activeTab["supplierPhone"] ?? "";
        $this->customerName = $activeTab["customerName"] ?? "";
        $this->customerPhone = $activeTab["customerPhone"] ?? "";
        $this->paymentMethod = $activeTab["paymentMethod"] ?? "cash";
        $this->amountPaid = $activeTab["amountPaid"] ?? 0;
        $this->notes = $activeTab["notes"] ?? "";
        $this->paymentNotes = $activeTab["paymentNotes"] ?? "";
        $this->tabName = $activeTab["name"] ?? "Tab " . $this->activeTabId;
    }

    private function updateActiveTabFromCurrentProperties()
    {
        if (!isset($this->carts[$this->activeTabId])) return;
        if (!isset($this->carts[$this->activeTabId]["cart"])) {
            $this->carts[$this->activeTabId]["cart"] = [];
        }

        $this->carts[$this->activeTabId]["cart"] = $this->cart;
        $this->carts[$this->activeTabId]["supplierName"] = $this->supplierName ?? "";
        $this->carts[$this->activeTabId]["supplierPhone"] = $this->supplierPhone ?? "";
        $this->carts[$this->activeTabId]["customerName"] = $this->customerName ?? "";
        $this->carts[$this->activeTabId]["customerPhone"] = $this->customerPhone ?? "";
        $this->carts[$this->activeTabId]["paymentMethod"] = $this->paymentMethod ?? "cash";
        $this->carts[$this->activeTabId]["amountPaid"] = $this->amountPaid ?? 0;
        $this->carts[$this->activeTabId]["notes"] = $this->notes ?? "";
        $this->carts[$this->activeTabId]["paymentNotes"] = $this->paymentNotes ?? "";
    }

    public function getTabItemCount($tabId)
    {
        if (!isset($this->carts[$tabId])) return 0;
        return count($this->carts[$tabId]["cart"]);
    }

    public function getTabTotal($tabId)
    {
        if (!isset($this->carts[$tabId])) return 0;
        $total = 0;
        foreach ($this->carts[$tabId]["cart"] as $item) {
            $total += $item["price"] * $item["quantity"];
        }
        return max(0, $total);
    }

    public function getCartProducts()
    {
        if (empty($this->cart)) return collect();
        $productIds = array_column($this->cart, "product_id");
        return Product::with([
            "unit:id,name,abbreviation",
            "unitScales.unit:id,name,abbreviation",
        ])
            ->whereIn("id", $productIds)
            ->get()
            ->keyBy("id");
    }

    // Receipt Printing Methods
    public function printReceiptThermal($saleId)
    {
        $this->dispatch('print-receipt-thermal', saleId: $saleId);
    }

    public function exportReceiptPNG($saleId)
    {
        $this->dispatch('export-receipt-png', saleId: $saleId);
    }

    public function exportReceiptPDFThermal($saleId)
    {
        $this->dispatch('export-receipt-pdf-thermal', saleId: $saleId);
    }

    public function exportInvoiceA4($saleId)
    {
        $this->dispatch('export-invoice-a4', saleId: $saleId);
    }

    // Infinite scroll / Lazy load methods
    public function loadMore()
    {
        $this->currentPage++;
    }

    public function updatedProductSearch()
    {
        $this->currentPage = 1;
        $this->hasMoreProducts = true;
    }

    public function updatedFilterCategory()
    {
        $this->currentPage = 1;
        $this->hasMoreProducts = true;
    }

    public function updatedFilterStockStatus()
    {
        $this->currentPage = 1;
        $this->hasMoreProducts = true;
    }

    public function updatedSortBy()
    {
        $this->currentPage = 1;
        $this->hasMoreProducts = true;
    }

    public function render()
    {
        $query = Product::where("status", "active")
            ->whereNull("deleted_at")
            ->with([
                "warehouseStocks" => function ($query) {
                    if ($this->warehouseId) {
                        $query->where("warehouse_id", $this->warehouseId);
                    }
                },
            ]);

        if ($this->warehouseId) {
            $query->whereHas("warehouseStocks", function ($q) {
                $q->where("warehouse_id", $this->warehouseId)->where("stock_on_hand", ">", 0);
            });
        }

        // Search filter
        if (!empty($this->productSearch)) {
            $query->where(function ($q) {
                $q->where("name", "like", "%" . $this->productSearch . "%")
                    ->orWhere("sku", "like", "%" . $this->productSearch . "%")
                    ->orWhere("barcode", "like", "%" . $this->productSearch . "%");
            });
        }

        // Category filter (using string category field)
        if (!empty($this->filterCategory)) {
            $query->where("category", $this->filterCategory);
        }

        // Stock status filter
        if ($this->filterStockStatus !== "all" && $this->warehouseId) {
            $query->whereHas("warehouseStocks", function ($q) {
                $q->where("warehouse_id", $this->warehouseId);

                if ($this->filterStockStatus === "available") {
                    $q->where("stock_on_hand", ">", 10);
                } elseif ($this->filterStockStatus === "low") {
                    $q->where("stock_on_hand", "<=", 10)->where("stock_on_hand", ">", 0);
                } elseif ($this->filterStockStatus === "out") {
                    $q->where("stock_on_hand", "<=", 0);
                }
            });
        }

        // Sorting
        switch ($this->sortBy) {
            case "most_purchased":
                // Will sort in collection after fetching
                break;
            case "price_low":
                $query->orderBy("price_retail", "asc");
                break;
            case "price_high":
                $query->orderBy("price_retail", "desc");
                break;
            case "stock":
                // Sort by stock requires joining, so we'll do it in collection
                break;
            case "name":
                $query->orderBy("name", "asc");
                break;
            default:
                // Default to most purchased
                break;
        }

        // For infinite scroll: get paginated products
        $allProducts = $query->get();

        // Sort by most purchased (from sales)
        if ($this->sortBy === "most_purchased") {
            $allProducts = $allProducts->sortByDesc(function ($product) {
                return \App\SaleItem::where('product_id', $product->id)
                    ->sum('qty') ?? 0;
            })->values();
        }

        // Sort by stock if needed
        if ($this->sortBy === "stock" && $this->warehouseId) {
            $allProducts = $allProducts->sortByDesc(function ($product) {
                return \App\ProductWarehouseStock::where('product_id', $product->id)
                    ->where('warehouse_id', $this->warehouseId)
                    ->value('stock_on_hand') ?? 0;
            })->values();
        }

        // Paginate products for lazy loading
        $total = $allProducts->count();
        $products = $allProducts->take($this->currentPage * $this->productsPerPage);
        $this->hasMoreProducts = $products->count() < $total;

        $cartProducts = $this->getCartProducts();

        // Get unique categories from products (string field)
        $categories = Product::where("status", "active")
            ->whereNull("deleted_at")
            ->whereNotNull("category")
            ->where("category", "!=", "")
            ->distinct()
            ->pluck("category")
            ->sort()
            ->values();

        return view("livewire.pos-responsive", [
            "products" => $products,
            "cartProducts" => $cartProducts,
            "selectedWarehouseId" => $this->warehouseId,
            "warehouses" => $this->warehouses,
            "carts" => $this->carts,
            "activeTabId" => $this->activeTabId,
            "categories" => $categories,
        ]);
    }
}
