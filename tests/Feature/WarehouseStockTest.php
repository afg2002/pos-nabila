<?php

namespace Tests\Feature;

use App\Domains\User\Models\User;
use App\Product;
use App\ProductWarehouseStock;
use App\Warehouse;
use App\StockMovement;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class WarehouseStockTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $user;
    protected $warehouse1;
    protected $warehouse2;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set test database configuration
        config(['database.connections.mysql.database' => 'pos_nabila_test']);

        // Create test user with permissions
        $this->user = User::factory()->create();
        
        // Create role with inventory permissions
        $role = \App\Domains\Role\Models\Role::create([
            'name' => 'Test Admin',
            'display_name' => 'Test Administrator',
            'description' => 'Test role with inventory permissions',
            'is_active' => true,
        ]);
        
        // Create permissions
        $managePermission = \App\Domains\Permission\Models\Permission::firstOrCreate([
            'name' => 'inventory.manage',
            'display_name' => 'Manage Inventory',
            'description' => 'Manage inventory',
            'is_active' => true,
        ]);
        
        $createPermission = \App\Domains\Permission\Models\Permission::firstOrCreate([
            'name' => 'inventory.create',
            'display_name' => 'Create Inventory',
            'description' => 'Create inventory',
            'is_active' => true,
        ]);
        
        // Attach permissions to role
        $role->permissions()->attach([$managePermission->id, $createPermission->id]);
        
        // Assign role to user
        $this->user->assignRole($role);
        
        $this->actingAs($this->user);

        // Create warehouse using factory
        $this->warehouse1 = Warehouse::factory()->create([
            'name' => 'Main Store',
            'type' => 'store',
            'address' => 'Main Address',
            'is_default' => true,
        ]);

        $this->warehouse2 = Warehouse::factory()->create([
            'name' => 'Test Warehouse 2',
            'type' => 'store',
            'address' => 'Test Address 2',
        ]);

        // Create test product
        $this->product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST001',
            'barcode' => '1234567890',
            'category' => 'Test Category',
            'unit_id' => 1, // Default unit
            'base_cost' => 8000,
            'price_retail' => 10000,
            'price_grosir' => 9000,
            'min_margin_pct' => 20.00,
            'default_price_type' => 'retail',
            'status' => 'active',
            'current_stock' => 0,
        ]);
    }

    /** @test */
    public function it_can_create_stock_in_movement_for_specific_warehouse()
    {
        // Create initial stock record
        $stockRecord = ProductWarehouseStock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse1->id,
            'stock_on_hand' => 0,
            'reserved_stock' => 0,
            'safety_stock' => 0,
        ]);

        $component = Livewire::test('stock-form')
            ->set('product_id', $this->product->id)
            ->set('warehouse_id', $this->warehouse1->id)
            ->set('movement_type', 'in')
            ->set('quantity', 50)
            ->set('notes', 'Initial stock')
            ->call('save');

        // Assert stock movement was created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse1->id,
            'type' => 'IN',
            'qty' => 50,
            'note' => 'Initial stock',
        ]);

        // Assert warehouse stock was updated
        $stockRecord->refresh();
        $this->assertEquals(50, $stockRecord->stock_on_hand);

        $component->assertHasNoErrors()
            ->assertDispatched('stock-updated');
    }

    /** @test */
    public function it_can_create_stock_out_movement_with_sufficient_stock()
    {
        // Create initial stock
        $stockRecord = ProductWarehouseStock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse1->id,
            'stock_on_hand' => 100,
            'reserved_stock' => 0,
            'safety_stock' => 0,
        ]);

        $component = Livewire::test('stock-form')
            ->set('product_id', $this->product->id)
            ->set('warehouse_id', $this->warehouse1->id)
            ->set('movement_type', 'out')
            ->set('quantity', 30)
            ->set('notes', 'Stock out test')
            ->call('save');

        // Assert stock movement was created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse1->id,
            'type' => 'OUT',
            'qty' => -30,
            'note' => 'Stock out test',
        ]);

        // Assert warehouse stock was updated
        $stockRecord->refresh();
        $this->assertEquals(70, $stockRecord->stock_on_hand);

        $component->assertHasNoErrors();
    }

    /** @test */
    public function it_prevents_stock_out_when_insufficient_stock()
    {
        // Create initial stock with low quantity
        ProductWarehouseStock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse1->id,
            'stock_on_hand' => 10,
            'reserved_stock' => 0,
            'safety_stock' => 0,
        ]);

        $component = Livewire::test('stock-form')
            ->set('product_id', $this->product->id)
            ->set('warehouse_id', $this->warehouse1->id)
            ->set('movement_type', 'out')
            ->set('quantity', 50) // More than available
            ->set('notes', 'Should fail')
            ->call('save');

        // Assert no stock movement was created
        $this->assertDatabaseMissing('stock_movements', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse1->id,
            'type' => 'OUT',
            'qty' => -50,
        ]);

        $component->assertHasErrors(['quantity']);
    }

    /** @test */
    public function it_can_create_stock_adjustment_movement()
    {
        // Create initial stock
        $stockRecord = ProductWarehouseStock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse1->id,
            'stock_on_hand' => 75,
            'reserved_stock' => 0,
            'safety_stock' => 0,
        ]);

        $component = Livewire::test('stock-form')
            ->set('product_id', $this->product->id)
            ->set('warehouse_id', $this->warehouse1->id)
            ->set('movement_type', 'adjustment')
            ->set('quantity', 100) // Adjust to 100
            ->set('notes', 'Stock adjustment')
            ->set('reason_code', 'RECOUNT')
            ->call('save');

        // Assert stock movement was created with correct adjustment
        $movement = StockMovement::where([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse1->id,
            'type' => 'ADJ', // Updated to match database enum
        ])->first();

        $this->assertNotNull($movement);
        $this->assertEquals(25, $movement->qty); // 100 - 75 = 25
        $this->assertEquals('Stock adjustment', $movement->note);
        $this->assertEquals('RECOUNT', $movement->reason_code);

        // Assert warehouse stock was updated to exact amount
        $stockRecord->refresh();
        $this->assertEquals(100, $stockRecord->stock_on_hand);
    }

    /** @test */
    public function it_tracks_stock_separately_per_warehouse()
    {
        // Create stock records for both warehouses
        $stock1 = ProductWarehouseStock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse1->id,
            'stock_on_hand' => 0,
            'reserved_stock' => 0,
            'safety_stock' => 0,
        ]);

        $stock2 = ProductWarehouseStock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse2->id,
            'stock_on_hand' => 0,
            'reserved_stock' => 0,
            'safety_stock' => 0,
        ]);

        // Add stock to warehouse 1
        Livewire::test('stock-form')
            ->set('product_id', $this->product->id)
            ->set('warehouse_id', $this->warehouse1->id)
            ->set('movement_type', 'in')
            ->set('quantity', 50)
            ->call('save');

        // Add different stock to warehouse 2
        Livewire::test('stock-form')
            ->set('product_id', $this->product->id)
            ->set('warehouse_id', $this->warehouse2->id)
            ->set('movement_type', 'in')
            ->set('quantity', 30)
            ->call('save');

        // Assert stocks are tracked separately
        $stock1->refresh();
        $stock2->refresh();

        $this->assertEquals(50, $stock1->stock_on_hand);
        $this->assertEquals(30, $stock2->stock_on_hand);

        // Assert separate stock movements were created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse1->id,
            'qty' => 50,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse2->id,
            'qty' => 30,
        ]);
    }

    /** @test */
    public function it_updates_current_warehouse_stock_display_when_warehouse_changes()
    {
        // Create different stock levels for each warehouse
        ProductWarehouseStock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse1->id,
            'stock_on_hand' => 100,
            'reserved_stock' => 0,
            'safety_stock' => 0,
        ]);

        ProductWarehouseStock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse2->id,
            'stock_on_hand' => 50,
            'reserved_stock' => 0,
            'safety_stock' => 0,
        ]);

        $component = Livewire::test('stock-form')
            ->set('product_id', $this->product->id)
            ->set('warehouse_id', $this->warehouse1->id);

        // Assert warehouse 1 stock is displayed
        $this->assertEquals(100, $component->get('currentWarehouseStock'));

        // Change to warehouse 2
        $component->set('warehouse_id', $this->warehouse2->id);

        // Assert warehouse 2 stock is now displayed
        $this->assertEquals(50, $component->get('currentWarehouseStock'));
    }

    /** @test */
    public function it_creates_warehouse_stock_record_if_not_exists()
    {
        // Don't create initial stock record
        $component = Livewire::test('stock-form')
            ->set('product_id', $this->product->id)
            ->set('warehouse_id', $this->warehouse1->id)
            ->set('movement_type', 'in')
            ->set('quantity', 25)
            ->call('save');

        // Assert warehouse stock record was created
        $this->assertDatabaseHas('product_warehouse_stock', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse1->id,
            'stock_on_hand' => 25,
        ]);

        $component->assertHasNoErrors();
    }

    /** @test */
    public function it_validates_required_fields_for_stock_movement()
    {
        $component = Livewire::test('stock-form')
            ->set('product_id', '')
            ->set('warehouse_id', '')
            ->set('movement_type', '')
            ->set('quantity', '')
            ->call('save');

        $component->assertHasErrors([
            'product_id',
            'warehouse_id', 
            'movement_type',
            'quantity'
        ]);
    }

    /** @test */
    public function it_validates_movement_type_values()
    {
        $component = Livewire::test('stock-form')
            ->set('product_id', $this->product->id)
            ->set('warehouse_id', $this->warehouse1->id)
            ->set('movement_type', 'invalid_type')
            ->set('quantity', 10)
            ->call('save');

        $component->assertHasErrors(['movement_type']);
    }

    /** @test */
    public function it_validates_quantity_is_positive_number()
    {
        $component = Livewire::test('stock-form')
            ->set('product_id', $this->product->id)
            ->set('warehouse_id', $this->warehouse1->id)
            ->set('movement_type', 'in')
            ->set('quantity', -5)
            ->call('save');

        $component->assertHasErrors(['quantity']);

        $component->set('quantity', 0)->call('save');
        $component->assertHasErrors(['quantity']);

        $component->set('quantity', 'abc')->call('save');
        $component->assertHasErrors(['quantity']);
    }

    /** @test */
    public function it_records_movement_metadata_correctly()
    {
        ProductWarehouseStock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse1->id,
            'stock_on_hand' => 20,
            'reserved_stock' => 0,
            'safety_stock' => 0,
        ]);

        Livewire::test('stock-form')
            ->set('product_id', $this->product->id)
            ->set('warehouse_id', $this->warehouse1->id)
            ->set('movement_type', 'in')
            ->set('quantity', 15)
            ->set('notes', 'Test movement')
            ->set('reason_code', 'PURCHASE')
            ->call('save');

        $movement = StockMovement::latest()->first();

        $this->assertEquals($this->product->id, $movement->product_id);
        $this->assertEquals($this->warehouse1->id, $movement->warehouse_id);
        $this->assertEquals('IN', $movement->type);
        $this->assertEquals(15, $movement->qty);
        $this->assertEquals('Test movement', $movement->note);
        $this->assertEquals('PURCHASE', $movement->reason_code);
        $this->assertEquals($this->user->id, $movement->performed_by);
        $this->assertEquals(20, $movement->stock_before);
        $this->assertEquals(35, $movement->stock_after);
        $this->assertEquals('manual', $movement->ref_type);
        $this->assertEquals($this->warehouse1->code, $movement->warehouse);
    }
}