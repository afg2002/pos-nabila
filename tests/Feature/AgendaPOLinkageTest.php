<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Domains\User\Models\User;
use App\Models\Agenda;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\AgendaManagement;
use App\Livewire\PurchaseOrderForm;

class AgendaPOLinkageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $supplier;
    protected $product;
    protected $warehouse;
    protected $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        // Ensure unit available first
        $this->unit = ProductUnit::factory()->create(['name' => 'Pieces']);

        // Single supplier creation
        $this->supplier = Supplier::factory()->create([
            'name' => 'Test Supplier',
            'email' => 'supplier@test.com',
            'contact_person' => 'John Doe',
        ]);

        // Single warehouse creation
        $this->warehouse = Warehouse::factory()->create([
            'name' => 'Main Warehouse',
            'code' => 'MW001',
            'is_default' => true,
        ]);

        // Single product creation with unit linkage
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST001',
            'unit_id' => $this->unit->id,
        ]);
    }

    /** @test */
    public function it_can_create_agenda_for_purchase_order()
    {
        $this->actingAs($this->user);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'po_number' => 'PO-2024-001',
            'status' => 'pending',
            'total_amount' => 1000000,
            'expected_delivery_date' => now()->addDays(7),
        ]);

        Livewire::test(AgendaManagement::class)
            ->call('openModal')
            ->set('title', 'Follow up PO-2024-001')
            ->set('description', 'Follow up purchase order dengan supplier')
            ->set('agenda_date', now()->addDays(3)->format('Y-m-d'))
            ->set('agenda_time', '10:00')
            ->set('priority', 'high')
            ->set('status', 'pending')
            ->set('related_type', 'purchase_order')
            ->set('related_id', $purchaseOrder->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('refresh');

        $this->assertDatabaseHas('agendas', [
            'title' => 'Follow up PO-2024-001',
            'related_type' => 'purchase_order',
            'related_id' => $purchaseOrder->id,
            'priority' => 'high',
        ]);
    }

    /** @test */
    public function it_can_create_purchase_order_with_items()
    {
        $this->actingAs($this->user);

        Livewire::test(PurchaseOrderForm::class)
            ->call('openModal')
            ->set('supplier_id', $this->supplier->id)
            ->set('warehouse_id', $this->warehouse->id)
            ->set('expected_delivery_date', now()->addDays(7)->format('Y-m-d'))
            ->set('notes', 'Test purchase order')
            ->call('addItem')
            ->set('items.0.product_id', $this->product->id)
            ->set('items.0.quantity', 100)
            ->set('items.0.unit_price', 5000)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('refresh');

        $purchaseOrder = PurchaseOrder::latest()->first();
        
        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'pending',
            'total_amount' => 500000, // 100 * 5000
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $purchaseOrder->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
            'unit_price' => 5000,
        ]);
    }

    /** @test */
    public function it_can_receive_purchase_order_and_update_stock()
    {
        $this->actingAs($this->user);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'pending',
            'total_amount' => 500000,
        ]);

        PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
            'unit_price' => 5000,
        ]);

        // Initial stock should be 0
        $initialStock = WarehouseStock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        
        $this->assertNull($initialStock);

        // Receive the purchase order
        Livewire::test(PurchaseOrderForm::class)
            ->call('edit', $purchaseOrder->id)
            ->set('status', 'received')
            ->set('received_date', now()->format('Y-m-d'))
            ->call('save')
            ->assertHasNoErrors();

        // Check if purchase order status is updated
        $purchaseOrder->refresh();
        $this->assertEquals('received', $purchaseOrder->status);

        // Check if stock was created/updated
        $updatedStock = WarehouseStock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        $this->assertNotNull($updatedStock);
        $this->assertEquals(100, $updatedStock->quantity);

        // Check if stock movement was created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'IN',
            'quantity' => 100,
            'reference_type' => 'purchase_order',
            'reference_id' => $purchaseOrder->id,
        ]);
    }

    /** @test */
    public function it_can_mark_purchase_order_as_paid()
    {
        $this->actingAs($this->user);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'received',
            'total_amount' => 500000,
            'paid_amount' => 0,
        ]);

        Livewire::test(PurchaseOrderForm::class)
            ->call('edit', $purchaseOrder->id)
            ->set('paid_amount', 500000)
            ->set('payment_date', now()->format('Y-m-d'))
            ->set('payment_method', 'cash')
            ->call('save')
            ->assertHasNoErrors();

        $purchaseOrder->refresh();
        $this->assertEquals(500000, $purchaseOrder->paid_amount);
        $this->assertEquals('paid', $purchaseOrder->payment_status);
    }

    /** @test */
    public function it_can_create_agenda_when_po_is_overdue()
    {
        $this->actingAs($this->user);

        // Create overdue purchase order
        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'pending',
            'expected_delivery_date' => now()->subDays(2), // 2 days overdue
        ]);

        // Simulate automatic agenda creation for overdue PO
        Livewire::test(AgendaManagement::class)
            ->call('openModal')
            ->set('title', 'PO Overdue: ' . $purchaseOrder->po_number)
            ->set('description', 'Purchase order sudah melewati tanggal pengiriman yang diharapkan')
            ->set('agenda_date', now()->format('Y-m-d'))
            ->set('agenda_time', '09:00')
            ->set('priority', 'urgent')
            ->set('status', 'pending')
            ->set('related_type', 'purchase_order')
            ->set('related_id', $purchaseOrder->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('agendas', [
            'title' => 'PO Overdue: ' . $purchaseOrder->po_number,
            'priority' => 'urgent',
            'related_type' => 'purchase_order',
            'related_id' => $purchaseOrder->id,
        ]);
    }

    /** @test */
    public function it_can_complete_agenda_when_po_is_received()
    {
        $this->actingAs($this->user);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'pending',
        ]);

        // Create agenda for the PO
        $agenda = Agenda::factory()->create([
            'title' => 'Follow up PO-2024-001',
            'related_type' => 'purchase_order',
            'related_id' => $purchaseOrder->id,
            'status' => 'pending',
        ]);

        // Mark PO as received
        $purchaseOrder->update(['status' => 'received']);

        // Complete the related agenda
        Livewire::test(AgendaManagement::class)
            ->call('edit', $agenda->id)
            ->set('status', 'completed')
            ->set('completion_notes', 'PO sudah diterima dengan lengkap')
            ->call('save')
            ->assertHasNoErrors();

        $agenda->refresh();
        $this->assertEquals('completed', $agenda->status);
        $this->assertEquals('PO sudah diterima dengan lengkap', $agenda->completion_notes);
    }

    /** @test */
    public function it_validates_purchase_order_items()
    {
        $this->actingAs($this->user);

        Livewire::test(PurchaseOrderForm::class)
            ->call('openModal')
            ->set('supplier_id', $this->supplier->id)
            ->set('warehouse_id', $this->warehouse->id)
            ->call('addItem')
            ->call('save')
            ->assertHasErrors([
                'items.0.product_id' => 'required',
                'items.0.quantity' => 'required',
                'items.0.unit_price' => 'required',
            ]);
    }

    /** @test */
    public function it_calculates_purchase_order_total_correctly()
    {
        $this->actingAs($this->user);

        $product2 = Product::factory()->create([
            'name' => 'Test Product 2',
            'sku' => 'TEST002',
            'unit_id' => $this->unit->id,
        ]);

        $component = Livewire::test(PurchaseOrderForm::class)
            ->call('openModal')
            ->set('supplier_id', $this->supplier->id)
            ->set('warehouse_id', $this->warehouse->id)
            ->call('addItem')
            ->set('items.0.product_id', $this->product->id)
            ->set('items.0.quantity', 100)
            ->set('items.0.unit_price', 5000)
            ->call('addItem')
            ->set('items.1.product_id', $product2->id)
            ->set('items.1.quantity', 50)
            ->set('items.1.unit_price', 8000)
            ->call('calculateTotal');

        // Total should be (100 * 5000) + (50 * 8000) = 900000
        $this->assertEquals(900000, $component->get('total_amount'));
    }

    /** @test */
    public function it_can_cancel_purchase_order()
    {
        $this->actingAs($this->user);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'pending',
        ]);

        Livewire::test(PurchaseOrderForm::class)
            ->call('edit', $purchaseOrder->id)
            ->set('status', 'cancelled')
            ->set('cancellation_reason', 'Supplier tidak dapat memenuhi pesanan')
            ->call('save')
            ->assertHasNoErrors();

        $purchaseOrder->refresh();
        $this->assertEquals('cancelled', $purchaseOrder->status);
        $this->assertEquals('Supplier tidak dapat memenuhi pesanan', $purchaseOrder->cancellation_reason);
    }

    /** @test */
    public function it_prevents_receiving_cancelled_purchase_order()
    {
        $this->actingAs($this->user);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'cancelled',
        ]);

        Livewire::test(PurchaseOrderForm::class)
            ->call('edit', $purchaseOrder->id)
            ->set('status', 'received')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    /** @test */
    public function it_can_partially_receive_purchase_order()
    {
        $this->actingAs($this->user);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'pending',
        ]);

        $poItem = PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
            'unit_price' => 5000,
        ]);

        // Partially receive the order
        Livewire::test(PurchaseOrderForm::class)
            ->call('edit', $purchaseOrder->id)
            ->set('items.0.received_quantity', 60) // Only 60 out of 100
            ->set('status', 'partially_received')
            ->call('save')
            ->assertHasNoErrors();

        $poItem->refresh();
        $this->assertEquals(60, $poItem->received_quantity);

        // Check if stock was updated with received quantity
        $stock = WarehouseStock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        $this->assertEquals(60, $stock->quantity);
    }

    /** @test */
    public function it_can_filter_agendas_by_purchase_order()
    {
        $this->actingAs($this->user);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
        ]);

        Agenda::factory()->create([
            'title' => 'PO Related Agenda',
            'related_type' => 'purchase_order',
            'related_id' => $purchaseOrder->id,
        ]);

        Agenda::factory()->create([
            'title' => 'General Agenda',
            'related_type' => null,
            'related_id' => null,
        ]);

        Livewire::test(AgendaManagement::class)
            ->set('filterRelatedType', 'purchase_order')
            ->assertSee('PO Related Agenda')
            ->assertDontSee('General Agenda');
    }

    /** @test */
    public function it_shows_purchase_order_details_in_agenda()
    {
        $this->actingAs($this->user);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'po_number' => 'PO-2024-001',
            'total_amount' => 1000000,
        ]);

        $agenda = Agenda::factory()->create([
            'title' => 'Follow up PO-2024-001',
            'related_type' => 'purchase_order',
            'related_id' => $purchaseOrder->id,
        ]);

        Livewire::test(AgendaManagement::class)
            ->call('viewDetails', $agenda->id)
            ->assertSee('PO-2024-001')
            ->assertSee($this->supplier->name)
            ->assertSee('Rp 1.000.000');
    }
}