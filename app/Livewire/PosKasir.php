<?php

namespace App\Livewire;

use Livewire\Component;
use App\Product;
use App\Sale;
use App\SaleItem;
use App\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;

class PosKasir extends Component
{
    public $barcode = '';
    public $productSearch = '';
    public $cart = [];
    public $customerName = '';
    public $customerPhone = '';
    public $paymentMethod = 'cash';
    public $amountPaid = 0;
    public $discount = 0;
    public $discountType = 'amount'; // amount or percentage
    public $notes = '';
    
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
        'customerName' => 'nullable|string|max:255',
        'customerPhone' => 'nullable|string|max:20',
        'paymentMethod' => 'required|in:cash,card,transfer',
        'amountPaid' => 'required|numeric|min:0',
        'discount' => 'nullable|numeric|min:0',
        'discountType' => 'required|in:amount,percentage',
        'notes' => 'nullable|string|max:500'
    ];
    
    public function mount()
    {
        // Check if user has permission to access POS
        if (!auth()->user()->hasPermission('pos.access')) {
            abort(403, 'You do not have permission to access POS system.');
        }
        
        $this->calculateTotals();
    }
    
    public function updatedBarcode()
    {
        if (!empty($this->barcode)) {
            // Clean and validate barcode format
            $this->barcode = $this->cleanBarcode($this->barcode);
            
            if ($this->isValidBarcodeFormat($this->barcode)) {
                $this->addProductByBarcode();
            } else {
                session()->flash('error', 'Format barcode tidak valid: "' . $this->barcode . '"');
                $this->barcode = '';
            }
        }
    }
    
    public function updatedDiscount()
    {
        $this->calculateTotals();
    }
    
    public function updatedDiscountType()
    {
        $this->calculateTotals();
    }
    
    public function updatedAmountPaid()
    {
        $this->change = max(0, $this->amountPaid - $this->total);
    }
    
    public function addProductByBarcode()
    {
        if (empty($this->barcode)) {
            return;
        }
        
        $product = Product::where('barcode', $this->barcode)
                         ->where('is_active', true)
                         ->first();
        
        if (!$product) {
            // Try to find by SKU as fallback
            $product = Product::where('sku', $this->barcode)
                             ->where('is_active', true)
                             ->first();
        }
        
        if (!$product) {
            session()->flash('error', 'Produk dengan barcode/SKU "' . $this->barcode . '" tidak ditemukan!');
            $this->barcode = '';
            return;
        }
        
        if ($product->current_stock <= 0) {
            session()->flash('error', 'Stok produk "' . $product->name . '" habis!');
            $this->barcode = '';
            return;
        }
        
        $this->addToCart($product->id);
        $this->barcode = '';
        
        // Log successful barcode scan
        \App\Shared\Services\LoggerService::logUserAction(
            'barcode_scan',
            'product',
            $product->id,
            ['barcode' => $this->barcode, 'product_name' => $product->name]
        );
    }
    
    public function addToCart($productId)
    {
        $product = Product::find($productId);
        
        if (!$product || !$product->is_active) {
            session()->flash('error', 'Produk tidak ditemukan atau tidak aktif!');
            return;
        }
        
        $cartKey = 'product_' . $productId;
        
        if (isset($this->cart[$cartKey])) {
            // Check stock availability
            if ($this->cart[$cartKey]['quantity'] >= $product->current_stock) {
                session()->flash('error', 'Stok tidak mencukupi untuk produk "' . $product->name . '"!');
                return;
            }
            $this->cart[$cartKey]['quantity']++;
        } else {
            $this->cart[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'price' => $product->price_retail,
                'base_cost' => $product->base_cost,
                'quantity' => 1,
                'available_stock' => $product->current_stock
            ];
        }
        
        $this->calculateTotals();
        session()->flash('success', 'Produk "' . $product->name . '" ditambahkan ke keranjang!');
    }
    
    public function updateQuantity($cartKey, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($cartKey);
            return;
        }
        
        if (isset($this->cart[$cartKey])) {
            $availableStock = $this->cart[$cartKey]['available_stock'];
            
            if ($quantity > $availableStock) {
                session()->flash('error', 'Stok tidak mencukupi! Stok tersedia: ' . $availableStock);
                return;
            }
            
            $this->cart[$cartKey]['quantity'] = $quantity;
            $this->calculateTotals();
        }
    }
    
    public function updatePrice($cartKey, $price)
    {
        if (isset($this->cart[$cartKey])) {
            $baseCost = $this->cart[$cartKey]['base_cost'];
            $minPrice = $baseCost * 1.1; // Minimum 10% margin
            
            if ($price < $minPrice) {
                session()->flash('error', 'Harga tidak boleh kurang dari Rp ' . number_format($minPrice, 0, ',', '.') . ' (margin minimum 10%)');
                return;
            }
            
            $this->cart[$cartKey]['price'] = $price;
            $this->calculateTotals();
        }
    }
    
    public function removeFromCart($cartKey)
    {
        if (isset($this->cart[$cartKey])) {
            unset($this->cart[$cartKey]);
            $this->calculateTotals();
            session()->flash('success', 'Produk dihapus dari keranjang!');
        }
    }
    
    public function clearCart()
    {
        $this->cart = [];
        $this->calculateTotals();
        session()->flash('success', 'Keranjang dikosongkan!');
    }
    
    public function calculateTotals()
    {
        $this->subtotal = 0;
        
        foreach ($this->cart as $item) {
            $this->subtotal += $item['price'] * $item['quantity'];
        }
        
        // Calculate discount
        if ($this->discountType === 'percentage') {
            $this->discountAmount = ($this->subtotal * $this->discount) / 100;
        } else {
            $this->discountAmount = $this->discount;
        }
        
        $this->total = max(0, $this->subtotal - $this->discountAmount);
        $this->change = max(0, $this->amountPaid - $this->total);
    }
    
    public function openCheckout()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang masih kosong!');
            return;
        }
        
        $this->amountPaid = $this->total;
        $this->showCheckoutModal = true;
    }
    
    public function closeCheckout()
    {
        $this->showCheckoutModal = false;
    }
    
    public function processCheckout()
    {
        // Check if user has permission to process sales
        if (!auth()->user()->hasPermission('pos.sell')) {
            session()->flash('error', 'Anda tidak memiliki izin untuk memproses penjualan!');
            return;
        }
        
        $this->validate();
        
        if ($this->amountPaid < $this->total) {
            session()->flash('error', 'Jumlah bayar tidak mencukupi!');
            return;
        }
        
        try {
            DB::beginTransaction();
            
            // Create sale record
            $sale = Sale::create([
                'sale_number' => 'POS-' . date('Ymd') . '-' . str_pad(Sale::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
                'customer_name' => $this->customerName,
                'customer_phone' => $this->customerPhone,
                'subtotal' => $this->subtotal,
                'discount_amount' => $this->discountAmount,
                'final_total' => $this->total,
                'payment_method' => $this->paymentMethod,
                'amount_paid' => $this->amountPaid,
                'change_amount' => $this->change,
                'notes' => $this->notes,
                'cashier_id' => Auth::id(),
                // created_at akan otomatis diisi oleh Laravel
            ]);
            
            // Create sale items and update stock
            foreach ($this->cart as $item) {
                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity']
                ]);
                
                // Create stock movement (outgoing)
                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'type' => 'OUT',
                    'qty' => -$item['quantity'], // Negatif untuk OUT
                    'ref_type' => 'sale',
                    'ref_id' => $sale->id,
                    'note' => 'Penjualan #' . $sale->sale_number,
                    'performed_by' => Auth::id()
                ]);
                
                // Update product current_stock (sudah negatif di qty, jadi langsung tambahkan)
                $product = Product::find($item['product_id']);
                $product->increment('current_stock', -$item['quantity']);
            }
            
            DB::commit();
            
            $this->lastSale = $sale->load('items.product');
            $this->resetForm();
            $this->showCheckoutModal = false;
            $this->showReceiptModal = true;
            
            session()->flash('success', 'Transaksi berhasil! Nomor: ' . $sale->sale_number);
            
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
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
        $this->cart = [];
        $this->customerName = '';
        $this->customerPhone = '';
        $this->paymentMethod = 'cash';
        $this->amountPaid = 0;
        $this->discount = 0;
        $this->discountType = 'amount';
        $this->notes = '';
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
     * Get barcode scan statistics for current session
     */
    public function getBarcodeStats()
    {
        return [
            'total_scans' => session('barcode_scans', 0),
            'successful_scans' => session('successful_scans', 0),
            'failed_scans' => session('failed_scans', 0)
        ];
    }
    
    public function render()
    {
        $query = Product::where('is_active', true)
                       ->where('current_stock', '>', 0);
        
        // Apply search filter if productSearch is not empty
        if (!empty($this->productSearch)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->productSearch . '%')
                  ->orWhere('sku', 'like', '%' . $this->productSearch . '%')
                  ->orWhere('barcode', 'like', '%' . $this->productSearch . '%');
            });
        }
        
        $products = $query->orderBy('name')->get();
        
        return view('livewire.pos-kasir', compact('products'));
    }
}