<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'type',
        'discount_percentage',
        'total_purchases',
        'total_transactions',
        'birth_date',
        'gender',
        'is_active',
        'notes'
    ];
    
    protected $casts = [
        'birth_date' => 'date',
        'discount_percentage' => 'decimal:2',
        'total_purchases' => 'decimal:2',
        'is_active' => 'boolean'
    ];
    
    // Relationships
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
    
    // Accessors
    public function getTypeDisplayAttribute()
    {
        return match($this->type) {
            'regular' => 'Regular',
            'member' => 'Member',
            'vip' => 'VIP',
            default => 'Regular'
        };
    }
    
    public function getGenderDisplayAttribute()
    {
        return match($this->gender) {
            'male' => 'Laki-laki',
            'female' => 'Perempuan',
            default => '-'
        };
    }
    
    // Methods
    public function updatePurchaseStats($amount)
    {
        $this->increment('total_transactions');
        $this->increment('total_purchases', $amount);
    }
}
