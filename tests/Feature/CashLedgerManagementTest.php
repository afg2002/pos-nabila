<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\CashLedger;
use App\Models\CapitalTracking;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\CashLedgerManagement;

class CashLedgerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $capitalTracking;
    protected $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        $this->warehouse = Warehouse::factory()->create([
            'name' => 'Gudang Utama',
            'branch' => 'Cabang Utama',
        ]);

        $this->capitalTracking = CapitalTracking::create([
            'initial_capital' => 1000000,
            'current_capital' => 1000000,
            'total_income' => 0,
            'total_expense' => 0,
            'last_updated' => now(),
        ]);
    }

    /** @test */
    public function it_can_render_cash_ledger_management_component()
    {
        $this->actingAs($this->user);

        Livewire::test(CashLedgerManagement::class)
            ->assertStatus(200)
            ->assertSee('Buku Kas')
            ->assertSee('Kelola catatan pemasukan dan pengeluaran kas');
    }

    /** @test */
    public function it_can_create_income_transaction()
    {
        $this->actingAs($this->user);

        Livewire::test(CashLedgerManagement::class)
            ->call('openModal')
            ->set('type', 'in')
            ->set('amount', 500000)
            ->set('transaction_date', now()->format('Y-m-d'))
            ->set('capital_tracking_id', $this->capitalTracking->id)
            ->set('category', 'sales')
            ->set('description', 'Penjualan produk')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('notes', 'Catatan pemasukan')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('refresh');

        $this->assertDatabaseHas('cash_ledgers', [
            'type' => 'in',
            'amount' => 500000,
            'description' => 'Penjualan produk',
            'category' => 'sales',
            'capital_tracking_id' => $this->capitalTracking->id,
            'warehouse_id' => $this->warehouse->id,
            'notes' => 'Catatan pemasukan',
        ]);

        // Check if capital tracking amount is updated
        $this->capitalTracking->refresh();
        $this->assertEquals(1500000, $this->capitalTracking->current_amount);
    }

    /** @test */
    public function it_can_create_expense_transaction()
    {
        $this->actingAs($this->user);

        Livewire::test(CashLedgerManagement::class)
            ->call('openModal')
            ->set('type', 'out')
            ->set('amount', 200000)
            ->set('transaction_date', now()->format('Y-m-d'))
            ->set('capital_tracking_id', $this->capitalTracking->id)
            ->set('category', 'operational')
            ->set('description', 'Biaya operasional')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('notes', 'Catatan pengeluaran')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('refresh');

        $this->assertDatabaseHas('cash_ledgers', [
            'type' => 'out',
            'amount' => 200000,
            'description' => 'Biaya operasional',
            'category' => 'operational',
            'capital_tracking_id' => $this->capitalTracking->id,
            'warehouse_id' => $this->warehouse->id,
            'notes' => 'Catatan pengeluaran',
        ]);

        // Check if capital tracking amount is updated
        $this->capitalTracking->refresh();
        $this->assertEquals(800000, $this->capitalTracking->current_amount);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->actingAs($this->user);

        Livewire::test(CashLedgerManagement::class)
            ->call('openModal')
            ->call('save')
            ->assertHasErrors([
                'type' => 'required',
                'amount' => 'required',
                'transaction_date' => 'required',
                'capital_tracking_id' => 'required',
                'category' => 'required',
                'description' => 'required',
            ]);
    }

    /** @test */
    public function it_validates_amount_is_positive()
    {
        $this->actingAs($this->user);

        Livewire::test(CashLedgerManagement::class)
            ->call('openModal')
            ->set('amount', -100)
            ->call('save')
            ->assertHasErrors(['amount' => 'min']);
    }

    /** @test */
    public function it_can_edit_existing_transaction()
    {
        $this->actingAs($this->user);

        $cashLedger = CashLedger::factory()->create([
            'type' => 'in',
            'amount' => 300000,
            'description' => 'Original description',
            'capital_tracking_id' => $this->capitalTracking->id,
            'warehouse_id' => $this->warehouse->id,
        ]);

        Livewire::test(CashLedgerManagement::class)
            ->call('edit', $cashLedger->id)
            ->set('description', 'Updated description')
            ->set('amount', 350000)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('refresh');

        $this->assertDatabaseHas('cash_ledgers', [
            'id' => $cashLedger->id,
            'description' => 'Updated description',
            'amount' => 350000,
        ]);
    }

    /** @test */
    public function it_can_delete_transaction()
    {
        $this->actingAs($this->user);

        $cashLedger = CashLedger::factory()->create([
            'type' => 'in',
            'amount' => 300000,
            'capital_tracking_id' => $this->capitalTracking->id,
        ]);

        Livewire::test(CashLedgerManagement::class)
            ->call('confirmDelete', $cashLedger->id)
            ->call('delete')
            ->assertDispatched('refresh');

        $this->assertDatabaseMissing('cash_ledgers', [
            'id' => $cashLedger->id,
        ]);
    }

    /** @test */
    public function it_can_filter_by_type()
    {
        $this->actingAs($this->user);

        CashLedger::factory()->create([
            'type' => 'in',
            'description' => 'Income transaction',
            'capital_tracking_id' => $this->capitalTracking->id,
        ]);

        CashLedger::factory()->create([
            'type' => 'out',
            'description' => 'Expense transaction',
            'capital_tracking_id' => $this->capitalTracking->id,
        ]);

        Livewire::test(CashLedgerManagement::class)
            ->set('filterType', 'in')
            ->assertSee('Income transaction')
            ->assertDontSee('Expense transaction');
    }

    /** @test */
    public function it_can_filter_by_warehouse()
    {
        $this->actingAs($this->user);

        $warehouse2 = Warehouse::factory()->create([
            'name' => 'Gudang Kedua',
            'branch' => 'Cabang Kedua',
        ]);

        CashLedger::factory()->create([
            'description' => 'Transaction warehouse 1',
            'warehouse_id' => $this->warehouse->id,
            'capital_tracking_id' => $this->capitalTracking->id,
        ]);

        CashLedger::factory()->create([
            'description' => 'Transaction warehouse 2',
            'warehouse_id' => $warehouse2->id,
            'capital_tracking_id' => $this->capitalTracking->id,
        ]);

        Livewire::test(CashLedgerManagement::class)
            ->set('filterWarehouse', $this->warehouse->id)
            ->assertSee('Transaction warehouse 1')
            ->assertDontSee('Transaction warehouse 2');
    }

    /** @test */
    public function it_can_search_by_description()
    {
        $this->actingAs($this->user);

        CashLedger::factory()->create([
            'description' => 'Penjualan produk A',
            'capital_tracking_id' => $this->capitalTracking->id,
        ]);

        CashLedger::factory()->create([
            'description' => 'Pembelian bahan baku',
            'capital_tracking_id' => $this->capitalTracking->id,
        ]);

        Livewire::test(CashLedgerManagement::class)
            ->set('searchTerm', 'Penjualan')
            ->assertSee('Penjualan produk A')
            ->assertDontSee('Pembelian bahan baku');
    }

    /** @test */
    public function it_calculates_totals_correctly()
    {
        $this->actingAs($this->user);

        // Create income transactions
        CashLedger::factory()->create([
            'type' => 'in',
            'amount' => 500000,
            'capital_tracking_id' => $this->capitalTracking->id,
        ]);

        CashLedger::factory()->create([
            'type' => 'in',
            'amount' => 300000,
            'capital_tracking_id' => $this->capitalTracking->id,
        ]);

        // Create expense transactions
        CashLedger::factory()->create([
            'type' => 'out',
            'amount' => 200000,
            'capital_tracking_id' => $this->capitalTracking->id,
        ]);

        $component = Livewire::test(CashLedgerManagement::class);

        $this->assertEquals(800000, $component->get('totalIncome'));
        $this->assertEquals(200000, $component->get('totalExpense'));
        $this->assertEquals(600000, $component->get('netBalance'));
    }

    /** @test */
    public function it_can_reset_filters()
    {
        $this->actingAs($this->user);

        Livewire::test(CashLedgerManagement::class)
            ->set('filterType', 'in')
            ->set('filterDate', '2024-01-01')
            ->set('searchTerm', 'test')
            ->call('resetFilters')
            ->assertSet('filterType', '')
            ->assertSet('filterDate', '')
            ->assertSet('searchTerm', '');
    }

    /** @test */
    public function it_can_toggle_annual_summary()
    {
        $this->actingAs($this->user);

        Livewire::test(CashLedgerManagement::class)
            ->assertSet('showAnnualSummary', false)
            ->call('toggleAnnualSummary')
            ->assertSet('showAnnualSummary', true)
            ->call('toggleAnnualSummary')
            ->assertSet('showAnnualSummary', false);
    }

    /** @test */
    public function it_prevents_insufficient_funds_for_expense()
    {
        $this->actingAs($this->user);

        // Set capital to a low amount
        $this->capitalTracking->update(['current_amount' => 100000]);

        Livewire::test(CashLedgerManagement::class)
            ->call('openModal')
            ->set('type', 'out')
            ->set('amount', 200000) // More than available
            ->set('transaction_date', now()->format('Y-m-d'))
            ->set('capital_tracking_id', $this->capitalTracking->id)
            ->set('category', 'operational')
            ->set('description', 'Biaya operasional')
            ->call('save')
            ->assertHasErrors(['amount']);
    }

    /** @test */
    public function it_can_close_modal()
    {
        $this->actingAs($this->user);

        Livewire::test(CashLedgerManagement::class)
            ->call('openModal')
            ->assertSet('showModal', true)
            ->call('closeModal')
            ->assertSet('showModal', false);
    }

    /** @test */
    public function it_resets_form_when_opening_modal()
    {
        $this->actingAs($this->user);

        Livewire::test(CashLedgerManagement::class)
            ->set('type', 'in')
            ->set('amount', 100000)
            ->call('openModal')
            ->assertSet('type', '')
            ->assertSet('amount', null);
    }
}