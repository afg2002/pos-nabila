<?php

namespace App\Livewire;

use App\Models\IncomingGoodsAgenda;
use App\Models\ProductUnit;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Warehouse;

class IncomingGoodsAgendaManagement extends Component
{
    use WithPagination;

    // Form properties
    public $supplier_name = '';
    public $supplier_id = '';

    public $goods_name = '';

    public $description = '';

    public $quantity = '';
    public $total_quantity = '';

    public $unit = '';
    public $quantity_unit = '';
    public $unit_id = '';

    public $unit_price = '';

    public $total_amount = '';
    public $total_purchase_amount = '';

    public $scheduled_date = '';

    public $payment_due_date = '';

    public $notes = '';

    public $contact_person = '';

    public $phone_number = '';

    public $batch_number = '';

    public $expired_date = '';

    public $warehouse_id = '';

    public $product_id = '';

    public $input_mode = 'simplified'; // 'simplified' or 'detailed'

    public $editingId = null;

    // Modal states
    public $showModal = false;

    public $showPaymentModal = false;

    public $showDeleteModal = false;

    public $selectedAgenda = null;

    public $confirmingDelete = false;

    public $deleteId = null;

    public $paymentId = null;

    public $paymentAmount = '';

    public $paymentNotes = '';


    // Calendar and filters
    public $currentDate;

    public $selectedDate = '';

    public $filterStatus = '';

    public $filterMonth = '';

    public $viewMode = 'calendar'; // calendar or list

    public $totalAgendas = 0;

    public $search = '';

    // Supplier search UI state
    public $supplierSearch = '';
    public $showSupplierResults = false;
    public $supplierSearchResults = [];
    public $showSupplierDropdown = false;

    // Quantity unit search UI state (simplified mode)
    public $quantityUnitSearch = '';
    public $showQuantityUnitResults = false;
    public $quantityUnitSearchResults = [];
    public $showQuantityUnitDropdown = false;

    protected $rules = [
        // Simplified mode rules
        'supplier_id' => 'required_if:input_mode,simplified|exists:suppliers,id',
        'supplier_name' => 'required_if:input_mode,detailed|string|max:255',
        'total_quantity' => 'required_if:input_mode,simplified|numeric|min:0.01',
        'quantity_unit' => 'required_if:input_mode,simplified|string|max:50',
        'total_purchase_amount' => 'required_if:input_mode,simplified|numeric|min:0.01',
        
        // Detailed mode rules
        'goods_name' => 'required_if:input_mode,detailed|string|max:255',
        'quantity' => 'required_if:input_mode,detailed|integer|min:1',
        'unit_id' => 'required_if:input_mode,detailed|exists:product_units,id',
        'unit_price' => 'required_if:input_mode,detailed|numeric|min:0',
        
        // Common rules
        'description' => 'nullable|string|max:500',
        'scheduled_date' => 'required|date|after_or_equal:today',
        'payment_due_date' => 'required|date|after_or_equal:scheduled_date',
        'batch_number' => 'nullable|string|max:50|unique:incoming_goods_agenda,batch_number',
        'expired_date' => 'nullable|date|after:scheduled_date',
        'notes' => 'nullable|string|max:1000',
        'contact_person' => 'nullable|string|max:255',
        'phone_number' => 'nullable|string|max:20',
        'warehouse_id' => 'nullable|exists:warehouses,id',
        'product_id' => 'nullable|exists:products,id',
    ];

