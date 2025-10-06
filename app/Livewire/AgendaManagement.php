<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Agenda;
use App\Models\PurchaseOrder;
use Illuminate\Validation\Rule;

class AgendaManagement extends Component
{
    // Form properties
    public $showModal = false;
    public $editMode = false;
    public $editingId = null;

    public $title = '';
    public $description = '';
    public $agenda_date = '';
    public $agenda_time = '';
    public $priority = 'medium';
    public $status = 'pending';
    public $related_type = null;
    public $related_id = null;
    public $completion_notes = '';

    // Filters and details
    public $filterRelatedType = '';
    public $selectedAgenda = null;
    public $detailPO = null;

    protected function rules()
    {
        if ($this->editMode) {
            return [
                'status' => ['required', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
                'completion_notes' => ['nullable', 'string'],
            ];
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'agenda_date' => ['required', 'date'],
            'agenda_time' => ['nullable', 'date_format:H:i'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            'related_type' => ['nullable', 'string'],
            'related_id' => ['nullable', 'integer'],
            'completion_notes' => ['nullable', 'string'],
        ];
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
        $this->title = '';
        $this->description = '';
        $this->agenda_date = '';
        $this->agenda_time = '';
        $this->priority = 'medium';
        $this->status = 'pending';
        $this->related_type = null;
        $this->related_id = null;
        $this->completion_notes = '';
        $this->editMode = false;
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->validate();

        if ($this->editMode && $this->editingId) {
            $agenda = Agenda::findOrFail($this->editingId);
            $agenda->update([
                'status' => $this->status,
                'completion_notes' => $this->completion_notes,
            ]);
        } else {
            Agenda::create([
                'title' => $this->title,
                'description' => $this->description,
                'agenda_date' => $this->agenda_date,
                'agenda_time' => $this->agenda_time,
                'priority' => $this->priority,
                'status' => $this->status,
                'related_type' => $this->related_type,
                'related_id' => $this->related_id,
                'completion_notes' => $this->completion_notes,
            ]);

            // Notify parent/listeners to refresh
            $this->dispatch('refresh');
        }

        $this->closeModal();
    }

    public function edit($id)
    {
        $agenda = Agenda::findOrFail($id);
        $this->editMode = true;
        $this->editingId = $id;

        $this->title = $agenda->title;
        $this->description = $agenda->description;
        $this->agenda_date = $agenda->agenda_date ? $agenda->agenda_date->format('Y-m-d') : '';
        $this->agenda_time = $agenda->agenda_time ?: '';
        $this->priority = $agenda->priority;
        $this->status = $agenda->status;
        $this->related_type = $agenda->related_type;
        $this->related_id = $agenda->related_id;
        $this->completion_notes = $agenda->completion_notes ?: '';

        $this->showModal = true;
    }

    public function viewDetails($agendaId)
    {
        $this->selectedAgenda = Agenda::findOrFail($agendaId);
        $this->detailPO = null;

        if ($this->selectedAgenda->related_type === 'purchase_order' && $this->selectedAgenda->related_id) {
            $this->detailPO = PurchaseOrder::with(['supplier'])->find($this->selectedAgenda->related_id);
        }
    }

    public function render()
    {
        $query = Agenda::query();
        if ($this->filterRelatedType) {
            $query->where('related_type', $this->filterRelatedType);
        }
        $agendas = $query->orderBy('agenda_date', 'asc')->orderBy('created_at', 'desc')->get();

        return view('livewire.agenda-management', [
            'agendas' => $agendas,
            'selectedAgenda' => $this->selectedAgenda,
            'detailPO' => $this->detailPO,
        ]);
    }
}