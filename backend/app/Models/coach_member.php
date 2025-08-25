<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class coach_member extends Model
{
    use HasFactory;
    
    protected $table = 'coach_members';
    
    protected $fillable = [
        'coach_id',
        'member_id',
        'assigned_at',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // علاقة مع المدرب
    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    // علاقة مع العضو
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}
