<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';
    
    protected $fillable = [
        'user_id', 
        'check_in_time', 
        'check_out_time'
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    // علاقة مع المستخدم
    public function user() 
    {
        return $this->belongsTo(User::class);
    }

    // Scope للحضور اليوم
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // Scope للحضور في فترة معينة
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Scope للحضور النشط (لم يخرج بعد)
    public function scopeActive($query)
    {
        return $query->whereNotNull('check_in_time')->whereNull('check_out_time');
    }

    // Scope للحضور المكتمل
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('check_in_time')->whereNotNull('check_out_time');
    }
}
