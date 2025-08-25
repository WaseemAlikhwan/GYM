<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NutritionPlan extends Model
{
    use HasFactory;
    
    protected $table = 'nutrition_plans';
    
    protected $fillable = [
        'user_id', 
        'coach_id', 
        'title', 
        'description', 
        'start_date', 
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    // علاقة مع العضو
    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // علاقة مع المدرب
    public function coach() 
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    // Scope للخطط النشطة
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('end_date', '>=', now());
    }

    // Scope للخطط المنتهية
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }

    // Scope للخطط القادمة
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    // Scope للخطط في فترة معينة
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
    }

    // دالة للحصول على اسم الخطة
    public function getNameAttribute()
    {
        return $this->title;
    }
}
