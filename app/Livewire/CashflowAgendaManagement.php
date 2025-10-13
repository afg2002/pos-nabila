<?php

namespace App\Livewire;

use App\CapitalTracking;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class CashflowAgendaManagement extends Component
{
    use WithPagination;

    // Form properties
    public $date = '';
    public $total_omset = '';
    public $total_ecer = '';
    public $total_grosir = '';
    public $grosir_cash_hari_ini = '';
    public $qr_payment_amount = '';
    public $edc_payment_amount = '';
    public $notes = '';
    public $capital_tracking_id = '';
    
    public $editingId = null;

    // Modal states
    public $showModal = false;
    public $showDeleteModal = false;
    public $deleteId = null;

    // Calendar and filters
    public $currentDate;
    public $selectedDate = '';
    public $filterMonth = '';
    public $viewMode = 'calendar'; // calendar or list
    public $search = '';

    protected $rules = [
        'date' => 'required|date',
        'total_ecer' => 'required|numeric|min:0',
        'total_grosir' => 'required|numeric|min:0',
        'grosir_cash_hari_ini' => 'required|numeric|min:0',
        'qr_payment_amount' => 'required|numeric|min:0',
        'edc_payment_amount' => 'required|numeric|min:0',
        'notes' => 'nullable|string|max:1000',
        'capital_tracking_id' => 'required|exists:capital_tracking,id',
    ];

    public function mount()
    {
        $this->currentDate = now()->format('Y-m-d');
        $this->filterMonth = now()->format('Y-m');
        $this->date = now()->format('Y-m-d');
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->date = now()->format('Y-m-d');
        $this->total_omset = '';
        $this->total_ecer = '';
        $this->total_grosir = '';
        $this->grosir_cash_hari_ini = '';
        $this->qr_payment_amount = '';
        $this->edc_payment_amount = '';
        $this->notes = '';
        $this->capital_tracking_id = '';
    }

    public function edit($id)
    {
        $agenda = DB::table('cashflow_agenda')->find($id);
        
        if ($agenda) {
            $this->editingId = $id;
            $this->date = $agenda->date;
            $this->total_omset = $agenda->total_omset;
            $this->total_ecer = $agenda->total_ecer;
            $this->total_grosir = $agenda->total_grosir;
            $this->grosir_cash_hari_ini = $agenda->grosir_cash_hari_ini;
            $this->qr_payment_amount = $agenda->qr_payment_amount;
            $this->edc_payment_amount = $agenda->edc_payment_amount;
            $this->notes = $agenda->notes;
            $this->capital_tracking_id = $agenda->capital_tracking_id;
            $this->showModal = true;
        }
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'date' => $this->date,
                'total_ecer' => $this->total_ecer,
                'total_grosir' => $this->total_grosir,
                'grosir_cash_hari_ini' => $this->grosir_cash_hari_ini,
                'qr_payment_amount' => $this->qr_payment_amount,
                'edc_payment_amount' => $this->edc_payment_amount,
                'notes' => $this->notes,
                'capital_tracking_id' => $this->capital_tracking_id,
                'total_omset' => $this->total_ecer + $this->total_grosir,
                'created_by' => auth()->id(),
                'updated_at' => now(),
            ];

            if ($this->editingId) {
                DB::table('cashflow_agenda')->where('id', $this->editingId)->update($data);
                \App\Shared\Services\AlertService::success('Agenda cashflow berhasil diperbarui.');
            } else {
                $data['created_at'] = now();
                DB::table('cashflow_agenda')->insert($data);
                \App\Shared\Services\AlertService::success('Agenda cashflow berhasil ditambahkan.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            \App\Shared\Services\AlertService::error('Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        try {
            DB::table('cashflow_agenda')->where('id', $this->deleteId)->delete();
            \App\Shared\Services\AlertService::success('Agenda cashflow berhasil dihapus.');
            $this->showDeleteModal = false;
            $this->deleteId = null;
        } catch (\Exception $e) {
            \App\Shared\Services\AlertService::error('Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function render()
    {
        // Get capital trackings for dropdown
        $capitalTrackings = CapitalTracking::where('is_active', true)->get();

        // Build query
        $query = DB::table('cashflow_agenda')
            ->leftJoin('capital_tracking', 'cashflow_agenda.capital_tracking_id', '=', 'capital_tracking.id')
            ->select('cashflow_agenda.*', 'capital_tracking.name as capital_name');

        // Apply filters
        if ($this->search) {
            $query->where(function($q) {
                $q->where('notes', 'like', '%'.$this->search.'%')
                  ->orWhere('capital_tracking.name', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterMonth) {
            $query->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$this->filterMonth]);
        }

        if ($this->selectedDate) {
            $query->whereDate('date', $this->selectedDate);
        }

        $agendas = $query->orderBy('date', 'desc')->paginate(10);

        // Get statistics
        $todayRevenue = DB::table('cashflow_agenda')
            ->whereDate('date', today())
            ->sum('total_omset');

        $monthlyRevenue = DB::table('cashflow_agenda')
            ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [now()->format('Y-m')])
            ->sum('total_omset');

        // Calculate totals for summary cards
        $totalRevenue = DB::table('cashflow_agenda')->sum('total_omset');
        $totalPayment = DB::table('cashflow_agenda')
            ->sum(DB::raw('grosir_cash_hari_ini + qr_payment_amount + edc_payment_amount'));
        $netCashflow = $totalRevenue - $totalPayment;

        // Calendar data for current month
        $calendarData = [];
        if ($this->viewMode === 'calendar') {
            $currentMonth = Carbon::parse($this->filterMonth.'-01');
            $startOfMonth = $currentMonth->copy()->startOfMonth();
            $endOfMonth = $currentMonth->copy()->endOfMonth();

            // Get agendas for the month
            $monthlyAgendas = DB::table('cashflow_agenda')
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->get()
                ->groupBy('date');

            // Generate calendar grid
            $startDate = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
            $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

            $current = $startDate->copy();
            while ($current <= $endDate) {
                $dateStr = $current->format('Y-m-d');
                $calendarData[] = [
                    'date' => $dateStr,
                    'day' => $current->day,
                    'isCurrentMonth' => $current->month === $currentMonth->month,
                    'isToday' => $current->isToday(),
                    'agendas' => $monthlyAgendas->get($dateStr, collect()),
                ];
                $current->addDay();
            }
        }

        return view('livewire.cashflow-agenda-management', [
            'agendas' => $agendas,
            'capitalTrackings' => $capitalTrackings,
            'todayRevenue' => $todayRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'calendarData' => $calendarData,
            'totalRevenue' => $totalRevenue,
            'totalPayment' => $totalPayment,
            'netCashflow' => $netCashflow,
        ]);
    }
}