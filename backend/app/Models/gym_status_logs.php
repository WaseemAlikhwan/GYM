<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class gym_status_logs extends Model
{
    use HasFactory;

    protected $table = 'gym_status_logs';
    
    protected $fillable = [
        'user_id', 
        'status'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // علاقة مع المستخدم
    public function user() 
    {
        return $this->belongsTo(User::class);
    }

    // Scope للحالة المفتوحة
    public function scopeOpen($query)
    {
        return $query->where('status', 'in');
    }

    // Scope للحالة المغلقة
    public function scopeClosed($query)
    {
        return $query->where('status', 'out');
    }

    // Scope للحصول على آخر حالة
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
