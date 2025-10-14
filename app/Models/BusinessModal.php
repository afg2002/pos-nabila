<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessModal extends Model
{
    use HasFactory;

    protected $table = 'business_modals';

    protected $fillable = [
        'name',
        'type',
        'description',
    ];
}