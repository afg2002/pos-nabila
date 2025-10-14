# 🔧 Technical Implementation Guide

## 📋 Migration Scripts

### 1. Purchase Order Integration Migration
```php
// database/migrations/2025_10_14_200000_add_purchase_order_integration_to_incoming_goods_agenda.php
Schema::table('incoming_goods_agenda', function (Blueprint $table) {
    $table->boolean('is_purchase_order_generated')->default(false);
    $table->string('po_number')->nullable();
    $table->decimal('remaining_amount', 15, 2)->default(0);
    $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
    $table->index(['is_purchase_order_generated']);
    $table->index(['payment_status', 'payment_due_date']);
});
```

### 2. Batch Expiration Migration
```php
// database/migrations/2025_10_14_200100_add_batch_expiration_to_incoming_goods_agenda.php
Schema::table('incoming_goods_agenda', function (Blueprint $table) {
    $table->string('batch_number')->nullable()->after('supplier_id');
    $table->date('expired_date')->nullable()->after('batch_number');
    $table->index(['batch_number', 'expired_date']);
});
```

### 3. Sales Invoices Table
```php
// database/migrations/2025_10_14_200200_create_sales_invoices_table.php
Schema::create('sales_invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sale_id')->constrained()->onDelete('cascade');
    $table->string('invoice_number')->unique();
    $table->string('customer_name')->nullable();
    $table->string('customer_phone')->nullable();
    $table->decimal('subtotal', 15, 2);
    $table->decimal('tax_amount', 15, 2)->default(0);
    $table->decimal('discount_amount', 15, 2)->default(0);
    $table->decimal('total_amount', 15, 2);
    $table->decimal('paid_amount', 15, 2)->default(0);
    $table->decimal('remaining_amount', 15, 2)->default(0);
    $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
    $table->enum('payment_method', ['cash', 'qr', 'edc', 'transfer'])->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

## 🏗️ Model Relationships

### 1. Enhanced IncomingGoodsAgenda Model
```php
// app/Models/IncomingGoodsAgenda.php
class IncomingGoodsAgenda extends Model
{
    protected $fillable = [
        'supplier_id', 'batch_number', 'expired_date',
        'total_quantity', 'quantity_unit', 'total_purchase_amount',
        'scheduled_date', 'payment_due_date',
        'is_purchase_order_generated', 'po_number',
        'paid_amount', 'remaining_amount', 'payment_status',
        // ... existing fields
    ];

