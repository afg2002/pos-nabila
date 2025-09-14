<?php

namespace App\Livewire;

use Livewire\Component;
use App\Product;
use App\StockMovement;
use App\Domains\User\Models\User;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StockForm extends Component
{
    use AuthorizesRequests;
    #[Validate('required|exists:products,id')]
    public $product_id = '';
    
    #[Validate('required|in:in,out,adjustment')]
    public $movement_type = 'in';
    
    #[Validate('required|numeric|min:1')]
    public $quantity = '';
    
    #[Validate('nullable|string|max:255')]
    public $notes = '';
    
    #[Validate('nullable|string|max:50')]
    public $reason_code = '';
    
    public $showModal = false;
    public $products = [];
    public $selectedProduct = null;
    
    public function mount()
    {
        $this->products = Product::where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
    }
    
    public function updatedProductId($value)
    {
        if ($value) {
            $this->selectedProduct = Product::find($value);
        } else {
            $this->selectedProduct = null;
        }
    }
    
    public function openModal()
    {
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->reset([
            'product_id', 'movement_type', 'quantity', 'notes', 'reason_code'
        ]);
        $this->selectedProduct = null;
    }
    
    public function save()
    {
        $this->validate();
        
        // Check authorization
        $this->authorize('manage', StockMovement::class);
        
        try {
            DB::beginTransaction();
            
            $product = Product::findOrFail($this->product_id);
            
            // Validasi stok untuk movement keluar
            if ($this->movement_type === 'out' && $product->current_stock < $this->quantity) {
                $this->addError('quantity', 'Stok tidak mencukupi. Stok tersedia: ' . $product->current_stock);
                return;
            }
            
            // Hitung perubahan stok
            $stockChange = 0;
            switch ($this->movement_type) {
                case 'in':
                    $stockChange = $this->quantity;
                    break;
                case 'out':
                    $stockChange = -$this->quantity;
                    break;
                case 'adjustment':
                    $stockChange = $this->quantity - $product->current_stock;
                    break;
            }
            
            // Hitung stok sebelum dan sesudah
            $stockBefore = $product->current_stock;
            $stockAfter = $this->movement_type === 'adjustment' ? $this->quantity : $stockBefore + $stockChange;
            
            // Update stok produk
            $product->update(['current_stock' => $stockAfter]);
            
            // Catat movement
            StockMovement::createMovement($this->product_id, $stockChange, strtoupper($this->movement_type), [
                'ref_type' => 'manual',
                'ref_id' => null,
                'note' => $this->notes,
                'reason_code' => $this->reason_code,
                'performed_by' => Auth::id(),
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
            ]);
            
            DB::commit();
            
            session()->flash('message', 'Stok berhasil diperbarui!');
            $this->closeModal();
            $this->dispatch('stock-updated');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        return view('livewire.stock-form');
    }
}
