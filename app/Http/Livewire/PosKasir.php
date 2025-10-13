<?php

namespace App\Http\Livewire;

use App\Product;
use App\Sale;
use App\SaleItem;
use App\Warehouse;
use Livewire\Component;

class PosKasir extends Component
{
    public $warehouseId;
    public $pricingTier = 'retail';
    public $barcode;
    public $searchQuery = '';
    public $cart = [];
    public $subtotal = 0;
    public $discount = 0;
    public $discountType = 'amount';
    public $total = 0;
    public $paymentMethod = 'cash';
    public $paymentNotes = '';
    public $amountPaid = 0;
    public $change = 0;
    public $showCheckoutModal = false;

    protected $listeners = ['productSelected' => 'addToCart'];

    public function mount()
    {
        $defaultWarehouse = Warehouse::getDefault();
        if ($defaultWarehouse) {
            $this->warehouseId = $defaultWarehouse->id;
        }
    }

    public function render()
    {
        $warehouses = Warehouse::all();
        
        $products = collect([]);
        
        if ($this->warehouseId) {
            $productsQuery = Product::query()
                ->where('status', 'active');
                
            if (!empty($this->searchQuery)) {
                $productsQuery->where(function($query) {
                    $query->where('name', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('barcode', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('sku', 'like', '%' . $this->searchQuery . '%');
                });
            }
            
            $products = $productsQuery->take(20)->get();
        }
        
        return view('livewire.pos-kasir', [
            'warehouses' => $warehouses,
            'products' => $products,
        ]);
    }

    public function updatedBarcode()
    {
        if (empty($this->barcode)) {
            return;
        }

        $product = Product::where('barcode', $this->barcode)
            ->orWhere('sku', $this->barcode)
            ->first();

        if (!$product) {
            session()->flash('error', 'Produk tidak ditemukan');
            return;
        }

        $this->addProductToCart($product);
        $this->barcode = '';
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        if (!$product) {
            session()->flash('error', 'Produk tidak ditemukan');
            return;
        }

        $this->addProductToCart($product);
    }

    public function addProductToCart($product)
    {
        // Cek stok di gudang yang dipilih
        if ($this->warehouseId) {
            $stock = $product->getWarehouseStock($this->warehouseId);
            $currentQty = 0;
            $key = 'product_' . $product->id;
            
            if (isset($this->cart[$key])) {
                $currentQty = $this->cart[$key]['quantity'];
            }
            
            if ($stock <= $currentQty) {
                session()->flash('error', 'Stok produk tidak mencukupi');
                return;
            }
        }

        $price = $product->getPriceByType($this->pricingTier);
        $key = 'product_' . $product->id;

        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity'] += 1;
        } else {
            $this->cart[$key] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $price,
                'quantity' => 1,
            ];
        }

        $this->calculateTotals();
    }

    public function incrementItem($key)
    {
        if (isset($this->cart[$key])) {
            $product = Product::find($this->cart[$key]['id']);
            
            if ($this->warehouseId && $product) {
                $stock = $product->getWarehouseStock($this->warehouseId);
                if ($stock <= $this->cart[$key]['quantity']) {
                    session()->flash('error', 'Stok produk tidak mencukupi');
                    return;
                }
            }
            
            $this->cart[$key]['quantity'] += 1;
            $this->calculateTotals();
        }
    }

    public function decrementItem($key)
    {
        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity'] -= 1;
            
            if ($this->cart[$key]['quantity'] <= 0) {
                $this->removeItem($key);
            } else {
                $this->calculateTotals();
            }
        }
    }

    public function removeItem($key)
    {
        if (isset($this->cart[$key])) {
            unset($this->cart[$key]);
            $this->calculateTotals();
        }
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;
        foreach ($this->cart as $item) {
            $this->subtotal += $item['price'] * $item['quantity'];
        }

        $discountAmount = 0;
        if ($this->discountType === 'amount') {
            $discountAmount = $this->discount;
        } else {
            $discountAmount = $this->subtotal * ($this->discount / 100);
        }

        $this->total = max(0, $this->subtotal - $discountAmount);
        $this->calculateChange();
    }

    public function calculateChange()
    {
        $this->change = max(0, $this->amountPaid - $this->total);
    }

    public function updatedAmountPaid()
    {
        $this->calculateChange();
    }

    public function updatedDiscount()
    {
        $this->calculateTotals();
    }

    public function updatedDiscountType()
    {
        $this->calculateTotals();
    }

    public function updatedWarehouseId()
    {
        // Refresh product availability based on warehouse
    }

    public function updatedPricingTier()
    {
        // Update prices in cart based on new pricing tier
        foreach ($this->cart as $key => $item) {
            $product = Product::find($item['id']);
            if ($product) {
                $this->cart[$key]['price'] = $product->getPriceByType($this->pricingTier);
            }
        }
        $this->calculateTotals();
    }

    public function processCheckout()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang belanja kosong');
            return;
        }

        if ($this->total <= 0) {
            session()->flash('error', 'Total harus lebih dari 0');
            return;
        }

        if ($this->amountPaid < $this->total) {
            session()->flash('error', 'Jumlah pembayaran kurang dari total');
            return;
        }

        $this->showCheckoutModal = true;
    }

    public function completeTransaction()
    {
        // Validasi data
        if (empty($this->cart) || $this->total <= 0 || $this->amountPaid < $this->total || !$this->warehouseId) {
            session()->flash('error', 'Data transaksi tidak valid');
            $this->showCheckoutModal = false;
            return;
        }

        try {
            // Buat transaksi penjualan
            $sale = new Sale();
            $sale->sale_number = 'SALE-' . date('YmdHis');
            $sale->cashier_id = auth()->id();
            $sale->subtotal = $this->subtotal;
            $sale->discount_total = $this->subtotal - $this->total;
            $sale->final_total = $this->total;
            $sale->payment_method = $this->paymentMethod;
            $sale->payment_notes = $this->paymentNotes;
            $sale->status = 'completed';
            $sale->save();

            // Simpan item penjualan
            foreach ($this->cart as $item) {
                $saleItem = new SaleItem();
                $saleItem->sale_id = $sale->id;
                $saleItem->product_id = $item['id'];
                $saleItem->quantity = $item['quantity'];
                $saleItem->price = $item['price'];
                $saleItem->subtotal = $item['price'] * $item['quantity'];
                $saleItem->warehouse_id = $this->warehouseId;
                $saleItem->save();

                // Update stok produk
                $product = Product::find($item['id']);
                if ($product) {
                    $product->decrementStock($this->warehouseId, $item['quantity']);
                }
            }

            // Reset keranjang
            $this->reset(['cart', 'subtotal', 'discount', 'total', 'amountPaid', 'change', 'paymentNotes']);
            $this->showCheckoutModal = false;

            session()->flash('success', 'Transaksi berhasil disimpan');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
            $this->showCheckoutModal = false;
        }
    }
}