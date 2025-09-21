<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgendaEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'event_date',
        'event_time',
        'event_type',
        'priority',
        'status',
        'location',
        'attendees',
        'notes',
        'created_by',
        'reminder_minutes'
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_time' => 'datetime',
        'attendees' => 'array',
        'reminder_minutes' => 'integer'
    ];

    protected $dates = [
        'event_date',
        'event_time',
        'deleted_at'
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now()->toDateString())
                    ->where('status', '!=', 'cancelled');
    }

    public function scopeToday($query)
    {
        return $query->where('event_date', now()->toDateString());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Accessors
    public function getFormattedDateAttribute()
    {
        return $this->event_date ? $this->event_date->format('d/m/Y') : '-';
    }

    public function getFormattedTimeAttribute()
    {
        return $this->event_time ? $this->event_time->format('H:i') : '-';
    }

    public function getIsOverdueAttribute()
    {
        return $this->event_date && $this->event_date->isPast() && $this->status === 'pending';
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'info',
            default => 'secondary'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'completed' => 'success',
            'cancelled' => 'danger',
            'in_progress' => 'warning',
            'pending' => 'info',
            default => 'secondary'
        };
    }
}