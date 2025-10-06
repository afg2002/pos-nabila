<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Domains\User\Models\User;
use App\Product;
use App\ProductUnit;
use App\Warehouse;
use App\ProductWarehouseStock;
use App\StockMovement;
use App\Sale;
use App\SaleItem;
use App\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\PosKasir;

class POSFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $warehouse;
    protected $unit;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions first
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        // Create user with proper permissions
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        // Create warehouse
        $this->warehouse = Warehouse::factory()->create([
            'name' => 'Main Store',
            'is_default' => true,
        ]);

        // Create product unit first (or use existing)
        $this->unit = ProductUnit::firstOrCreate(
            ['name' => 'Pieces'],
            ['abbreviation' => 'pcs']
        );

        // Create product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST001',
            'price_retail' => 10000,
            'current_stock' => 100,
            'unit_id' => $this->unit->id,
        ]);

        // Create warehouse stock
        ProductWarehouseStock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'stock_on_hand' => 100,
            'reserved_stock' => 0,
            'safety_stock' => 10,
        ]);
    }

    /** @test */
    public function it_can_render_pos_kasir_component()
    {
        $this->actingAs($this->user);

        $response = $this->get('/pos');
        
        $response->assertStatus(200);
        $response->assertSeeLivewire(PosKasir::class);
    }

    /** @test */
    public function it_initializes_with_correct_default_values()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(PosKasir::class);
        
        $component->assertSet('cart', [])
                  ->assertSet('warehouseId', $this->warehouse->id)
                  ->assertSet('subtotal', 0)
                  ->assertSet('total', 0)
                  ->assertSet('discount', 0)
                  ->assertSet('discountType', 'amount')
                  ->assertSet('showCheckoutModal', false);
    }

    /** @test */
    public function it_can_add_product_to_cart_via_barcode()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(PosKasir::class);
        
        $component->set('barcode', $this->product->barcode ?: $this->product->sku)
                  ->call('updatedBarcode');
        
        $cart = $component->get('cart');
        $this->assertNotEmpty($cart);
    }

    /** @test */
    public function it_can_set_discount_amount()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(PosKasir::class);
        
        // Manually add item to cart first
        $component->set('cart', [
            'product_' . $this->product->id => [
                'product_id' => $this->product->id,
                'name' => $this->product->name,
                'price' => 10000,
                'quantity' => 1,
            ]
        ]);
        
        $component->set('discount', 1000)
                  ->set('discountType', 'amount')
                  ->call('calculateTotals');
        
        $component->assertSet('discount', 1000)
                  ->assertSet('discountType', 'amount');
    }

    /** @test */
    public function it_can_set_discount_percentage()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(PosKasir::class);
        
        // Manually add item to cart first
        $component->set('cart', [
            'product_' . $this->product->id => [
                'product_id' => $this->product->id,
                'name' => $this->product->name,
                'price' => 10000,
                'quantity' => 1,
            ]
        ]);
        
        $component->set('discount', 10)
                  ->set('discountType', 'percentage')
                  ->call('calculateTotals');
        
        $component->assertSet('discount', 10)
                  ->assertSet('discountType', 'percentage');
    }

    /** @test */
    public function it_calculates_change_when_amount_paid_is_set()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(PosKasir::class);
        
        // Set total first
        $component->set('total', 10000);
        
        // Set amount paid
        $component->set('amountPaid', 15000);
        
        $change = $component->get('change');
        $this->assertEquals(5000, $change);
    }

    /** @test */
    public function it_can_switch_warehouses()
    {
        $this->actingAs($this->user);

        // Create another warehouse
        $warehouse2 = Warehouse::factory()->create([
            'name' => 'Second Store',
            'is_default' => false,
        ]);

        $component = Livewire::test(PosKasir::class);
        
        $component->set('warehouseId', $warehouse2->id);
        
        $component->assertSet('warehouseId', $warehouse2->id);
    }

    /** @test */
    public function it_validates_required_fields_for_checkout()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(PosKasir::class);
        
        // Try to process checkout without required data
        $component->call('processCheckout')
                  ->assertHasErrors(['warehouseId', 'amountPaid']);
    }

    /** @test */
    public function it_can_set_payment_method()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(PosKasir::class);
        
        $component->set('paymentMethod', 'transfer');
        
        $component->assertSet('paymentMethod', 'transfer');
    }

    /** @test */
    public function it_can_set_pricing_tier()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(PosKasir::class);
        
        $component->set('pricingTier', 'grosir');
        
        $component->assertSet('pricingTier', 'grosir');
    }
}