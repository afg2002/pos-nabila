<?php

namespace Tests\Feature;

use App\Livewire\PosKasir;
use App\Domains\User\Models\User;
use App\Domains\Role\Models\Role;
use App\Domains\Permission\Models\Permission;
use App\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosKasirPaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    private function createAuthorizedUser(): User
    {
        // Create user
        /** @var User $user */
        $user = User::factory()->create();

        // Create role and permissions
        $role = Role::create([
            'name' => 'cashier',
            'display_name' => 'Cashier',
            'description' => 'POS Cashier',
            'is_active' => true,
        ]);

        $posAccess = Permission::create([
            'name' => 'pos.access',
            'display_name' => 'Access POS',
            'description' => 'Can access POS',
            'group' => 'pos',
            'is_active' => true,
        ]);

        $posCreateSales = Permission::create([
            'name' => 'pos.create_sales',
            'display_name' => 'Create POS Sales',
            'description' => 'Can create sales in POS',
            'group' => 'pos',
            'is_active' => true,
        ]);

        // Attach permissions to role and role to user
        $role->permissions()->attach([$posAccess->id, $posCreateSales->id]);
        $user->assignRole($role);

        return $user;
    }

    private function ensureStoreWarehouse(): Warehouse
    {
        return \Database\Factories\WarehouseFactory::new()->asStore()->default()->create();
    }

    private function setupCartWithCustomItem($component, $price = 10000, $qty = 2)
    {
        $component->set('customItemName', 'Jasa Perbaikan');
        $component->set('customItemDescription', 'Pekerjaan ringan');
        $component->set('customItemPrice', $price);
        $component->set('customItemQuantity', $qty);
        $component->call('addCustomItem');
        $component->call('calculateTotals');
    }

    public function test_payment_status_selector_updates_amount_paid_and_change()
    {
        $user = $this->createAuthorizedUser();
        $this->actingAs($user);
        $this->ensureStoreWarehouse();

        $component = Livewire::test(PosKasir::class);

        // Setup cart with a custom item (no stock check)
        $this->setupCartWithCustomItem($component, price: 10000, qty: 2); // total = 20000

        // Open checkout should default to PAID with amountPaid = total
        $component->call('openCheckout');
        $component->assertSet('total', 20000);
        $component->assertSet('paymentStatus', 'PAID');
        $component->assertSet('amountPaid', 20000);
        $component->assertSet('change', 0);

        // Set to UNPAID -> amountPaid becomes 0, change = 0
        $component->set('paymentStatus', 'UNPAID');
        
        $component->assertSet('amountPaid', 0);
        $component->assertSet('change', 0);

        // Set to PARTIAL -> amountPaid becomes > 0 and < total, change = 0
        $component->set('paymentStatus', 'PARTIAL');
        $amountPaidPartial = $component->get('amountPaid');
        $total = $component->get('total');
        $this->assertTrue($amountPaidPartial > 0 && $amountPaidPartial < $total, 'amountPaid for PARTIAL should be between 0 and total');
        $component->assertSet('change', 0);

        // Set to PAID -> amountPaid >= total, change = 0
        $component->set('paymentStatus', 'PAID');
        $amountPaidPaid = $component->get('amountPaid');
        $this->assertTrue($amountPaidPaid >= $total, 'amountPaid for PAID should be at least total');
        $component->assertSet('change', 0);
    }

    public function test_checkout_persists_selected_payment_status_paid()
    {
        $user = $this->createAuthorizedUser();
        $this->actingAs($user);
        $this->ensureStoreWarehouse();

        $component = Livewire::test(PosKasir::class);
        $this->setupCartWithCustomItem($component, price: 5000, qty: 2); // total = 10000
        $component->call('openCheckout');
        $component->set('paymentStatus', 'PAID');
        $component->call('processCheckout');

        $sale = \App\Sale::first();
        $this->assertNotNull($sale, 'Sale should be created');
        $this->assertEquals('PAID', $sale->payment_status);
        $this->assertEquals(10000, $sale->final_total);
        $this->assertEquals(10000, $sale->cash_amount);
        $this->assertEquals(0, $sale->change_amount);
        $this->assertCount(1, $sale->saleItems);
    }

    public function test_checkout_persists_selected_payment_status_unpaid()
    {
        $user = $this->createAuthorizedUser();
        $this->actingAs($user);
        $this->ensureStoreWarehouse();

        $component = Livewire::test(PosKasir::class);
        $this->setupCartWithCustomItem($component, price: 7000, qty: 1); // total = 7000
        $component->call('openCheckout');
        $component->set('paymentStatus', 'UNPAID');
        $component->call('processCheckout');

        $sale = \App\Sale::first();
        $this->assertNotNull($sale, 'Sale should be created');
        $this->assertEquals('UNPAID', $sale->payment_status);
        $this->assertEquals(7000, $sale->final_total);
        $this->assertEquals(0, $sale->cash_amount);
        $this->assertEquals(0, $sale->change_amount);
        $this->assertCount(1, $sale->saleItems);
    }

    public function test_checkout_persists_selected_payment_status_partial()
    {
        $user = $this->createAuthorizedUser();
        $this->actingAs($user);
        $this->ensureStoreWarehouse();

        $component = Livewire::test(PosKasir::class);
        $this->setupCartWithCustomItem($component, price: 12000, qty: 1); // total = 12000
        $component->call('openCheckout');
        $component->set('paymentStatus', 'PARTIAL');
        $partialPaid = $component->get('amountPaid');
        $total = $component->get('total');
        $this->assertTrue($partialPaid > 0 && $partialPaid < $total, 'amountPaid for PARTIAL should be between 0 and total');

        $component->call('processCheckout');

        $sale = \App\Sale::first();
        $this->assertNotNull($sale, 'Sale should be created');
        $this->assertEquals('PARTIAL', $sale->payment_status);
        $this->assertEquals(12000, $sale->final_total);
        $this->assertEquals($partialPaid, $sale->cash_amount);
        $this->assertEquals(0, $sale->change_amount);
        $this->assertCount(1, $sale->saleItems);
    }
}