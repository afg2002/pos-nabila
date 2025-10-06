<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\CashLedger;
use App\Models\CapitalTracking;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Dashboard;

class DashboardCalculationsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $customer;
    protected $warehouse;
    protected $warehouse2;
    protected $supplier;
    protected $capitalTracking;
    protected $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        $this->warehouse = Warehouse::factory()->create([
            'name' => 'Main Store',
            'is_default' => true,
        ]);

        $this->warehouse2 = Warehouse::factory()->create([
            'name' => 'Branch Store',
        ]);

        $this->unit = ProductUnit::factory()->create(['name' => 'Pieces']);
        
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST001',
            'price_retail' => 10000,
            'price_purchase' => 6000,
            'unit_id' => $this->unit->id,
        ]);

        $this->customer = Customer::factory()->create([
            'name' => 'Test Customer',
        ]);

        $this->supplier = Supplier::factory()->create(['name' => 'Test Supplier']);
        
        $this->capitalTracking = CapitalTracking::factory()->create([
            'name' => 'Modal Utama',
            'initial_amount' => 1000000,
            'current_amount' => 1000000,
        ]);

        // Create initial stock
        WarehouseStock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
        ]);

        WarehouseStock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse2->id,
            'quantity' => 50,
        ]);
    }

    /** @test */
    public function it_calculates_daily_sales_correctly()
    {
        $this->actingAs($this->user);

        // Create sales for today
        Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 100000,
            'sale_date' => now(),
        ]);

        Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 150000,
            'sale_date' => now(),
        ]);

        // Create sale for yesterday (should not be included)
        Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 200000,
            'sale_date' => now()->subDay(),
        ]);

        $component = Livewire::test(Dashboard::class);
        
        $this->assertEquals(250000, $component->get('todaySales'));
    }

    /** @test */
    public function it_calculates_monthly_sales_correctly()
    {
        $this->actingAs($this->user);

        // Create sales for this month
        Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 300000,
            'sale_date' => now()->startOfMonth(),
        ]);

        Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 400000,
            'sale_date' => now(),
        ]);

        // Create sale for last month (should not be included)
        Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 500000,
            'sale_date' => now()->subMonth(),
        ]);

        $component = Livewire::test(Dashboard::class);
        
        $this->assertEquals(700000, $component->get('monthlySales'));
    }

    /** @test */
    public function it_calculates_daily_profit_correctly()
    {
        $this->actingAs($this->user);

        $sale = Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 100000,
            'sale_date' => now(),
        ]);

        // Create sale items with profit calculation
        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 10000, // Selling price
            'cost_price' => 6000,  // Cost price
        ]);

        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 10000,
            'cost_price' => 6000,
        ]);

        $component = Livewire::test(Dashboard::class);
        
        // Profit = (10000 - 6000) * 10 = 40000
        $this->assertEquals(40000, $component->get('todayProfit'));
    }

    /** @test */
    public function it_calculates_monthly_profit_correctly()
    {
        $this->actingAs($this->user);

        // Sale 1 - This month
        $sale1 = Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 100000,
            'sale_date' => now()->startOfMonth(),
        ]);

        SaleItem::factory()->create([
            'sale_id' => $sale1->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 10000,
            'cost_price' => 6000,
        ]);

        // Sale 2 - This month
        $sale2 = Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 50000,
            'sale_date' => now(),
        ]);

        SaleItem::factory()->create([
            'sale_id' => $sale2->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 10000,
            'cost_price' => 6000,
        ]);

        $component = Livewire::test(Dashboard::class);
        
        // Profit = (10000 - 6000) * 15 = 60000
        $this->assertEquals(60000, $component->get('monthlyProfit'));
    }

    /** @test */
    public function it_calculates_cash_balance_correctly()
    {
        $this->actingAs($this->user);

        // Create income transactions
        CashLedger::factory()->create([
            'type' => 'in',
            'amount' => 500000,
            'capital_tracking_id' => $this->capitalTracking->id,
            'transaction_date' => now(),
        ]);

        CashLedger::factory()->create([
            'type' => 'in',
            'amount' => 300000,
            'capital_tracking_id' => $this->capitalTracking->id,
            'transaction_date' => now(),
        ]);

        // Create expense transactions
        CashLedger::factory()->create([
            'type' => 'out',
            'amount' => 200000,
            'capital_tracking_id' => $this->capitalTracking->id,
            'transaction_date' => now(),
        ]);

        $component = Livewire::test(Dashboard::class);
        
        // Balance = Initial (1000000) + Income (800000) - Expense (200000) = 1600000
        $this->assertEquals(1600000, $component->get('cashBalance'));
    }

    /** @test */
    public function it_counts_pending_purchase_orders_correctly()
    {
        $this->actingAs($this->user);

        // Create pending purchase orders
        PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'pending',
        ]);

        PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'pending',
        ]);

        // Create received purchase order (should not be counted)
        PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'received',
        ]);

        $component = Livewire::test(Dashboard::class);
        
        $this->assertEquals(2, $component->get('pendingPurchaseOrders'));
    }

    /** @test */
    public function it_calculates_sales_breakdown_by_category()
    {
        $this->actingAs($this->user);

        $product1 = Product::factory()->create([
            'name' => 'Electronics Product',
            'category' => 'electronics',
            'price_retail' => 20000,
            'unit_id' => $this->unit->id,
        ]);

        $product2 = Product::factory()->create([
            'name' => 'Clothing Product',
            'category' => 'clothing',
            'price_retail' => 15000,
            'unit_id' => $this->unit->id,
        ]);

        // Sale with electronics
        $sale1 = Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 100000,
            'sale_date' => now(),
        ]);

        SaleItem::factory()->create([
            'sale_id' => $sale1->id,
            'product_id' => $product1->id,
            'quantity' => 5,
            'unit_price' => 20000,
        ]);

        // Sale with clothing
        $sale2 = Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 45000,
            'sale_date' => now(),
        ]);

        SaleItem::factory()->create([
            'sale_id' => $sale2->id,
            'product_id' => $product2->id,
            'quantity' => 3,
            'unit_price' => 15000,
        ]);

        $component = Livewire::test(Dashboard::class);
        $breakdown = $component->get('salesBreakdown');

        $this->assertArrayHasKey('electronics', $breakdown);
        $this->assertArrayHasKey('clothing', $breakdown);
        $this->assertEquals(100000, $breakdown['electronics']);
        $this->assertEquals(45000, $breakdown['clothing']);
    }

    /** @test */
    public function it_calculates_top_selling_products()
    {
        $this->actingAs($this->user);

        $product2 = Product::factory()->create([
            'name' => 'Product 2',
            'sku' => 'TEST002',
            'price_retail' => 15000,
            'unit_id' => $this->unit->id,
        ]);

        $sale1 = Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now(),
        ]);

        $sale2 = Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now(),
        ]);

        // Product 1 sold 15 units total
        SaleItem::factory()->create([
            'sale_id' => $sale1->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 10000,
        ]);

        SaleItem::factory()->create([
            'sale_id' => $sale2->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 10000,
        ]);

        // Product 2 sold 8 units total
        SaleItem::factory()->create([
            'sale_id' => $sale1->id,
            'product_id' => $product2->id,
            'quantity' => 8,
            'unit_price' => 15000,
        ]);

        $component = Livewire::test(Dashboard::class);
        $topProducts = $component->get('topSellingProducts');

        $this->assertCount(2, $topProducts);
        $this->assertEquals($this->product->id, $topProducts[0]['product_id']);
        $this->assertEquals(15, $topProducts[0]['total_quantity']);
        $this->assertEquals($product2->id, $topProducts[1]['product_id']);
        $this->assertEquals(8, $topProducts[1]['total_quantity']);
    }

    /** @test */
    public function it_calculates_monthly_sales_trend()
    {
        $this->actingAs($this->user);

        // Create sales for different months
        Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 100000,
            'sale_date' => now()->subMonths(2),
        ]);

        Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 150000,
            'sale_date' => now()->subMonth(),
        ]);

        Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 200000,
            'sale_date' => now(),
        ]);

        $component = Livewire::test(Dashboard::class);
        $salesTrend = $component->get('monthlySalesTrend');

        $this->assertIsArray($salesTrend);
        $this->assertCount(3, $salesTrend);
    }

    /** @test */
    public function it_handles_zero_values_gracefully()
    {
        $this->actingAs($this->user);

        // No sales, no cash transactions
        $component = Livewire::test(Dashboard::class);

        $this->assertEquals(0, $component->get('todaySales'));
        $this->assertEquals(0, $component->get('monthlySales'));
        $this->assertEquals(0, $component->get('todayProfit'));
        $this->assertEquals(0, $component->get('monthlyProfit'));
        $this->assertEquals(1000000, $component->get('cashBalance')); // Initial capital
        $this->assertEquals(0, $component->get('pendingPurchaseOrders'));
    }

    /** @test */
    public function it_calculates_profit_margin_correctly()
    {
        $this->actingAs($this->user);

        $sale = Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 100000,
            'sale_date' => now(),
        ]);

        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 10000,
            'cost_price' => 6000,
        ]);

        $component = Livewire::test(Dashboard::class);
        
        // Profit = 40000, Revenue = 100000, Margin = 40%
        $this->assertEquals(40, $component->get('profitMargin'));
    }

    /** @test */
    public function it_filters_data_by_warehouse()
    {
        $this->actingAs($this->user);

        // Sales in main warehouse
        Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 100000,
            'sale_date' => now(),
        ]);

        // Sales in branch warehouse
        Sale::factory()->create([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse2->id,
            'total_amount' => 200000,
            'sale_date' => now(),
        ]);

        $component = Livewire::test(Dashboard::class)
            ->set('selectedWarehouse', $this->warehouse->id);

        $this->assertEquals(100000, $component->get('todaySales'));
    }
}