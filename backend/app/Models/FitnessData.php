<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FitnessData extends Model
{
    use HasFactory;

    protected $table = 'fitness_data';
    
    protected $fillable = [
        'user_id', 
        'weight', 
        'height', 
        'bmi', 
        'fat_percent',
        'muscle_mass',
        'body_fat_percentage',
        'waist_circumference',
        'chest_circumference',
        'arm_circumference',
        'leg_circumference',
        'water_percentage',
        'bone_density',
        'metabolic_rate',
        'visceral_fat',
        'recorded_at',
        'notes'
    ];

    protected $casts = [
        'weight' => 'float',
        'height' => 'float',
        'bmi' => 'float',
        'fat_percent' => 'float',
        'muscle_mass' => 'float',
        'body_fat_percentage' => 'float',
        'waist_circumference' => 'float',
        'chest_circumference' => 'float',
        'arm_circumference' => 'float',
        'leg_circumference' => 'float',
        'water_percentage' => 'float',
        'bone_density' => 'float',
        'metabolic_rate' => 'float',
        'visceral_fat' => 'float',
        'recorded_at' => 'datetime',
    ];

    // علاقة مع المستخدم
    public function user() 
    {
        return $this->belongsTo(User::class);
    }

    // Scope للبيانات الحديثة
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Scope للبيانات في فترة معينة
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Scope للحصول على أحدث بيانات
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
