<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'goal_type',
        'target_value',
        'current_value',
        'unit',
        'target_date',
        'status',
        'priority',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'target_date' => 'date',
    ];

    // علاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة مع منشئ الهدف (مدرب أو مدير)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePaused($query)
    {
        return $query->where('status', 'paused');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('target_date', '>', Carbon::now());
    }

    public function scopeOverdue($query)
    {
        return $query->where('target_date', '<', Carbon::now())
                    ->where('status', 'active');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    // Accessors
    public function getProgressPercentageAttribute()
    {
        if ($this->target_value == 0) {
            return 0;
        }
        
        return min(100, round(($this->current_value / $this->target_value) * 100, 2));
    }

    public function getIsOverdueAttribute()
    {
        return $this->target_date < Carbon::now() && $this->status === 'active';
    }

    public function getDaysRemainingAttribute()
    {
        if ($this->target_date < Carbon::now()) {
            return 0;
        }
        
        return Carbon::now()->diffInDays($this->target_date);
    }

    // Methods
    public function updateProgress($newValue)
    {
        $this->current_value = $newValue;
        
        if ($newValue >= $this->target_value) {
            $this->status = 'completed';
        }
        
        $this->save();
    }

    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->current_value = $this->target_value;
        $this->save();
    }
}