    protected function rules()
    {
        $batchRule = $this->editingId
            ? Rule::unique('incoming_goods_agenda', 'batch_number')->ignore($this->editingId)
            : Rule::unique('incoming_goods_agenda', 'batch_number');

        return [
            // Simplified mode rules
            'supplier_id' => 'required_if:input_mode,simplified|exists:suppliers,id',
            'supplier_name' => 'required_if:input_mode,detailed|string|max:255',
            'total_quantity' => 'required_if:input_mode,simplified|numeric|min:0.01',
            'quantity_unit' => 'required_if:input_mode,simplified|string|max:50',
            'total_purchase_amount' => 'required_if:input_mode,simplified|numeric|min:0.01',
            
            // Detailed mode rules
            'goods_name' => 'required_if:input_mode,detailed|string|max:255',
            'quantity' => 'required_if:input_mode,detailed|integer|min:1',
            'unit_id' => 'required_if:input_mode,detailed|exists:product_units,id',
            'unit_price' => 'required_if:input_mode,detailed|numeric|min:0',
            
            // Common rules
            'description' => 'nullable|string|max:500',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'payment_due_date' => 'required|date|after_or_equal:scheduled_date',
            'batch_number' => ['nullable','string','max:50', $batchRule],
            'expired_date' => 'nullable|date|after:scheduled_date',
            'notes' => 'nullable|string|max:1000',
            'contact_person' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'product_id' => 'nullable|exists:products,id',
        ];
    }

    protected $messages = [
        // Simplified mode messages
        'supplier_id.required_if' => 'Supplier harus dipilih.',
        'supplier_id.exists' => 'Supplier tidak valid.',
        'total_quantity.required_if' => 'Total jumlah barang harus diisi.',
        'total_quantity.numeric' => 'Total jumlah barang harus berupa angka.',
        'total_quantity.min' => 'Total jumlah barang minimal 0.01.',
        'quantity_unit.required_if' => 'Satuan barang harus diisi.',
        'total_purchase_amount.required_if' => 'Jumlah total belanja harus diisi.',
        'total_purchase_amount.numeric' => 'Jumlah total belanja harus berupa angka.',
        'total_purchase_amount.min' => 'Jumlah total belanja minimal 0.01.',
        
        // Detailed mode messages
        'supplier_name.required_if' => 'Nama supplier harus diisi.',
        'goods_name.required_if' => 'Nama barang harus diisi.',
        'quantity.required_if' => 'Jumlah barang harus diisi.',
        'quantity.integer' => 'Jumlah barang harus berupa angka.',
        'quantity.min' => 'Jumlah barang minimal 1.',
        'unit_id.required_if' => 'Satuan barang harus dipilih.',
        'unit_id.exists' => 'Satuan barang tidak valid.',
        'unit_price.required_if' => 'Harga per unit harus diisi.',
        'unit_price.numeric' => 'Harga per unit harus berupa angka.',
        'unit_price.min' => 'Harga per unit tidak boleh negatif.',
        
        // Common messages
        'scheduled_date.required' => 'Tanggal jadwal barang masuk harus diisi.',
        'scheduled_date.after_or_equal' => 'Tanggal jadwal tidak boleh kurang dari hari ini.',
        'payment_due_date.required' => 'Tanggal jatuh tempo pembayaran harus diisi.',
        'payment_due_date.after_or_equal' => 'Tanggal jatuh tempo tidak boleh kurang dari tanggal jadwal.',
    ];

    public function mount()
    {
        $this->currentDate = now()->format('Y-m-d');
        $this->selectedDate = $this->currentDate;
        $this->filterMonth = now()->format('Y-m');
    }

    public function updatedQuantity()
    {
        $this->calculateTotal();
    }

    public function updatedUnitPrice()
    {
        $this->calculateTotal();
    }

    public function updatedUnitId()
    {
        if ($this->unit_id) {
            $productUnit = ProductUnit::find($this->unit_id);
            if ($productUnit) {
                $this->unit = $productUnit->name;
            }
        }
    }

    private function calculateTotal()
    {
        if ($this->quantity && $this->unit_price) {
            $this->total_amount = $this->quantity * $this->unit_price;
        }
    }

