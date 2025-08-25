<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'points',
        'badge_icon',
        'badge_color',
        'is_unlocked',
        'unlocked_at',
        'progress_value',
        'target_value',
        'achievement_type'
    ];

    protected $casts = [
        'is_unlocked' => 'boolean',
        'unlocked_at' => 'datetime',
        'points' => 'integer',
        'progress_value' => 'decimal:2',
        'target_value' => 'decimal:2',
    ];

    // علاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnlocked($query)
    {
        return $query->where('is_unlocked', true);
    }

    public function scopeLocked($query)
    {
        return $query->where('is_unlocked', false);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeHighValue($query)
    {
        return $query->where('points', '>=', 100);
    }

    // Accessors
    public function getProgressPercentageAttribute()
    {
        if ($this->target_value == 0) {
            return 100;
        }
        
        return min(100, round(($this->progress_value / $this->target_value) * 100, 2));
    }

    public function getIsCompletedAttribute()
    {
        return $this->progress_value >= $this->target_value;
    }

    // Methods
    public function updateProgress($newValue)
    {
        $this->progress_value = $newValue;
        
        if ($newValue >= $this->target_value && !$this->is_unlocked) {
            $this->unlock();
        }
        
        $this->save();
    }

    public function unlock()
    {
        $this->is_unlocked = true;
        $this->unlocked_at = now();
        $this->progress_value = $this->target_value;
        $this->save();
        
        return $this;
    }

    public function addProgress($value)
    {
        $newValue = $this->progress_value + $value;
        $this->updateProgress($newValue);
    }
}