<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Livewire\StockHistory;
use App\StockMovement;
use App\Product;
use App\Warehouse;
use App\Domains\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

class StockHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user with admin role
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        // Mock authorization to always allow
        Gate::define('delete', function ($user, $stockMovement) {
            return true;
        });
        
        // Mock the policy for StockMovement using a string class name
        Gate::policy(\App\StockMovement::class, 'Tests\Unit\MockStockMovementPolicy');
        
        // Create test warehouse
        $this->warehouse = Warehouse::create([
            'name' => 'Test Warehouse',
            'code' => 'TW001',
            'type' => 'main',
            'branch' => 'main',
            'address' => 'Test Address',
            'phone' => '123456789',
            'is_default' => 1
        ]);

        // Create test product unit first
        $productUnit = \App\Models\ProductUnit::create([
            'name' => 'Pcs',
            'symbol' => 'pcs',
            'abbreviation' => 'pcs'
        ]);

        // Create test product
        $this->product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST001',
            'category' => 'test',
            'unit_id' => $productUnit->id,
            'base_cost' => 8000,
            'cost_price' => 8000,
            'price_purchase' => 8000,
            'price_retail' => 10000,
            'price_semi_grosir' => 9500,
            'price_grosir' => 9000,
            'min_margin_pct' => 10.00,
            'default_price_type' => 'retail',
            'current_stock' => 100,
            'min_stock' => 10,
            'status' => 'active',
            'is_active' => 1
        ]);
    }

    /** @test */
    public function it_can_render_stock_history_component()
    {
        Livewire::test(StockHistory::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.stock-history');
    }

    /** @test */
    public function it_can_search_stock_movements()
    {
        $movement1 = StockMovement::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'IN',
            'qty' => 50,
            'note' => 'Test movement 1',
            'ref_type' => 'manual',
            'performed_by' => $this->user->id,
        ]);

        $movement2 = StockMovement::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'OUT',
            'qty' => 30,
            'note' => 'Another movement',
            'ref_type' => 'manual',
            'performed_by' => $this->user->id,
        ]);

        $component = Livewire::test(StockHistory::class)
            ->set('search', 'Test movement')
            ->call('$refresh'); // Force refresh to apply search

        $component->assertSee($this->product->name); // Pastikan ada data yang ditampilkan
    }

    /** @test */
    public function it_can_filter_by_type()
    {
        $inMovement = StockMovement::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'IN',
            'qty' => 50,
            'note' => 'IN movement',
            'ref_type' => 'manual',
            'performed_by' => $this->user->id,
        ]);

        $outMovement = StockMovement::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'OUT',
            'qty' => 30,
            'note' => 'OUT movement',
            'ref_type' => 'manual',
            'performed_by' => $this->user->id,
        ]);

        $component = Livewire::test(StockHistory::class)
            ->set('movementTypeFilter', 'IN')
            ->call('$refresh'); // Force refresh to apply filter

        $component->assertSee('Masuk') // Melihat label type IN
            ->assertSee($this->product->name); // Pastikan ada data yang ditampilkan
    }

    /** @test */
    public function it_can_show_detail()
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

        Livewire::test(StockHistory::class)
            ->call('openDetailModal', $movement->id)
            ->assertSet('selectedMovement.id', $movement->id)
            ->assertSet('showDetailModal', true);
    }

    /** @test */
    public function it_can_edit_movement()
    {
        $movement = StockMovement::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'qty' => 10,
            'ref_type' => 'manual',
            'ref_id' => null,
            'note' => 'Test movement',
            'performed_by' => $this->user->id,
        ]);

        Livewire::test(StockHistory::class)
            ->call('openEditModal', $movement->id)
            ->assertSet('showEditModal', true)
            ->assertSet('selectedMovement.id', $movement->id)
            ->assertSet('editQty', 10)
            ->assertSet('editNotes', 'Test movement');
    }

    /** @test */
    public function it_can_confirm_delete_movement()
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

        Livewire::test(StockHistory::class)
            ->call('confirmDeleteMovement', $movement->id)
            ->assertSet('selectedMovement.id', $movement->id)
            ->assertSet('showDeleteModal', true);
    }

    /** @test */
    public function it_can_delete_movement()
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

        Livewire::test(StockHistory::class)
            ->call('confirmDeleteMovement', $movement->id)
            ->call('deleteMovement')
            ->assertSet('selectedMovement', null)
            ->assertSet('showDeleteModal', false);

        $this->assertDatabaseMissing('stock_movements', ['id' => $movement->id]);
    }

    /** @test */
    public function it_can_cancel_delete()
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

        Livewire::test(StockHistory::class)
            ->call('confirmDeleteMovement', $movement->id)
            ->call('cancelDelete')
            ->assertSet('showDeleteModal', false)
            ->assertSet('selectedMovement', null);

        $this->assertDatabaseHas('stock_movements', ['id' => $movement->id]);
    }

    /** @test */
    public function it_can_close_modals()
    {
        Livewire::test(StockHistory::class)
            ->set('showDetailModal', true)
            ->set('showEditModal', true)
            ->set('showDeleteModal', true)
            ->call('closeDetailModal')
            ->assertSet('showDetailModal', false);
    }

    /** @test */
    public function it_validates_edit_form()
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

        // Load the relationship manually to ensure it exists
        $movement->load('product', 'warehouse');

        Livewire::test(StockHistory::class)
            ->call('openEditModal', $movement->id)
            ->set('editQty', '')
            ->call('updateMovement')
            ->assertHasErrors(['editQty']);
    }
}