    public function render()
    {
        $query = IncomingGoodsAgenda::with(['supplier']);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('supplier_name', 'like', '%'.$this->search.'%')
                    ->orWhere('goods_name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('contact_person', 'like', '%'.$this->search.'%')
                    ->orWhereHas('supplier', function($subQuery) {
                        $subQuery->where('name', 'like', '%'.$this->search.'%');
                    });
            });
        }

        // Apply filters
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterMonth) {
            $query->whereYear('scheduled_date', substr($this->filterMonth, 0, 4))
                ->whereMonth('scheduled_date', substr($this->filterMonth, 5, 2));
        }

        if ($this->selectedDate && $this->viewMode === 'calendar') {
            $query->whereDate('scheduled_date', $this->selectedDate);
        }

        $agendas = $query->orderBy('scheduled_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $productUnits = ProductUnit::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        // Get statistics
        $this->totalAgendas = IncomingGoodsAgenda::count();

        $scheduledTodayCount = IncomingGoodsAgenda::whereDate('scheduled_date', today())
            ->where('status', 'scheduled')
            ->count();

        $paymentDueTodayCount = IncomingGoodsAgenda::whereDate('payment_due_date', today())
            ->whereIn('status', ['scheduled', 'received'])
            ->count();

        $overduePaymentCount = IncomingGoodsAgenda::where('payment_due_date', '<', today())
            ->whereIn('status', ['scheduled', 'received'])
            ->count();

        // Calendar data for current month
        $calendarData = [];
        if ($this->viewMode === 'calendar') {
            $currentMonth = Carbon::parse($this->filterMonth.'-01');
            $startOfMonth = $currentMonth->copy()->startOfMonth();
            $endOfMonth = $currentMonth->copy()->endOfMonth();

            // Get agendas for the month
            $agendas = IncomingGoodsAgenda::whereBetween('scheduled_date', [$startOfMonth, $endOfMonth])
                ->get()
                ->groupBy(function ($item) {
                    return $item->scheduled_date->format('Y-m-d');
                });

            // Generate calendar grid like AgendaCalendar
            $startDate = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
            $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

            $current = $startDate->copy();
            while ($current <= $endDate) {
                $dateStr = $current->format('Y-m-d');
                $calendarData[] = [
                    'date' => $dateStr,
                    'day' => $current->day,
                    'isCurrentMonth' => $current->month === $currentMonth->month,
                    'isToday' => $current->isToday(),
                    'agendas' => $agendas->get($dateStr, collect()),
                ];
                $current->addDay();
            }
        }

        return view('livewire.incoming-goods-agenda-management', [
            'agendas' => $agendas,
            'productUnits' => $productUnits,
            'warehouses' => $warehouses,
            'products' => $products,
            'suppliers' => $suppliers,
            'scheduledTodayCount' => $scheduledTodayCount,
            'paymentDueTodayCount' => $paymentDueTodayCount,
            'overduePaymentCount' => $overduePaymentCount,
            'calendarData' => $calendarData,
            'editingId' => $this->editingId,
            // Ensure these Livewire state variables are explicitly available in Blade to avoid undefined variable errors
            'showSupplierResults' => $this->showSupplierResults,
            'showSupplierDropdown' => $this->showSupplierDropdown,
            'supplierSearchResults' => $this->supplierSearchResults,
            'supplierSearch' => $this->supplierSearch,
            'showQuantityUnitResults' => $this->showQuantityUnitResults,
            'showQuantityUnitDropdown' => $this->showQuantityUnitDropdown,
            'quantityUnitSearchResults' => $this->quantityUnitSearchResults,
            'quantityUnitSearch' => $this->quantityUnitSearch,
        ]);
    }

    public function switchView($mode)
    {
        $this->viewMode = $mode;
        $this->resetPage();
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->openModal($date); // Automatically open modal when date is selected
        $this->resetPage();
    }

    public function openModal($date = null)
    {
        $this->resetForm();
        if ($date) {
            $this->scheduled_date = $date;
            $this->payment_due_date = Carbon::parse($date)->addDays(7)->format('Y-m-d');
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->selectedAgenda = null;
        $this->paymentNotes = '';
    }

    public function resetForm()
    {
        $this->supplier_name = '';
        $this->supplier_id = '';
        $this->goods_name = '';
        $this->description = '';
        $this->quantity = '';
        $this->total_quantity = '';
        $this->unit = '';
        $this->quantity_unit = '';
        $this->unit_id = '';
        $this->unit_price = '';
        $this->total_amount = '';
        $this->total_purchase_amount = '';
        $this->scheduled_date = '';
        $this->payment_due_date = '';
        $this->batch_number = '';
        $this->expired_date = '';
        $this->notes = '';
        $this->contact_person = '';
        $this->phone_number = '';
        $this->warehouse_id = '';
        $this->product_id = '';
        $this->input_mode = 'simplified';
        $this->editingId = null;

        // Clear supplier search UI
        $this->supplierSearch = '';
        $this->supplierSearchResults = [];
        $this->showSupplierDropdown = false;
        $this->showSupplierResults = false;

        // Clear quantity unit search UI
        $this->quantityUnitSearch = '';
        $this->quantityUnitSearchResults = [];
        $this->showQuantityUnitDropdown = false;
        $this->showQuantityUnitResults = false;

        $this->resetErrorBag();
    }

    public function save()
    {
        // Normalize empty string fields to null for correct 'nullable' behavior
        if ($this->batch_number === '') {
            $this->batch_number = null;
        }

        // Auto-detect input mode if user filled detailed fields without switching
        $autoDetailed = (!empty($this->goods_name))
            || (!empty($this->unit_id))
            || ((string)$this->unit_price !== '' && (float)$this->unit_price > 0)
            || ((string)$this->quantity !== '' && (float)$this->quantity > 0);
        $autoSimplified = ((string)$this->total_purchase_amount !== '' && (float)$this->total_purchase_amount > 0)
            || ((string)$this->total_quantity !== '' && (float)$this->total_quantity > 0)
            || (!empty($this->quantity_unit));
        if ($this->input_mode !== 'detailed' && $autoDetailed) {
            $this->input_mode = 'detailed';
        } elseif ($this->input_mode !== 'simplified' && $autoSimplified && !$autoDetailed) {
            $this->input_mode = 'simplified';
        }

        $this->validate();

        // For detailed mode, calculate total
        if ($this->input_mode === 'detailed') {
            $this->calculateTotal();
        }

        try {
            $data = [
                'scheduled_date' => $this->scheduled_date,
                'payment_due_date' => $this->payment_due_date,
                'batch_number' => $this->batch_number,
                'expired_date' => $this->expired_date,
                'notes' => $this->notes,
                'warehouse_id' => $this->warehouse_id ?: null,
                'product_id' => $this->product_id ?: null,
                'input_mode' => $this->input_mode,
            ];

            if ($this->input_mode === 'simplified') {
                // Simplified mode data
                $data['supplier_id'] = $this->supplier_id;
                $data['supplier_name'] = $this->supplier_name; // Will be auto-populated by model
                $data['total_quantity'] = $this->total_quantity;
                $data['quantity_unit'] = $this->quantity_unit;
                $data['total_purchase_amount'] = $this->total_purchase_amount;
                $data['goods_name'] = 'Barang Various'; // Default for simplified mode
                $data['description'] = 'Input sederhana - total barang';

                // Clear detailed-only fields
                $data['quantity'] = null;
                $data['unit'] = null;
                $data['unit_id'] = null;
                $data['unit_price'] = null;
                $data['total_amount'] = null;
            } else {
                // Detailed mode data
                $data['supplier_id'] = $this->supplier_id;
                $data['supplier_name'] = $this->supplier_name;
                $data['goods_name'] = $this->goods_name;
                $data['description'] = $this->description;
                $data['quantity'] = $this->quantity;
                $data['unit'] = $this->unit;
                $data['unit_id'] = $this->unit_id;
                $data['unit_price'] = $this->unit_price;
                $data['total_amount'] = $this->total_amount;
                $data['contact_person'] = $this->contact_person;
                $data['phone_number'] = $this->phone_number;

                // Clear simplified-only fields to prevent is_simplified from remaining true after switching modes
                $data['total_purchase_amount'] = null;
                $data['total_quantity'] = null;
                $data['quantity_unit'] = null;

                // Get unit name from selected ProductUnit
                if ($this->unit_id) {
                    $productUnit = ProductUnit::find($this->unit_id);
                    if ($productUnit) {
                        $data['unit'] = $productUnit->name;
                    }
                }
            }

            if ($this->editingId) {
                $agenda = IncomingGoodsAgenda::findOrFail($this->editingId);
                $agenda->update($data);
                session()->flash('message', 'Agenda barang masuk berhasil diperbarui.');
            } else {
                $data['created_by'] = auth()->id();
                IncomingGoodsAgenda::create($data);
                session()->flash('message', 'Agenda barang masuk berhasil ditambahkan.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $agenda = IncomingGoodsAgenda::findOrFail($id);
        $this->editingId = $id;
        
        // Determine input mode based on whether it's simplified
        $this->input_mode = $agenda->is_simplified ? 'simplified' : 'detailed';
        
        if ($this->input_mode === 'simplified') {
            $this->supplier_id = $agenda->supplier_id;
            $this->supplier_name = $agenda->effective_supplier_name;
            $this->total_quantity = $agenda->total_quantity;
            $this->quantity_unit = $agenda->quantity_unit;
            $this->total_purchase_amount = $agenda->total_purchase_amount;
        } else {
            $this->supplier_name = $agenda->supplier_name;
            $this->goods_name = $agenda->goods_name;
            $this->description = $agenda->description;
            $this->quantity = $agenda->quantity;
            $this->unit = $agenda->unit;
            $this->unit_id = $agenda->unit_id;

            // If unit_id exists, get unit name from productUnit relation
            if ($agenda->unit_id && $agenda->productUnit) {
                $this->unit = $agenda->productUnit->name;
            }
            $this->unit_price = $agenda->unit_price;
            $this->total_amount = $agenda->total_amount;
            $this->contact_person = $agenda->contact_person;
            $this->phone_number = $agenda->phone_number;
        }
        
        // Prefill supplier & unit search UI
        $this->supplierSearch = $this->input_mode === 'simplified'
            ? ($agenda->effective_supplier_name ?? '')
            : ($agenda->supplier_name ?? '');
        $this->showSupplierDropdown = false;
        $this->showSupplierResults = false;

        $this->quantityUnitSearch = $this->input_mode === 'simplified'
            ? ($agenda->quantity_unit ?? '')
            : (($agenda->productUnit->name ?? ''));

        $this->scheduled_date = $agenda->scheduled_date
            ? (\is_object($agenda->scheduled_date) && method_exists($agenda->scheduled_date, 'format')
                ? $agenda->scheduled_date->format('Y-m-d')
                : \Carbon\Carbon::parse($agenda->scheduled_date)->format('Y-m-d'))
            : '';

        $this->payment_due_date = $agenda->payment_due_date
            ? (\is_object($agenda->payment_due_date) && method_exists($agenda->payment_due_date, 'format')
                ? $agenda->payment_due_date->format('Y-m-d')
                : \Carbon\Carbon::parse($agenda->payment_due_date)->format('Y-m-d'))
            : '';

        $this->batch_number = $agenda->batch_number ?? '';

        $this->expired_date = $agenda->expired_date
            ? (\is_object($agenda->expired_date) && method_exists($agenda->expired_date, 'format')
                ? $agenda->expired_date->format('Y-m-d')
                : \Carbon\Carbon::parse($agenda->expired_date)->format('Y-m-d'))
            : '';

        $this->notes = $agenda->notes;
        $this->warehouse_id = $agenda->warehouse_id;
        $this->product_id = $agenda->product_id;
        
        // Debug: log prefilled values to help diagnose UI not showing
        logger()->info('[IncomingGoodsAgendaManagement@edit] Prefill values', [
            'editingId' => $this->editingId,
            'input_mode' => $this->input_mode,
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier_name,
            'quantity' => $this->quantity,
            'total_quantity' => $this->total_quantity,
            'unit' => $this->unit,
            'quantity_unit' => $this->quantity_unit,
            'unit_id' => $this->unit_id,
            'unit_price' => $this->unit_price,
            'total_amount' => $this->total_amount,
            'total_purchase_amount' => $this->total_purchase_amount,
            'scheduled_date' => $this->scheduled_date,
            'payment_due_date' => $this->payment_due_date,
            'batch_number' => $this->batch_number,
            'expired_date' => $this->expired_date,
        ]);
        
        $this->showModal = true;
    }

    public function switchInputMode($mode)
    {
        $this->input_mode = $mode;
        $this->resetErrorBag();
    }

    public function markAsReceived($id)
    {
        try {
            $agenda = IncomingGoodsAgenda::findOrFail($id);
            $agenda->markAsReceived();
            session()->flash('message', 'Barang berhasil ditandai sebagai diterima.');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function openPaymentModal($id)
    {
        $this->selectedAgenda = IncomingGoodsAgenda::findOrFail($id);
        $this->paymentAmount = $this->selectedAgenda->remaining_amount;
        $this->showPaymentModal = true;
    }

    public function processPayment()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
        ]);

        try {
            // Use the new makePayment method from the model
            $this->selectedAgenda->makePayment($this->paymentAmount, $this->paymentNotes);

            session()->flash('message', 'Pembayaran berhasil diproses sebesar Rp '.number_format($this->paymentAmount, 0, ',', '.'));
            $this->closePaymentModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function delete()
    {
        try {
            IncomingGoodsAgenda::findOrFail($this->deleteId)->delete();
            session()->flash('message', 'Agenda berhasil dihapus.');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
        }

        $this->closeDeleteModal();
    }

    public function previousMonth()
    {
        $currentMonth = Carbon::parse($this->filterMonth.'-01');
        $this->filterMonth = $currentMonth->subMonth()->format('Y-m');
        $this->resetPage();
    }

    public function nextMonth()
    {
        $currentMonth = Carbon::parse($this->filterMonth.'-01');
        $this->filterMonth = $currentMonth->addMonth()->format('Y-m');
        $this->resetPage();
    }

    // Supplier search handlers
    public function updatedSupplierSearch($value)
    {
        $this->supplierSearch = $value;
        $this->showSupplierDropdown = false;
        $this->showSupplierResults = true;

        $search = trim($value ?? '');
        if ($search === '' || strlen($search) < 2) {
            $this->supplierSearchResults = [];
            return;
        }

        $this->supplierSearchResults = Supplier::where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->all();
    }

    public function toggleSupplierDropdown()
    {
        // Show dropdown listing all suppliers when no search text
        $this->showSupplierDropdown = !$this->showSupplierDropdown;
        if ($this->showSupplierDropdown) {
            $this->showSupplierResults = false;
        }
    }

    public function clearSupplierSearch()
    {
        $this->supplierSearch = '';
        $this->supplierSearchResults = [];
        $this->showSupplierDropdown = false;
        $this->showSupplierResults = false;
    }

    public function selectSupplier($supplierId)
    {
        $supplier = Supplier::find($supplierId);
        if ($supplier) {
            $this->supplier_id = $supplier->id;
            $this->supplier_name = $supplier->name ?? '';
            $this->supplierSearch = $supplier->name ?? '';
        }
        $this->showSupplierDropdown = false;
        $this->showSupplierResults = false;
    }

    // Quantity unit search handlers (simplified mode)
    public function updatedQuantityUnitSearch($value)
    {
        $this->quantityUnitSearch = $value;
        $this->showQuantityUnitDropdown = false;
        $this->showQuantityUnitResults = true;

        $search = trim($value ?? '');
        if ($search === '' || strlen($search) < 1) {
            $this->quantityUnitSearchResults = [];
            return;
        }

        $this->quantityUnitSearchResults = ProductUnit::where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('abbreviation', 'like', "%$search%");
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->all();
    }

    public function toggleQuantityUnitDropdown()
    {
        // Show dropdown listing all product units when no search text
        $this->showQuantityUnitDropdown = !$this->showQuantityUnitDropdown;
        if ($this->showQuantityUnitDropdown) {
            $this->showQuantityUnitResults = false;
        }
    }

    public function clearQuantityUnitSearch()
    {
        $this->quantityUnitSearch = '';
        $this->quantityUnitSearchResults = [];
        $this->showQuantityUnitDropdown = false;
        $this->showQuantityUnitResults = false;
    }

    public function selectQuantityUnit($unitId)
    {
        $unit = ProductUnit::find($unitId);
        if ($unit) {
            // Simplified mode uses plain text unit name
            $this->quantity_unit = $unit->name ?? '';
            // Detailed mode also sets unit_id
            $this->unit_id = $unit->id;
            $this->quantityUnitSearch = $unit->name ?? '';
        }
        $this->showQuantityUnitDropdown = false;
        $this->showQuantityUnitResults = false;
    }
}
