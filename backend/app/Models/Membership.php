<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;
    
    protected $table = 'memberships';
    
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_days',
        'features',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    // علاقة مع الاشتراكات
    public function subscriptions() 
    {
        return $this->hasMany(Subscription::class);
    }

    // Scope للعضويات النشطة
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope للعضويات بترتيب السعر
    public function scopeByPrice($query, $order = 'asc')
    {
        return $query->orderBy('price', $order);
    }

    // Scope للعضويات حسب المدة
    public function scopeByDuration($query, $duration)
    {
        return $query->where('duration_days', $duration);
    }
}
