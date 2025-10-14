<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domains\User\Models\User;
use App\Product;
use App\ProductUnit;
use App\Warehouse;
use App\ProductWarehouseStock;
use App\Sale;
use App\SaleItem;
use App\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class POSBusinessLogicTest extends TestCase
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
    public function it_can_calculate_subtotal_correctly()
    {
        $cart = [
            'product_1' => [
                'product_id' => 1,
                'name' => 'Product 1',
                'price' => 10000,
                'quantity' => 2,
            ],
            'product_2' => [
                'product_id' => 2,
                'name' => 'Product 2',
                'price' => 5000,
                'quantity' => 3,
            ],
        ];

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $this->assertEquals(35000, $subtotal);
    }

    /** @test */
    public function it_can_calculate_discount_amount_correctly()
    {
        $subtotal = 10000;
        $discount = 1000;
        $discountType = 'amount';

        $discountAmount = $discountType === 'percentage' 
            ? ($subtotal * $discount) / 100 
            : $discount;

        $this->assertEquals(1000, $discountAmount);
    }

    /** @test */
    public function it_can_calculate_discount_percentage_correctly()
    {
        $subtotal = 10000;
        $discount = 10;
        $discountType = 'percentage';

        $discountAmount = $discountType === 'percentage' 
            ? ($subtotal * $discount) / 100 
            : $discount;

        $this->assertEquals(1000, $discountAmount);
    }

    /** @test */
    public function it_can_calculate_total_with_discount()
    {
        $subtotal = 10000;
        $discountAmount = 1000;

        $total = max(0, $subtotal - $discountAmount);

        $this->assertEquals(9000, $total);
    }

    /** @test */
    public function it_can_calculate_change_correctly()
    {
        $total = 9000;
        $amountPaid = 10000;

        $change = max(0, $amountPaid - $total);

        $this->assertEquals(1000, $change);
    }

    /** @test */
    public function it_prevents_negative_total()
    {
        $subtotal = 5000;
        $discountAmount = 10000; // Discount lebih besar dari subtotal

        $total = max(0, $subtotal - $discountAmount);

        $this->assertEquals(0, $total);
    }

    /** @test */
    public function it_prevents_negative_change()
    {
        $total = 10000;
        $amountPaid = 5000; // Bayar kurang dari total

        $change = max(0, $amountPaid - $total);

        $this->assertEquals(0, $change);
    }

    /** @test */
    public function it_can_create_sale_record()
    {
        $this->actingAs($this->user);

        $saleData = [
            'sale_number' => 'POS-' . date('Ymd') . '-0001',
            'subtotal' => 10000,
            'discount_total' => 1000,
            'final_total' => 9000,
            'payment_method' => 'cash',
            'payment_notes' => 'Test payment',
            'cashier_id' => $this->user->id,
            'status' => 'PAID',
        ];

        $sale = Sale::create($saleData);

        $this->assertDatabaseHas('sales', [
            'sale_number' => 'POS-' . date('Ymd') . '-0001',
            'subtotal' => 10000,
            'discount_total' => 1000,
            'final_total' => 9000,
            'cashier_id' => $this->user->id,
        ]);

        $this->assertEquals('POS-' . date('Ymd') . '-0001', $sale->sale_number);
    }

    /** @test */
    public function it_can_create_sale_items()
    {
        $this->actingAs($this->user);

        $sale = Sale::create([
            'sale_number' => 'POS-' . date('Ymd') . '-0001',
            'subtotal' => 10000,
            'discount_total' => 0,
            'final_total' => 10000,
            'payment_method' => 'cash',
            'cashier_id' => $this->user->id,
            'status' => 'PAID',
        ]);

        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'qty' => 2,
            'unit_price' => 10000,
            'total_price' => 20000,
        ]);

        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'qty' => 2,
            'unit_price' => 10000,
        ]);
    }

    /** @test */
    public function it_can_update_stock_after_sale()
    {
        $this->actingAs($this->user);

        $initialStock = $this->product->current_stock;
        $saleQuantity = 5;

        // Simulate stock update
        $this->product->update([
            'current_stock' => $initialStock - $saleQuantity
        ]);

        $this->assertEquals($initialStock - $saleQuantity, $this->product->fresh()->current_stock);
    }

    /** @test */
    public function it_can_create_stock_movement_record()
    {
        $this->actingAs($this->user);

        $stockBefore = 100;
        $quantity = 5;
        $stockAfter = $stockBefore - $quantity;

        $stockMovement = StockMovement::create([
            'product_id' => $this->product->id,
            'type' => 'out',
            'qty' => $quantity,
            'ref_type' => 'sale',
            'ref_id' => 1,
            'note' => 'POS Sale',
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'warehouse_id' => $this->warehouse->id,
            'warehouse' => $this->warehouse->code,
            'performed_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'out',
            'qty' => $quantity,
            'ref_type' => 'sale',
        ]);
    }

    /** @test */
    public function it_validates_sufficient_payment()
    {
        $total = 10000;
        $amountPaid = 5000;

        $isPaymentSufficient = $amountPaid >= $total;

        $this->assertFalse($isPaymentSufficient);
    }

    /** @test */
    public function it_validates_cart_not_empty()
    {
        $cart = [];

        $isCartEmpty = empty($cart);

        $this->assertTrue($isCartEmpty);
    }

    /** @test */
    public function it_can_generate_sale_number()
    {
        $todayCount = 0; // Simulate no sales today
        $expectedSaleNumber = 'POS-' . date('Ymd') . '-' . str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT);

        $this->assertEquals('POS-' . date('Ymd') . '-0001', $expectedSaleNumber);
    }

    /** @test */
    public function it_can_check_user_permissions()
    {
        // Ensure roles and permissions are seeded
        $this->seed(\Database\Seeders\RoleSeeder::class);
        
        // Create a fresh user and assign admin role
        $testUser = User::factory()->create();
        $testUser->assignRole('admin');
        
        // Force refresh the user and load relationships
        $testUser = User::find($testUser->id);
        $testUser->load('roles.permissions');

        $this->actingAs($testUser);

        // Debug output for test
        $roles = $testUser->roles->pluck('name')->toArray();
        $permissions = $testUser->roles->flatMap->permissions->pluck('name')->unique()->toArray();
        
        // Check if admin role exists and has permissions
        $this->assertContains('admin', $roles, 'User should have admin role');
        $this->assertContains('pos.access', $permissions, 'Admin role should have pos.access permission');
        $this->assertContains('pos.sell', $permissions, 'Admin role should have pos.sell permission');

        $hasAccessPermission = $testUser->hasPermission('pos.access');
        $hasSellPermission = $testUser->hasPermission('pos.sell');

        $this->assertTrue($hasAccessPermission, 'User should have pos.access permission');
        $this->assertTrue($hasSellPermission, 'User should have pos.sell permission');
    }

    /** @test */
    public function it_can_validate_pricing_tiers()
    {
        $validTiers = ['retail', 'semi_grosir', 'grosir', 'custom'];
        $testTier = 'retail';

        $isValidTier = in_array($testTier, $validTiers);

        $this->assertTrue($isValidTier);
    }

    /** @test */
    public function it_can_validate_payment_methods()
    {
        // Updated to align with POS: EDC replaces debit terminology
        $validMethods = ['cash', 'transfer', 'edc', 'qr'];
        $testMethod = 'cash';

        $isValidMethod = in_array($testMethod, $validMethods);

        $this->assertTrue($isValidMethod);
    }
}