    // Relationships
    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder() {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function batchExpirations() {
        return $this->hasMany(BatchExpiration::class);
    }

    // Methods
    public function generatePurchaseOrderNumber() {
        $prefix = 'PO-' . date('Ym');
        $sequence = static::where('po_number', 'like', $prefix . '%')->count() + 1;
        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function autoGeneratePurchaseOrder() {
        if (!$this->is_purchase_order_generated) {
            $po = PurchaseOrder::create([
                'po_number' => $this->generatePurchaseOrderNumber(),
                'supplier_name' => $this->supplier->name,
                'total_amount' => $this->total_purchase_amount,
                'payment_due_date' => $this->payment_due_date,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'created_by' => auth()->id(),
            ]);

            $this->update([
                'is_purchase_order_generated' => true,
                'po_number' => $po->po_number,
                'purchase_order_id' => $po->id,
            ]);

            return $po;
        }
        return $this->purchaseOrder;
    }
}
```

### 2. SalesInvoice Model
```php
// app/Models/SalesInvoice.php
class SalesInvoice extends Model
{
    protected $fillable = [
        'sale_id', 'invoice_number', 'customer_name', 'customer_phone',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount',
        'paid_amount', 'remaining_amount', 'payment_status', 'payment_method',
        'notes'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    // Relationships
    public function sale() {
        return $this->belongsTo(Sale::class);
    }

    public function payments() {
        return $this->hasMany(InvoicePayment::class);
    }

    // Methods
    public function generateInvoiceNumber() {
        $prefix = 'INV-' . date('Ym');
        $sequence = static::where('invoice_number', 'like', $prefix . '%')->count() + 1;
        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function makePayment($amount, $method, $notes = null) {
        $payment = InvoicePayment::create([
            'invoice_id' => $this->id,
            'amount' => $amount,
            'payment_method' => $method,
            'payment_date' => now(),
            'notes' => $notes,
        ]);

        $this->paid_amount += $amount;
        $this->remaining_amount = $this->total_amount - $this->paid_amount;
        
        if ($this->remaining_amount <= 0) {
            $this->payment_status = 'paid';
            $this->remaining_amount = 0;
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
        }

        $this->save();
        return $payment;
    }
}
```

## 🎨 Livewire Components

### 1. Single Page Agenda Management
```php
// app/Livewire/AgendaManagement.php
class AgendaManagement extends Component
{
    public $activeTab = 'cashflow';
    public $cashflowData = [];
    public $purchaseOrderData = [];

    protected $listeners = [
        'refreshCashflow' => 'loadCashflowData',
        'refreshPurchaseOrder' => 'loadPurchaseOrderData',
    ];

    public function mount()
    {
        $this->loadCashflowData();
        $this->loadPurchaseOrderData();
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function loadCashflowData()
    {
        // Load cashflow data for today
        $this->cashflowData = [
            'today' => CashflowAgenda::whereDate('date', today())->first(),
            'monthly' => CashflowAgenda::whereMonth('date', now()->month)->get(),
            'summary' => $this->calculateCashflowSummary(),
        ];
    }

    public function loadPurchaseOrderData()
    {
        // Load purchase order data
        $this->purchaseOrderData = [
            'pending' => IncomingGoodsAgenda::where('status', 'scheduled')->get(),
            'received' => IncomingGoodsAgenda::where('status', 'received')->get(),
            'overdue' => IncomingGoodsAgenda::where('payment_due_date', '<', today())
                ->where('payment_status', '!=', 'paid')->get(),
        ];
    }

    public function calculateCashflowSummary()
    {
        $monthly = CashflowAgenda::whereMonth('date', now()->month)->get();
        
        return [
            'total_omset' => $monthly->sum('total_omset'),
            'total_ecer' => $monthly->sum('total_ecer'),
            'total_grosir' => $monthly->sum('total_grosir'),
            'total_cash' => $monthly->sum('grosir_cash_hari_ini'),
            'total_qr' => $monthly->sum('qr_payment_amount'),
            'total_edc' => $monthly->sum('edc_payment_amount'),
        ];
    }

    public function render()
    {
        return view('livewire.agenda-management');
    }
}
```

### 2. Enhanced POS Kasir
```php
// app/Livewire/PosKasirEnhanced.php
class PosKasirEnhanced extends Component
{
    public $cart = [];
    public $customer = [];
    public $invoices = [];
    public $currentInvoice = null;
    public $paymentMethods = ['cash', 'qr', 'edc', 'transfer'];

    public function addInvoice()
    {
        $invoice = [
            'id' => uniqid(),
            'customer_name' => '',
            'customer_phone' => '',
            'items' => [],
            'subtotal' => 0,
            'total' => 0,
            'payment_status' => 'unpaid',
        ];

        $this->invoices[] = $invoice;
        $this->currentInvoice = count($this->invoices) - 1;
    }

    public function printInvoice($index)
    {
        $invoice = $this->invoices[$index];
        
        // Generate invoice data for thermal printing
        $printData = [
            'invoice_number' => $this->generateInvoiceNumber(),
            'customer_name' => $invoice['customer_name'],
            'items' => $invoice['items'],
            'subtotal' => $invoice['subtotal'],
            'total' => $invoice['total'],
            'payment_method' => $invoice['payment_method'] ?? 'cash',
            'date' => now()->format('d/m/Y H:i'),
        ];

        // Send to thermal printer
        $this->sendToThermalPrinter($printData);

        // Save invoice to database
        $this->saveInvoiceToDatabase($invoice, $printData);
    }

    private function sendToThermalPrinter($data)
    {
        // Implementation for Kassen DT360 thermal printer
        $printer = new ThermalPrintService();
        $printer->printInvoice($data);
    }

    private function saveInvoiceToDatabase($invoiceData, $printData)
    {
        $sale = Sale::create([
            'customer_name' => $invoiceData['customer_name'],
            'total_amount' => $invoiceData['total'],
            'payment_method' => $invoiceData['payment_method'] ?? 'cash',
            'payment_status' => $invoiceData['payment_status'],
            'created_by' => auth()->id(),
        ]);

        SalesInvoice::create([
            'sale_id' => $sale->id,
            'invoice_number' => $printData['invoice_number'],
            'customer_name' => $invoiceData['customer_name'],
            'total_amount' => $invoiceData['total'],
            'payment_status' => $invoiceData['payment_status'],
            'payment_method' => $invoiceData['payment_method'] ?? 'cash',
        ]);
    }
}
```

## 🖨️ Thermal Print Service

### 1. Thermal Print Service
```php
// app/Services/ThermalPrintService.php
class ThermalPrintService
{
    public function printInvoice($data)
    {
        // Format for 80x100mm thermal printer
        $content = $this->formatInvoiceContent($data);
        
        // Send to printer (implementation depends on printer driver)
        $this->sendToPrinter($content);
    }

    private function formatInvoiceContent($data)
    {
        $lines = [];
        
        // Header
        $lines[] = str_repeat("=", 32);
        $lines[] = "       INVOICE";
        $lines[] = str_repeat("=", 32);
        $lines[] = "No: " . $data['invoice_number'];
        $lines[] = "Tanggal: " . $data['date'];
        $lines[] = "Kasir: " . auth()->user()->name;
        $lines[] = "";
        
        // Customer
        if ($data['customer_name']) {
            $lines[] = "Pelanggan: " . $data['customer_name'];
            $lines[] = "";
        }
        
        // Items
        $lines[] = str_repeat("-", 32);
        $lines[] = str_pad("Item", 20) . str_pad("Qty", 5) . str_pad("Total", 7, " ", STR_PAD_LEFT);
        $lines[] = str_repeat("-", 32);
        
        foreach ($data['items'] as $item) {
            $lines[] = str_pad(substr($item['name'], 0, 20), 20) . 
                       str_pad($item['quantity'], 5) . 
                       str_pad(number_format($item['total'], 0), 7, " ", STR_PAD_LEFT);
        }
        
        // Total
        $lines[] = str_repeat("-", 32);
        $lines[] = str_pad("Subtotal:", 25, " ", STR_PAD_LEFT) . 
                   str_pad(number_format($data['subtotal'], 0), 7, " ", STR_PAD_LEFT);
        $lines[] = str_pad("TOTAL:", 25, " ", STR_PAD_LEFT) . 
                   str_pad(number_format($data['total'], 0), 7, " ", STR_PAD_LEFT);
        $lines[] = str_repeat("=", 32);
        
        // Payment
        $lines[] = "Pembayaran: " . strtoupper($data['payment_method']);
        $lines[] = "";
        $lines[] = "       TERIMA KASIH";
        $lines[] = str_repeat("=", 32);
        
        return implode("\n", $lines);
    }

    private function sendToPrinter($content)
    {
        // Implementation depends on printer connection
        // Could be USB, Bluetooth, or Network printer
        
        // Example for USB printer on Windows
        // exec("echo \"" . addslashes($content) . "\" > LPT1");
        
        // Example for network printer
        // exec("echo \"" . addslashes($content) . "\" | nc printer_ip 9100");
        
        // For now, we'll just return the content for testing
        return $content;
    }
}
```

## 🔄 Integration Services

### 1. Agenda Service
```php
// app/Services/AgendaService.php
class AgendaService
{
    public function createPurchaseOrderAgenda($data)
    {
        $agenda = IncomingGoodsAgenda::create([
            'supplier_id' => $data['supplier_id'],
            'batch_number' => $data['batch_number'] ?? null,
            'expired_date' => $data['expired_date'] ?? null,
            'total_quantity' => $data['total_quantity'],
            'quantity_unit' => $data['quantity_unit'],
            'total_purchase_amount' => $data['total_purchase_amount'],
            'scheduled_date' => $data['scheduled_date'],
            'payment_due_date' => $data['payment_due_date'],
            'payment_status' => 'unpaid',
            'created_by' => auth()->id(),
        ]);

        // Auto-generate PO
        $agenda->autoGeneratePurchaseOrder();

        // Create batch expiration record
        if ($data['expired_date']) {
            BatchExpiration::create([
                'incoming_goods_agenda_id' => $agenda->id,
                'batch_number' => $data['batch_number'] ?? 'BATCH-' . $agenda->id,
                'expired_date' => $data['expired_date'],
                'quantity' => $data['total_quantity'],
                'remaining_quantity' => $data['total_quantity'],
            ]);
        }

        return $agenda;
    }

    public function updateCashflowFromSale($sale)
    {
        $today = now()->format('Y-m-d');
        $cashflow = CashflowAgenda::firstOrCreate([
            'date' => $today,
            'capital_tracking_id' => $this->getDefaultCapitalTracking(),
        ]);

        // Update cashflow based on sale payment methods
        if ($sale->payment_method === 'cash') {
            $cashflow->increment('grosir_cash_hari_ini', $sale->total_amount);
        } elseif ($sale->payment_method === 'qr') {
            $cashflow->increment('qr_payment_amount', $sale->total_amount);
        } elseif ($sale->payment_method === 'edc') {
            $cashflow->increment('edc_payment_amount', $sale->total_amount);
        }

        // Update total omset
        $cashflow->total_omset = $cashflow->total_ecer + $cashflow->total_grosir;
        $cashflow->save();

        // Update cash ledger
        CashLedger::create([
            'date' => $today,
            'type' => 'income',
            'category' => 'sales',
            'amount' => $sale->total_amount,
            'payment_method' => $sale->payment_method,
            'description' => 'Penjualan - ' . $sale->customer_name,
            'reference_id' => $sale->id,
            'reference_type' => 'sale',
            'created_by' => auth()->id(),
        ]);
    }

    private function getDefaultCapitalTracking()
    {
        return CapitalTracking::where('is_active', true)->first()->id ?? 1;
    }
}
```

## 📱 Frontend Implementation

### 1. Single Page Template
```blade
<!-- resources/views/agenda-management/index.blade.php -->
@extends('layouts.app')

@section('title', 'Agenda Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Agenda Management</h1>
                    <p class="text-gray-600">Kelola cashflow dan purchase order dalam satu tempat</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="showTab('cashflow')" id="cashflow-tab" 
                        class="tab-button text-blue-600 border-b-2 border-blue-600 py-4 px-1 text-sm font-medium">
                    <i class="fas fa-money-bill-wave mr-2"></i>Agenda Cashflow
                </button>
                <button onclick="showTab('purchase-order')" id="purchase-order-tab" 
                        class="tab-button text-gray-500 border-b-2 border-transparent py-4 px-1 text-sm font-medium hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-box mr-2"></i>Agenda Purchase Order
                </button>
            </nav>
        </div>
        
        <!-- Tab Content -->
        <div class="p-6">
            <div id="cashflow-content" class="tab-content">
                @livewire('cashflow-agenda-tab')
            </div>
            <div id="purchase-order-content" class="tab-content hidden">
                @livewire('purchase-order-agenda-tab')
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showTab(tabName) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600');
        button.classList.add('text-gray-500', 'border-b-2', 'border-transparent', 'hover:text-gray-700', 'hover:border-gray-300');
    });
    
    // Show selected content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Activate selected tab
    const activeTab = document.getElementById(tabName + '-tab');
    activeTab.classList.remove('text-gray-500', 'border-b-2', 'border-transparent', 'hover:text-gray-700', 'hover:border-gray-300');
    activeTab.classList.add('text-blue-600', 'border-b-2', 'border-blue-600');
}
</script>
@endpush
@endsection
```

## 🧪 Testing Strategy

### 1. Unit Tests
```php
// tests/Feature/AgendaManagementTest.php
class AgendaManagementTest extends TestCase
{
    public function test_can_create_purchase_order_agenda()
    {
        $supplier = Supplier::factory()->create();
        
        $data = [
            'supplier_id' => $supplier->id,
            'batch_number' => 'BATCH001',
            'expired_date' => now()->addMonths(6),
            'total_quantity' => 100,
            'quantity_unit' => 'pcs',
            'total_purchase_amount' => 1000000,
            'scheduled_date' => now()->addDays(7),
            'payment_due_date' => now()->addDays(14),
        ];

        $agenda = (new AgendaService)->createPurchaseOrderAgenda($data);

        $this->assertInstanceOf(IncomingGoodsAgenda::class, $agenda);
        $this->assertTrue($agenda->is_purchase_order_generated);
        $this->assertNotNull($agenda->po_number);
        $this->assertEquals('unpaid', $agenda->payment_status);
    }

    public function test_can_create_multiple_sales_invoices()
    {
        $sale = Sale::factory()->create(['total_amount' => 200000]);
        
        $invoice1 = SalesInvoice::create([
            'sale_id' => $sale->id,
            'invoice_number' => 'INV-202410001',
            'customer_name' => 'Customer 1',
            'total_amount' => 100000,
            'payment_status' => 'paid',
        ]);

        $invoice2 = SalesInvoice::create([
            'sale_id' => $sale->id,
            'invoice_number' => 'INV-202410002',
            'customer_name' => 'Customer 2',
            'total_amount' => 100000,
            'payment_status' => 'partial',
            'paid_amount' => 50000,
            'remaining_amount' => 50000,
        ]);

        $this->assertEquals(2, $sale->invoices()->count());
        $this->assertEquals(150000, $sale->invoices()->sum('paid_amount'));
    }
}
```

## 📈 Performance Considerations

### 1. Database Indexing
```sql
-- Add indexes for better performance
CREATE INDEX idx_incoming_goods_supplier_date ON incoming_goods_agenda(supplier_id, scheduled_date);
CREATE INDEX idx_cashflow_date_capital ON cashflow_agenda(date, capital_tracking_id);
CREATE INDEX idx_sales_invoices_sale_id ON sales_invoices(sale_id);
CREATE INDEX idx_invoice_payments_invoice_id ON invoice_payments(invoice_id);
```

### 2. Caching Strategy
```php
// Cache frequently accessed data
$monthlyCashflow = Cache::remember('monthly_cashflow_' . now()->format('Ym'), 3600, function () {
    return CashflowAgenda::whereMonth('date', now()->month)->get();
});

$pendingAgendas = Cache::remember('pending_agendas', 1800, function () {
    return IncomingGoodsAgenda::where('status', 'scheduled')->get();
});
```

## 🚨 Error Handling

### 1. Validation Rules
```php
// Custom validation for batch expiration
'expired_date' => 'required|date|after:scheduled_date',
'total_purchase_amount' => 'required|numeric|min:1000',
'batch_number' => 'required_if:expired_date,not_null|string|max:50',
```

### 2. Exception Handling
```php
try {
    $agenda = $this->createPurchaseOrderAgenda($data);
} catch (ValidationException $e) {
    return response()->json(['errors' => $e->errors()], 422);
} catch (Exception $e) {
    Log::error('Failed to create agenda: ' . $e->getMessage());
    return response()->json(['message' => 'Failed to create agenda'], 500);
}
```

This technical guide provides comprehensive implementation details for all the enhanced features requested by the client.