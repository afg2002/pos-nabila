<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agenda extends Model
{
    use HasFactory;

    protected $table = 'agendas';

    protected $fillable = [
        'title',
        'description',
        'agenda_date',
        'agenda_time',
        'priority',
        'status',
        'related_type',
        'related_id',
        'completion_notes',
        'created_by',
    ];

    protected $casts = [
        'agenda_date' => 'date',
    ];

}