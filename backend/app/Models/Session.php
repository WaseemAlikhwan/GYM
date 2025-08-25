<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Session extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coach_id',
        'title',
        'description',
        'session_type',
        'start_time',
        'end_time',
        'duration_minutes',
        'status',
        'location',
        'max_participants',
        'current_participants',
        'price',
        'notes',
        'equipment_needed'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_minutes' => 'integer',
        'max_participants' => 'integer',
        'current_participants' => 'integer',
        'price' => 'decimal:2',
    ];

    // علاقة مع المستخدم (العضو)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة مع المدرب
    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', Carbon::now())
                    ->where('status', 'scheduled');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('start_time', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('start_time', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopePersonal($query)
    {
        return $query->where('session_type', 'personal');
    }

    public function scopeGroup($query)
    {
        return $query->where('session_type', 'group');
    }

    // Accessors
    public function getIsUpcomingAttribute()
    {
        return $this->start_time > Carbon::now() && $this->status === 'scheduled';
    }

    public function getIsOverdueAttribute()
    {
        return $this->start_time < Carbon::now() && $this->status === 'scheduled';
    }

    public function getHasSpaceAttribute()
    {
        return $this->current_participants < $this->max_participants;
    }

    public function getAvailableSpotsAttribute()
    {
        return max(0, $this->max_participants - $this->current_participants);
    }

    // Methods
    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->save();
    }

    public function cancel()
    {
        $this->status = 'cancelled';
        $this->save();
    }

    public function addParticipant()
    {
        if ($this->has_space) {
            $this->current_participants++;
            $this->save();
            return true;
        }
        return false;
    }

    public function removeParticipant()
    {
        if ($this->current_participants > 0) {
            $this->current_participants--;
            $this->save();
            return true;
        }
        return false;
    }
}