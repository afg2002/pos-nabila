<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashflowAgendaCalendar extends Component
{
    public $currentMonth;
    public $selectedDate = '';
    public $showModal = false;
    public $selectedAgenda = null;
    
    // New properties for create modal
    public $showCreateModal = false;
    public $showDetailsModal = false;
    public $selectedDateAgendas;
    public $createForm = [
        'total_ecer' => '',
        'total_grosir' => '',
        'grosir_cash_hari_ini' => '',
        'qr_payment_amount' => '',
        'edc_payment_amount' => '',
        'notes' => '',
        'capital_tracking_id' => '',
    ];

    protected $listeners = ['refreshCalendar' => '$refresh'];

    public function mount()
    {
        $this->currentMonth = now();
        $this->selectedDate = now()->format('Y-m-d');
        $this->selectedDateAgendas = collect();
    }

    public function previousMonth()
    {
        $this->currentMonth = $this->currentMonth->copy()->subMonth();
    }

    public function nextMonth()
    {
        $this->currentMonth = $this->currentMonth->copy()->addMonth();
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->dispatch('dateSelected', $date);
        
        // Check if there are existing agendas for this date
        $existingAgendas = DB::table('cashflow_agenda')
            ->where('date', $date)
            ->get();
            
        if ($existingAgendas->isEmpty()) {
            // No existing agendas, open modal to create new one
            $this->openCreateModal($date);
        } else {
            // Show existing agendas
            $this->viewAgendaDetails($date);
        }
    }
    
    public function openCreateModal($date)
    {
        $this->selectedDate = $date;
        $this->showCreateModal = true;
    }
    
    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }
    
    public function resetCreateForm()
    {
        $this->createForm = [
            'total_ecer' => '',
            'total_grosir' => '',
            'grosir_cash_hari_ini' => '',
            'qr_payment_amount' => '',
            'edc_payment_amount' => '',
            'notes' => '',
            'capital_tracking_id' => '',
        ];
    }
    
    public function saveAgenda()
    {
        $this->validate([
            'createForm.total_ecer' => 'required|numeric|min:0',
            'createForm.total_grosir' => 'required|numeric|min:0',
            'createForm.grosir_cash_hari_ini' => 'required|numeric|min:0',
            'createForm.qr_payment_amount' => 'required|numeric|min:0',
            'createForm.edc_payment_amount' => 'required|numeric|min:0',
            'createForm.notes' => 'nullable|string|max:1000',
            'createForm.capital_tracking_id' => 'required|exists:capital_tracking,id',
        ]);
        
        try {
            $data = [
                'date' => $this->selectedDate,
                'total_ecer' => $this->createForm['total_ecer'],
                'total_grosir' => $this->createForm['total_grosir'],
                'grosir_cash_hari_ini' => $this->createForm['grosir_cash_hari_ini'],
                'qr_payment_amount' => $this->createForm['qr_payment_amount'],
                'edc_payment_amount' => $this->createForm['edc_payment_amount'],
                'notes' => $this->createForm['notes'],
                'capital_tracking_id' => $this->createForm['capital_tracking_id'],
                'total_omset' => $this->createForm['total_ecer'] + $this->createForm['total_grosir'],
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            DB::table('cashflow_agenda')->insert($data);
            
            $this->closeCreateModal();
            session()->flash('message', 'Agenda cashflow berhasil ditambahkan!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function viewAgendaDetails($date)
    {
        $this->selectedDateAgendas = DB::table('cashflow_agenda')
            ->leftJoin('capital_tracking', 'cashflow_agenda.capital_tracking_id', '=', 'capital_tracking.id')
            ->leftJoin('users', 'cashflow_agenda.created_by', '=', 'users.id')
            ->select('cashflow_agenda.*', 'capital_tracking.name as capital_name', 'users.name as created_by_name')
            ->where('cashflow_agenda.date', $date)
            ->get();
            
        $this->showDetailsModal = true;
    }
    
    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedDateAgendas = collect();
    }

    public function viewAgenda($agendaId)
    {
        $this->selectedAgenda = DB::table('cashflow_agenda')
            ->leftJoin('capital_tracking', 'cashflow_agenda.capital_tracking_id', '=', 'capital_tracking.id')
            ->leftJoin('users', 'cashflow_agenda.created_by', '=', 'users.id')
            ->select('cashflow_agenda.*', 'capital_tracking.name as capital_name', 'users.name as created_by_name')
            ->where('cashflow_agenda.id', $agendaId)
            ->first();

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedAgenda = null;
    }

    public function render()
    {
        $currentMonthDate = $this->currentMonth->copy()->startOfMonth();
        $startOfMonth = $currentMonthDate->copy()->startOfMonth();
        $endOfMonth = $currentMonthDate->copy()->endOfMonth();

        // Get agendas for the month
        $monthlyAgendas = DB::table('cashflow_agenda')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->groupBy('date');

        // Generate calendar grid
        $startDate = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $calendarData = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $dayAgendas = $monthlyAgendas->get($dateStr, collect());
            
            $calendarData[] = [
                'date' => $dateStr,
                'day' => $current->day,
                'isCurrentMonth' => $current->month === $currentMonthDate->month,
                'isToday' => $current->isToday(),
                'isSelected' => $dateStr === $this->selectedDate,
                'agendas' => $dayAgendas,
                'totalOmset' => $dayAgendas->sum('total_omset'),
                'totalPayments' => $dayAgendas->sum(function($agenda) {
                    return $agenda->grosir_cash_hari_ini + $agenda->qr_payment_amount + $agenda->edc_payment_amount;
                }),
            ];
            $current->addDay();
        }

        // Get summary statistics for the month
        $monthStats = DB::table('cashflow_agenda')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->selectRaw('
                COUNT(*) as total_days,
                SUM(total_omset) as total_omset,
                SUM(total_ecer) as total_ecer,
                SUM(total_grosir) as total_grosir,
                SUM(grosir_cash_hari_ini) as total_cash,
                SUM(qr_payment_amount) as total_qr,
                SUM(edc_payment_amount) as total_edc
            ')
            ->first();

        // Get capital trackings for dropdown
        $capitalTrackings = \App\CapitalTracking::where('is_active', true)->get();

        return view('livewire.cashflow-agenda-calendar', [
            'calendarData' => $calendarData,
            'currentMonth' => $currentMonthDate,
            'monthStats' => $monthStats,
            'capitalTrackings' => $capitalTrackings,
        ]);
    }
}