<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemAchievement extends Model
{
    use HasFactory;
    
    protected $table = 'system_achievements';
    
    protected $fillable = [
        'title',
        'description',
        'type',
        'level',
        'points',
        'icon',
        'badge_image',
        'criteria',
        'is_active',
        'required_value',
        'unit'
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
        'points' => 'integer',
        'required_value' => 'integer',
    ];

    // علاقة مع إنجازات المستخدمين
    public function userAchievements()
    {
        return $this->hasMany(Achievement::class, 'system_achievement_id');
    }

    // Scope للإنجازات النشطة
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope للإنجازات حسب النوع
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope للإنجازات حسب المستوى
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    // Scope للإنجازات حسب النقاط
    public function scopeByPoints($query, $minPoints = 0, $maxPoints = null)
    {
        if ($maxPoints) {
            return $query->whereBetween('points', [$minPoints, $maxPoints]);
        }
        return $query->where('points', '>=', $minPoints);
    }

    // دالة لإنشاء إنجاز للمستخدم
    public function createForUser($userId)
    {
        return Achievement::create([
            'user_id' => $userId,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'level' => $this->level,
            'points' => $this->points,
            'icon' => $this->icon,
            'badge_image' => $this->badge_image,
            'criteria' => $this->criteria,
            'is_unlocked' => false,
        ]);
    }

    // دالة للتحقق من إمكانية فتح الإنجاز
    public function canUnlock($userData = [])
    {
        if (!$this->is_active) {
            return false; // الإنجاز غير نشط
        }

        // التحقق من المعايير
        if ($this->criteria) {
            foreach ($this->criteria as $criterion => $requiredValue) {
                if (!isset($userData[$criterion]) || $userData[$criterion] < $requiredValue) {
                    return false;
                }
            }
        }

        // التحقق من القيمة المطلوبة
        if ($this->required_value && isset($userData[$this->type])) {
            if ($userData[$this->type] < $this->required_value) {
                return false;
            }
        }

        return true;
    }

    // دالة لحساب النقاط
    public function calculatePoints()
    {
        $basePoints = $this->points;
        
        // إضافة نقاط إضافية حسب المستوى
        $levelMultiplier = [
            'bronze' => 1,
            'silver' => 2,
            'gold' => 3,
            'platinum' => 4,
            'diamond' => 5
        ];

        return $basePoints * ($levelMultiplier[$this->level] ?? 1);
    }

    // Accessor للحصول على اسم المستوى بالعربية
    public function getLevelArabicAttribute()
    {
        $levels = [
            'bronze' => 'برونزي',
            'silver' => 'فضي',
            'gold' => 'ذهبي',
            'platinum' => 'بلاتيني',
            'diamond' => 'ماسي'
        ];

        return $levels[$this->level] ?? $this->level;
    }

    // Accessor للحصول على اسم النوع بالعربية
    public function getTypeArabicAttribute()
    {
        $types = [
            'attendance' => 'الحضور',
            'weight_loss' => 'فقدان الوزن',
            'weight_gain' => 'زيادة الوزن',
            'muscle_gain' => 'بناء العضلات',
            'endurance' => 'التحمل',
            'strength' => 'القوة',
            'flexibility' => 'المرونة',
            'consistency' => 'الانتظام',
            'milestone' => 'معالم مهمة',
            'special' => 'إنجازات خاصة'
        ];

        return $types[$this->type] ?? $this->type;
    }

    // دالة للحصول على وصف المعايير
    public function getCriteriaDescriptionAttribute()
    {
        if (!$this->criteria && !$this->required_value) {
            return 'لا توجد معايير محددة';
        }

        $description = '';

        if ($this->required_value) {
            $description .= "المطلوب: {$this->required_value} {$this->unit}";
        }

        if ($this->criteria) {
            foreach ($this->criteria as $criterion => $value) {
                $criterionNames = [
                    'attendance_count' => 'عدد مرات الحضور',
                    'weight_loss' => 'فقدان الوزن',
                    'muscle_gain' => 'زيادة العضلات',
                    'consistency_days' => 'أيام الانتظام',
                    'workout_sessions' => 'جلسات التدريب',
                    'nutrition_plans' => 'خطط التغذية'
                ];

                $criterionName = $criterionNames[$criterion] ?? $criterion;
                $description .= "\n{$criterionName}: {$value}";
            }
        }

        return trim($description);
    }
}

