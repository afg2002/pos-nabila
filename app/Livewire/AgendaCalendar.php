<?php

namespace App\Livewire;

use Livewire\Component;
use App\IncomingGoods;
use App\PaymentSchedule;
use App\Models\AgendaEvent;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class AgendaCalendar extends Component
{
    public $currentDate;
    public $selectedDate;
    public $viewMode = 'month'; // month, week, day
    public $showModal = false;
    public $selectedEvents = [];
    
    // Form properties for creating/editing events
    public $showEventModal = false;
    public $editMode = false;
    public $editingId = null;
    public $title = '';
    public $description = '';
    public $event_date = '';
    public $event_time = '';
    public $event_type = 'reminder';
    public $priority = 'medium';
    public $status = 'pending';
    public $location = '';
    public $attendees = '';
    public $notes = '';
    public $reminder_minutes = 15;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'event_date' => 'required|date',
        'event_time' => 'nullable|date_format:H:i',
        'event_type' => 'required|in:meeting,reminder,task,appointment,deadline',
        'priority' => 'required|in:low,medium,high,urgent',
        'status' => 'required|in:scheduled,in_progress,completed,cancelled,postponed',
        'location' => 'nullable|string|max:255',
        'attendees' => 'nullable|string',
        'notes' => 'nullable|string',
        'reminder_minutes' => 'required|integer|min:0|max:1440'
    ];

    public function mount()
    {
        // Check permission
        if (!Gate::allows('agenda.view')) {
            abort(403, 'Unauthorized access to agenda.');
        }

        $this->currentDate = now();
        $this->selectedDate = now()->format('Y-m-d');
        $this->event_date = now()->format('Y-m-d');
    }

    public function previousMonth()
    {
        $this->currentDate = $this->currentDate->subMonth();
    }

    public function nextMonth()
    {
        $this->currentDate = $this->currentDate->addMonth();
    }

    public function previousWeek()
    {
        $this->currentDate = $this->currentDate->subWeek();
    }

    public function nextWeek()
    {
        $this->currentDate = $this->currentDate->addWeek();
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->selectedEvents = $this->getEventsForDate($date);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedEvents = [];
    }

    public function changeViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    // Event CRUD Methods
    public function openEventModal()
    {
        $this->resetEventForm();
        $this->showEventModal = true;
    }

    public function closeEventModal()
    {
        $this->showEventModal = false;
        $this->resetEventForm();
    }

    public function resetEventForm()
    {
        $this->editMode = false;
        $this->editingId = null;
        $this->title = '';
        $this->description = '';
        $this->event_date = now()->format('Y-m-d');
        $this->event_time = '';
        $this->event_type = 'reminder';
        $this->priority = 'medium';
        $this->status = 'scheduled';
        $this->location = '';
        $this->attendees = '';
        $this->notes = '';
        $this->reminder_minutes = 15;
        $this->resetErrorBag();
    }

    public function saveEvent()
    {
        $this->validate();

        try {
            $attendeesArray = $this->attendees ? 
                array_map('trim', explode(',', $this->attendees)) : 
                null;

            $eventData = [
                'title' => $this->title,
                'description' => $this->description,
                'event_date' => $this->event_date,
                'event_time' => $this->event_time ?: null,
                'event_type' => $this->event_type,
                'priority' => $this->priority,
                'status' => $this->status,
                'location' => $this->location,
                'attendees' => $attendeesArray,
                'notes' => $this->notes,
                'reminder_minutes' => $this->reminder_minutes,
                'created_by' => Auth::id()
            ];

            $event = AgendaEvent::create($eventData);

            // Audit log
            AuditLog::create([
                'actor_id' => Auth::id(),
                'action' => 'create',
                'table_name' => 'agenda_events',
                'record_id' => $event->id,
                'diff_json' => json_encode(['created' => $eventData])
            ]);

            $this->closeEventModal();
            $this->dispatch('event-saved', [
                'message' => 'Event berhasil disimpan!',
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('event-error', [
                'message' => 'Gagal menyimpan event: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function editEvent($eventId)
    {
        $event = AgendaEvent::findOrFail($eventId);
        
        $this->editMode = true;
        $this->editingId = $eventId;
        $this->title = $event->title;
        $this->description = $event->description;
        $this->event_date = $event->event_date->format('Y-m-d');
        $this->event_time = $event->event_time ? $event->event_time->format('H:i') : '';
        $this->event_type = $event->event_type;
        $this->priority = $event->priority;
        $this->status = $event->status;
        $this->location = $event->location;
        $this->attendees = $event->attendees ? implode(', ', $event->attendees) : '';
        $this->notes = $event->notes;
        $this->reminder_minutes = $event->reminder_minutes;
        
        $this->showEventModal = true;
    }

    public function updateEvent()
    {
        $this->validate();

        try {
            $event = AgendaEvent::findOrFail($this->editingId);
            $oldData = $event->toArray();

            $attendeesArray = $this->attendees ? 
                array_map('trim', explode(',', $this->attendees)) : 
                null;

            $eventData = [
                'title' => $this->title,
                'description' => $this->description,
                'event_date' => $this->event_date,
                'event_time' => $this->event_time ?: null,
                'event_type' => $this->event_type,
                'priority' => $this->priority,
                'status' => $this->status,
                'location' => $this->location,
                'attendees' => $attendeesArray,
                'notes' => $this->notes,
                'reminder_minutes' => $this->reminder_minutes
            ];

            $event->update($eventData);

            // Audit log
            AuditLog::create([
                'actor_id' => Auth::id(),
                'action' => 'update',
                'table_name' => 'agenda_events',
                'record_id' => $event->id,
                'diff_json' => json_encode([
                    'old' => $oldData,
                    'new' => $eventData
                ])
            ]);

            $this->closeEventModal();
            $this->dispatch('event-updated', [
                'message' => 'Event berhasil diperbarui!',
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('event-error', [
                'message' => 'Gagal memperbarui event: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function deleteEvent($eventId)
    {
        try {
            $event = AgendaEvent::findOrFail($eventId);
            $eventData = $event->toArray();
            
            $event->delete();

            // Audit log
            AuditLog::create([
                'actor_id' => Auth::id(),
                'action' => 'delete',
                'table_name' => 'agenda_events',
                'record_id' => $eventId,
                'diff_json' => json_encode(['deleted' => $eventData])
            ]);

            $this->dispatch('event-deleted', [
                'message' => 'Event berhasil dihapus!',
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('event-error', [
                'message' => 'Gagal menghapus event: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function getCalendarDaysProperty()
    {
        $startOfMonth = $this->currentDate->copy()->startOfMonth();
        $endOfMonth = $this->currentDate->copy()->endOfMonth();
        
        // Mulai dari hari Senin minggu pertama
        $startDate = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);
        
        $days = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $days[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->day,
                'isCurrentMonth' => $current->month === $this->currentDate->month,
                'isToday' => $current->isToday(),
                'events' => $this->getEventsForDate($current->format('Y-m-d'))
            ];
            $current->addDay();
        }
        
        return collect($days)->chunk(7); // Group by weeks
    }

    public function getWeekDaysProperty()
    {
        $startOfWeek = $this->currentDate->copy()->startOfWeek(Carbon::MONDAY);
        $days = [];
        
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $days[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->day,
                'dayName' => $date->format('l'),
                'isToday' => $date->isToday(),
                'events' => $this->getEventsForDate($date->format('Y-m-d'))
            ];
        }
        
        return $days;
    }

    private function getEventsForDate($date): Collection
    {
        $events = collect();
        
        // Get custom agenda events
        $agendaEvents = AgendaEvent::whereDate('event_date', $date)
            ->orderBy('event_time')
            ->get();
            
        foreach ($agendaEvents as $event) {
            $events->push([
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'time' => $event->event_time ? $event->event_time->format('H:i') : 'All Day',
                'type' => $event->event_type,
                'priority' => $event->priority,
                'status' => $event->status,
                'color' => $event->priority_color,
                'source' => 'agenda_event',
                'data' => $event
            ]);
        }
        
        // Barang yang diharapkan datang
        $incomingGoods = IncomingGoods::where('expected_date', $date)
            ->with(['creator'])
            ->get();
            
        foreach ($incomingGoods as $goods) {
            $events->push([
                'id' => $goods->id,
                'type' => 'incoming',
                'title' => "Barang Datang: {$goods->supplier_name}",
                'description' => "Invoice: {$goods->invoice_number}",
                'amount' => $goods->total_cost,
                'status' => $goods->status,
                'time' => 'All Day',
                'color' => 'bg-blue-500',
                'source' => 'incoming_goods',
                'data' => $goods
            ]);
        }
        
        // Barang yang sudah datang
        $arrivedGoods = IncomingGoods::where('actual_arrival_date', $date)
            ->with(['creator'])
            ->get();
            
        foreach ($arrivedGoods as $goods) {
            $events->push([
                'id' => $goods->id,
                'type' => 'arrived',
                'title' => "Barang Tiba: {$goods->supplier_name}",
                'description' => "Invoice: {$goods->invoice_number}",
                'amount' => $goods->total_cost,
                'status' => $goods->status,
                'time' => 'All Day',
                'color' => 'bg-green-500',
                'source' => 'arrived_goods',
                'data' => $goods
            ]);
        }
        
        // Jadwal pembayaran jatuh tempo
        $paymentSchedules = PaymentSchedule::where('due_date', $date)
            ->with(['incomingGoods', 'creator'])
            ->get();
            
        foreach ($paymentSchedules as $schedule) {
            $events->push([
                'id' => $schedule->id,
                'type' => 'payment',
                'title' => "Jatuh Tempo: {$schedule->incomingGoods->supplier_name}",
                'description' => "Pembayaran Invoice: {$schedule->incomingGoods->invoice_number}",
                'amount' => $schedule->amount,
                'status' => $schedule->status,
                'time' => 'All Day',
                'color' => 'bg-red-500',
                'source' => 'payment_schedule',
                'data' => $schedule
            ]);
        }
        
        return $events->sortBy('title');
    }

    public function getUpcomingEventsProperty()
    {
        $events = collect();
        $startDate = now();
        $endDate = now()->addDays(7);
        
        // Barang yang akan datang minggu ini
        $incomingGoods = IncomingGoods::whereBetween('expected_date', [$startDate, $endDate])
            ->where('status', 'pending')
            ->with(['creator'])
            ->orderBy('expected_date')
            ->get();
            
        foreach ($incomingGoods as $goods) {
            $events->push([
                'type' => 'incoming',
                'date' => $goods->expected_date,
                'title' => "Barang Datang: {$goods->supplier_name}",
                'description' => "Invoice: {$goods->invoice_number}",
                'amount' => $goods->total_cost,
                'status' => $goods->status,
                'urgency' => $goods->expected_date->isToday() ? 'high' : ($goods->expected_date->isTomorrow() ? 'medium' : 'low')
            ]);
        }
        
        // Pembayaran yang jatuh tempo minggu ini
        $paymentSchedules = PaymentSchedule::whereBetween('due_date', [$startDate, $endDate])
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->with(['incomingGoods'])
            ->orderBy('due_date')
            ->get();
            
        foreach ($paymentSchedules as $schedule) {
            $events->push([
                'type' => 'payment',
                'date' => $schedule->due_date,
                'title' => "Jatuh Tempo: {$schedule->incomingGoods->supplier_name}",
                'description' => "Pembayaran Invoice: {$schedule->incomingGoods->invoice_number}",
                'amount' => $schedule->remaining_amount,
                'status' => $schedule->status,
                'urgency' => $schedule->due_date->isPast() ? 'high' : ($schedule->due_date->isToday() ? 'high' : ($schedule->due_date->isTomorrow() ? 'medium' : 'low'))
            ]);
        }
        
        return $events->sortBy('date');
    }

    public function render()
    {
        return view('livewire.agenda-calendar', [
            'calendarDays' => $this->calendarDays,
            'weekDays' => $this->weekDays,
            'upcomingEvents' => $this->upcomingEvents,
            'currentMonthName' => $this->currentDate->format('F Y'),
            'currentWeekRange' => $this->currentDate->startOfWeek(Carbon::MONDAY)->format('M d') . ' - ' . $this->currentDate->endOfWeek(Carbon::SUNDAY)->format('M d, Y')
        ]);
    }
}
