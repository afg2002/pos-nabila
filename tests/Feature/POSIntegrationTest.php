<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Domains\User\Models\User;
use App\Product;
use App\ProductUnit;
use App\Warehouse;
use App\ProductWarehouseStock;
use App\Sale;
use App\SaleItem;
use App\StockMovement;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class POSIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $warehouse;
    protected $product;
    protected $productUnit;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(RoleSeeder::class);

        // Create user with admin role
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        // Create warehouse
        $this->warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'code' => 'MAIN001',
            'type' => 'main',
            'branch' => 'Main Branch',
            'address' => 'Test Address',
            'phone' => '081234567890',
            'is_default' => true,
        ]);

        // Create product unit
        $this->productUnit = ProductUnit::firstOrCreate([
            'abbreviation' => 'pcs',
        ], [
            'name' => 'Pcs',
            'description' => 'Pieces',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Create product
        $this->product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST001',
            'barcode' => '1234567890',
            'category' => 'Test Category',
            'unit_id' => $this->productUnit->id,
            'base_cost' => 5000,
            'cost_price' => 5000,
            'price_retail' => 10000,
            'price_semi_grosir' => 9000,
            'price_grosir' => 8000,
            'min_margin_pct' => 20,
            'default_price_type' => 'retail',
            'current_stock' => 100,
            'status' => 'active',
        ]);

        // Create initial stock
        ProductWarehouseStock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'stock_on_hand' => 100,
            'reserved_stock' => 0,
            'safety_stock' => 10,
        ]);
    }

    /** @test */
    public function it_can_access_pos_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/pos');

        $response->assertStatus(200);
        $response->assertSee('POS Kasir');
    }

    /** @test */
    public function it_can_create_sale_through_api()
    {
        $saleData = [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 2,
                    'unit_price' => 10000,
                ]
            ],
            'subtotal' => 20000,
            'discount_total' => 0,
            'final_total' => 20000,
            'payment_method' => 'cash',
            'payment_amount' => 25000,
            'change_amount' => 5000,
            'warehouse_id' => $this->warehouse->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/checkout', $saleData);

        $response->assertStatus(200);
        
        // Verify sale was created
        $this->assertDatabaseHas('sales', [
            'subtotal' => 20000,
            'final_total' => 20000,
            'payment_method' => 'cash',
            'cashier_id' => $this->user->id,
            'status' => 'PAID',
        ]);

        // Verify sale item was created
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $this->product->id,
            'qty' => 2,
            'unit_price' => 10000,
            'total_price' => 20000,
        ]);

        // Verify stock was updated
        $this->assertDatabaseHas('product_warehouse_stocks', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'stock_on_hand' => 98, // 100 - 2
        ]);

        // Verify stock movement was created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'out',
            'qty' => 2,
            'ref_type' => 'sale',
        ]);
    }

    /** @test */
    public function it_can_search_product_by_barcode()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/pos/search-product?barcode=' . $this->product->barcode);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'barcode' => $this->product->barcode,
                'price' => $this->product->price,
            ]
        ]);
    }

    /** @test */
    public function it_can_calculate_discount()
    {
        $cartData = [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 2,
                    'unit_price' => 10000,
                ]
            ],
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/calculate-total', $cartData);

        $response->assertStatus(200);
        $response->assertJson([
            'subtotal' => 20000,
            'discount_total' => 2000, // 10% of 20000
            'final_total' => 18000,
        ]);
    }

    /** @test */
    public function it_validates_insufficient_stock()
    {
        $saleData = [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 150, // More than available stock (100)
                    'unit_price' => 10000,
                ]
            ],
            'subtotal' => 1500000,
            'discount_total' => 0,
            'final_total' => 1500000,
            'payment_method' => 'cash',
            'payment_amount' => 1500000,
            'change_amount' => 0,
            'warehouse_id' => $this->warehouse->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/checkout', $saleData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.qty']);
    }

    /** @test */
    public function it_validates_insufficient_payment()
    {
        $saleData = [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 2,
                    'unit_price' => 10000,
                ]
            ],
            'subtotal' => 20000,
            'discount_total' => 0,
            'final_total' => 20000,
            'payment_method' => 'cash',
            'payment_amount' => 15000, // Less than final total
            'change_amount' => 0,
            'warehouse_id' => $this->warehouse->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/pos/checkout', $saleData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_amount']);
    }

    /** @test */
    public function it_can_handle_different_payment_methods()
    {
        $paymentMethods = ['cash', 'card', 'transfer', 'qris'];

        foreach ($paymentMethods as $method) {
            $saleData = [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'qty' => 1,
                        'unit_price' => 10000,
                    ]
                ],
                'subtotal' => 10000,
                'discount_total' => 0,
                'final_total' => 10000,
                'payment_method' => $method,
                'payment_amount' => 10000,
                'change_amount' => 0,
                'warehouse_id' => $this->warehouse->id,
            ];

            $response = $this->actingAs($this->user)
                ->postJson('/api/pos/checkout', $saleData);

            $response->assertStatus(200);
            
            $this->assertDatabaseHas('sales', [
                'payment_method' => $method,
                'final_total' => 10000,
                'status' => 'PAID',
            ]);
        }
    }

    /** @test */
    public function it_generates_unique_sale_numbers()
    {
        $saleNumbers = [];

        for ($i = 0; $i < 3; $i++) {
            $saleData = [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'qty' => 1,
                        'unit_price' => 10000,
                    ]
                ],
                'subtotal' => 10000,
                'discount_total' => 0,
                'final_total' => 10000,
                'payment_method' => 'cash',
                'payment_amount' => 10000,
                'change_amount' => 0,
                'warehouse_id' => $this->warehouse->id,
            ];

            $response = $this->actingAs($this->user)
                ->postJson('/api/pos/checkout', $saleData);

            $response->assertStatus(200);
            
            $sale = Sale::latest()->first();
            $saleNumbers[] = $sale->sale_number;
        }

        // Verify all sale numbers are unique
        $this->assertEquals(3, count(array_unique($saleNumbers)));
    }

    /** @test */
    public function it_requires_pos_permissions()
    {
        // Create user without admin role
        $userWithoutPermission = User::factory()->create();

        $response = $this->actingAs($userWithoutPermission)
            ->get('/pos');

        $response->assertStatus(403);
    }
}