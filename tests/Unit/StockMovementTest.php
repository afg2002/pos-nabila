<?php

namespace Tests\Unit;

use App\Product;
use App\StockMovement;
use App\Domains\User\Models\User;
use App\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->warehouse = Warehouse::factory()->create(['name' => 'Test Warehouse']);
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST001',
            'current_stock' => 100
        ]);
    }

    /** @test */
    public function it_can_create_stock_movement()
    {
        $movement = StockMovement::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'IN',
            'qty' => 50,
            'note' => 'Test stock in',
            'ref_type' => 'manual',
            'performed_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'IN',
            'qty' => 50,
        ]);

        $this->assertEquals('Test Warehouse', $movement->warehouse_name);
    }

    /** @test */
    public function it_can_update_stock_movement()
    {
        $movement = StockMovement::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'IN',
            'qty' => 50,
            'note' => 'Original note',
            'ref_type' => 'manual',
            'performed_by' => $this->user->id,
        ]);

        $movement->update([
            'qty' => 75,
            'note' => 'Updated note'
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'qty' => 75,
            'note' => 'Updated note'
        ]);
    }

    /** @test */
    public function it_can_delete_stock_movement()
    {
        $movement = StockMovement::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'IN',
            'qty' => 50,
            'note' => 'Test movement',
            'ref_type' => 'manual',
            'performed_by' => $this->user->id,
        ]);

        $movementId = $movement->id;
        $movement->delete();

        $this->assertSoftDeleted('stock_movements', [
            'id' => $movementId
        ]);
    }

    /** @test */
    public function it_has_correct_relationships()
    {
        $movement = StockMovement::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'IN',
            'qty' => 50,
            'note' => 'Test movement',
            'ref_type' => 'manual',
            'performed_by' => $this->user->id,
        ]);

        $this->assertInstanceOf(Product::class, $movement->product);
        $this->assertInstanceOf(Warehouse::class, $movement->warehouse);
        $this->assertInstanceOf(User::class, $movement->performedBy);
        
        $this->assertEquals($this->product->id, $movement->product->id);
        $this->assertEquals($this->warehouse->id, $movement->warehouse->id);
        $this->assertEquals($this->user->id, $movement->performedBy->id);
    }

    /** @test */
    public function it_returns_correct_warehouse_name()
    {
        $movement = StockMovement::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'IN',
            'qty' => 50,
            'note' => 'Test movement',
            'ref_type' => 'manual',
            'performed_by' => $this->user->id,
        ]);

        $this->assertEquals('Test Warehouse', $movement->warehouse_name);
    }

    /** @test */
    public function it_returns_default_warehouse_name_when_no_warehouse()
    {
        $movement = StockMovement::create([
            'product_id' => $this->product->id,
            'warehouse_id' => null,
            'type' => 'IN',
            'qty' => 50,
            'note' => 'Test movement',
            'ref_type' => 'manual',
            'performed_by' => $this->user->id,
        ]);

        $this->assertEquals('Tanpa Gudang', $movement->warehouse_name);
    }

    /** @test */
    public function it_can_scope_pending_approval()
    {
        // Clear any existing movements first
        StockMovement::query()->delete();
        
        StockMovement::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'IN',
            'qty' => 50,
            'note' => 'Pending movement',
            'ref_type' => 'manual',
            'performed_by' => $this->user->id,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        StockMovement::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'IN',
            'qty' => 30,
            'note' => 'Approved movement',
            'ref_type' => 'manual',
            'performed_by' => $this->user->id,
            'approved_by' => $this->user->id,
            'approved_at' => now(),
        ]);

        $pendingMovements = StockMovement::pendingApproval()->get();
        $this->assertCount(1, $pendingMovements);
        $this->assertEquals('Pending movement', $pendingMovements->first()->note);
    }

    /** @test */
    public function it_can_scope_approved()
    {
        // Clear any existing movements first
        StockMovement::query()->delete();
        
        StockMovement::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'IN',
            'qty' => 50,
            'note' => 'Pending movement',
            'ref_type' => 'manual',
            'performed_by' => $this->user->id,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        StockMovement::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'IN',
            'qty' => 30,
            'note' => 'Approved movement',
            'ref_type' => 'manual',
            'performed_by' => $this->user->id,
            'approved_by' => $this->user->id,
            'approved_at' => now(),
        ]);

        $approvedMovements = StockMovement::approved()->get();
        $this->assertCount(1, $approvedMovements);
        $this->assertEquals('Approved movement', $approvedMovements->first()->note);
    }
}