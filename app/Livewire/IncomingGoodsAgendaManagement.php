<?php

namespace App\Livewire;

use App\CapitalTracking;
use App\IncomingGoodsAgenda;
use App\ProductUnit;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class IncomingGoodsAgendaManagement extends Component
{
    use WithPagination;

    // Form properties
    public $supplier_name = '';

    public $goods_name = '';

    public $description = '';

    public $quantity = '';

    public $unit = '';

    public $unit_id = '';

    public $unit_price = '';

    public $total_amount = '';

    public $scheduled_date = '';

    public $payment_due_date = '';

    public $notes = '';

    public $contact_person = '';

    public $phone_number = '';

    public $capital_tracking_id = '';

    public $warehouse_id = '';

    public $product_id = '';

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

    public $paymentCapitalId = '';

    // Calendar and filters
    public $currentDate;

    public $selectedDate = '';

    public $filterStatus = '';

    public $filterMonth = '';

    public $viewMode = 'calendar'; // calendar or list

    public $totalAgendas = 0;

    public $search = '';

    protected $rules = [
        'supplier_name' => 'required|string|max:255',
        'goods_name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
        'quantity' => 'required|integer|min:1',
        'unit' => 'nullable|string|max:50',
        'unit_id' => 'required|exists:product_units,id',
        'unit_price' => 'required|numeric|min:0',
        'scheduled_date' => 'required|date|after_or_equal:today',
        'payment_due_date' => 'required|date|after_or_equal:scheduled_date',
        'notes' => 'nullable|string|max:1000',
        'contact_person' => 'nullable|string|max:255',
        'phone_number' => 'nullable|string|max:20',
        'capital_tracking_id' => 'required|exists:capital_tracking,id',
        'warehouse_id' => 'nullable|exists:warehouses,id',
        'product_id' => 'nullable|exists:products,id',
    ];

    protected $messages = [
        'supplier_name.required' => 'Nama supplier harus diisi.',
        'goods_name.required' => 'Nama barang harus diisi.',
        'quantity.required' => 'Jumlah barang harus diisi.',
        'quantity.integer' => 'Jumlah barang harus berupa angka.',
        'quantity.min' => 'Jumlah barang minimal 1.',
        'unit_id.required' => 'Satuan barang harus dipilih.',
        'unit_id.exists' => 'Satuan barang tidak valid.',
        'unit_price.required' => 'Harga per unit harus diisi.',
        'unit_price.numeric' => 'Harga per unit harus berupa angka.',
        'unit_price.min' => 'Harga per unit tidak boleh negatif.',
        'scheduled_date.required' => 'Tanggal jadwal barang masuk harus diisi.',
        'scheduled_date.after_or_equal' => 'Tanggal jadwal tidak boleh kurang dari hari ini.',
        'payment_due_date.required' => 'Tanggal jatuh tempo pembayaran harus diisi.',
        'payment_due_date.after_or_equal' => 'Tanggal jatuh tempo tidak boleh kurang dari tanggal jadwal.',
        'capital_tracking_id.required' => 'Modal usaha harus dipilih.',
        'capital_tracking_id.exists' => 'Modal usaha tidak valid.',
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
        $query = IncomingGoodsAgenda::with('capitalTracking');

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('supplier_name', 'like', '%'.$this->search.'%')
                    ->orWhere('goods_name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('contact_person', 'like', '%'.$this->search.'%');
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

        $capitalTrackings = CapitalTracking::where('is_active', true)->get();
        $productUnits = ProductUnit::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $warehouses = \App\Warehouse::orderBy('name')->get();
        $products = \App\Product::where('is_active', true)->orderBy('name')->get();

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
            'capitalTrackings' => $capitalTrackings,
            'productUnits' => $productUnits,
            'warehouses' => $warehouses,
            'products' => $products,
            'scheduledTodayCount' => $scheduledTodayCount,
            'paymentDueTodayCount' => $paymentDueTodayCount,
            'overduePaymentCount' => $overduePaymentCount,
            'calendarData' => $calendarData,
            'editingId' => $this->editingId,
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
        $this->paymentCapitalId = null;
        $this->paymentNotes = '';
    }

    public function resetForm()
    {
        $this->supplier_name = '';
        $this->goods_name = '';
        $this->description = '';
        $this->quantity = '';
        $this->unit = '';
        $this->unit_id = '';
        $this->unit_price = '';
        $this->total_amount = '';
        $this->scheduled_date = '';
        $this->payment_due_date = '';
        $this->notes = '';
        $this->contact_person = '';
        $this->phone_number = '';
        $this->capital_tracking_id = '';
        $this->warehouse_id = '';
        $this->product_id = '';
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->validate();
        $this->calculateTotal();

        // Get unit name from selected ProductUnit
        if ($this->unit_id) {
            $productUnit = ProductUnit::find($this->unit_id);
            if ($productUnit) {
                $this->unit = $productUnit->name;
            }
        }

        try {
            if ($this->editingId) {
                $agenda = IncomingGoodsAgenda::findOrFail($this->editingId);
                $agenda->update([
                    'supplier_name' => $this->supplier_name,
                    'goods_name' => $this->goods_name,
                    'description' => $this->description,
                    'quantity' => $this->quantity,
                    'unit' => $this->unit,
                    'unit_id' => $this->unit_id,
                    'unit_price' => $this->unit_price,
                    'total_amount' => $this->total_amount,
                    'scheduled_date' => $this->scheduled_date,
                    'payment_due_date' => $this->payment_due_date,
                    'notes' => $this->notes,
                    'contact_person' => $this->contact_person,
                    'phone_number' => $this->phone_number,
                    'capital_tracking_id' => $this->capital_tracking_id,
                    'warehouse_id' => $this->warehouse_id ?: null,
                    'product_id' => $this->product_id ?: null,
                ]);
                session()->flash('message', 'Agenda barang masuk berhasil diperbarui.');
            } else {
                IncomingGoodsAgenda::create([
                    'supplier_name' => $this->supplier_name,
                    'goods_name' => $this->goods_name,
                    'description' => $this->description,
                    'quantity' => $this->quantity,
                    'unit' => $this->unit,
                    'unit_id' => $this->unit_id,
                    'unit_price' => $this->unit_price,
                    'total_amount' => $this->total_amount,
                    'scheduled_date' => $this->scheduled_date,
                    'payment_due_date' => $this->payment_due_date,
                    'notes' => $this->notes,
                    'contact_person' => $this->contact_person,
                    'phone_number' => $this->phone_number,
                    'capital_tracking_id' => $this->capital_tracking_id,
                    'warehouse_id' => $this->warehouse_id ?: null,
                    'product_id' => $this->product_id ?: null,
                ]);
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
        $this->scheduled_date = $agenda->scheduled_date->format('Y-m-d');
        $this->payment_due_date = $agenda->payment_due_date->format('Y-m-d');
        $this->notes = $agenda->notes;
        $this->contact_person = $agenda->contact_person;
        $this->phone_number = $agenda->phone_number;
        $this->capital_tracking_id = $agenda->capital_tracking_id;
        $this->warehouse_id = $agenda->warehouse_id;
        $this->product_id = $agenda->product_id;
        $this->showModal = true;
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
            'paymentCapitalId' => 'required|exists:capital_tracking,id',
        ]);

        try {
            $capitalTracking = CapitalTracking::findOrFail($this->paymentCapitalId);

            // Check if capital tracking has enough balance
            if ($capitalTracking->current_amount < $this->paymentAmount) {
                session()->flash('error', 'Saldo modal tidak mencukupi.');
                return;
            }

            // Use the new makePayment method from the model
            $this->selectedAgenda->makePayment($this->paymentAmount, $this->paymentNotes);

            // Update capital tracking - deduct payment amount
            $capitalTracking->current_amount -= $this->paymentAmount;
            $capitalTracking->save();

            // Create capital tracking transaction record
            CapitalTracking::create([
                'business_modal_id' => $capitalTracking->business_modal_id,
                'transaction_type' => 'expense',
                'amount' => $this->paymentAmount,
                'description' => 'Pembayaran agenda barang masuk: '.$this->selectedAgenda->supplier_name.' - '.$this->selectedAgenda->item_name,
                'transaction_date' => now(),
                'current_amount' => $capitalTracking->current_amount,
                'notes' => $this->paymentNotes,
            ]);

            session()->flash('message', 'Pembayaran berhasil diproses. Saldo modal berkurang sebesar Rp '.number_format($this->paymentAmount, 0, ',', '.'));
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
}
