<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable ;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'gender',
        'date_of_birth',
        'address',
        'emergency_contact',
        'profile_picture',
        'fingerprint_id',
        'specialization',
        'experience_level',
        'certification',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

  public function subscriptions() {
        return $this->hasMany(Subscription::class);
    }

    public function fitnessData() {
        return $this->hasMany(FitnessData::class);
    }

    public function attendances() {
        return $this->hasMany(Attendance::class);
    }

    public function workoutPlans() {
        return $this->hasMany(WorkoutPlan::class, 'user_id');
    }

    public function nutritionPlans() {
        return $this->hasMany(NutritionPlan::class, 'user_id');
    }

    public function coachedWorkoutPlans() {
        return $this->hasMany(WorkoutPlan::class, 'coach_id');
    }

    public function coachedNutritionPlans() {
        return $this->hasMany(NutritionPlan::class, 'coach_id');
    }

    public function gymStatusLogs() {
        return $this->hasMany(GymStatusLog::class);
    }

    // علاقات المتدرب والكوتش
    public function members() {
        return $this->belongsToMany(User::class, 'coach_members', 'coach_id', 'member_id');
    }

    public function coaches() {
        return $this->belongsToMany(User::class, 'coach_members', 'member_id', 'coach_id');
    }

    // علاقة المدرب المفردة للعضو
    public function coach() {
        return $this->belongsToMany(User::class, 'coach_members', 'member_id', 'coach_id');
    }

    // علاقة مع جدول coach_members
    public function coachMemberRelationships() {
        return $this->hasMany(coach_member::class, 'coach_id');
    }

    public function memberCoachRelationships() {
        return $this->hasMany(coach_member::class, 'member_id');
    }

    // علاقة مع الأهداف
    public function goals() {
        return $this->hasMany(Goal::class);
    }

    // علاقة مع الجلسات (كعضو)
    public function sessions() {
        return $this->hasMany(Session::class, 'user_id');
    }

    // علاقة مع الجلسات (كمدرب)
    public function coachedSessions() {
        return $this->hasMany(Session::class, 'coach_id');
    }

    // علاقة مع المدفوعات
    public function payments() {
        return $this->hasMany(Payment::class);
    }

    // علاقة مع الأهداف النشطة
    public function activeGoals() {
        return $this->hasMany(Goal::class)->where('status', 'active');
    }

    // علاقة مع الجلسات القادمة
    public function upcomingSessions() {
        return $this->hasMany(Session::class, 'user_id')
            ->where('start_time', '>', now())
            ->where('status', 'scheduled');
    }

    // علاقة مع الجلسات اليوم
    public function todaySessions() {
        return $this->hasMany(Session::class, 'user_id')
            ->whereDate('start_time', today());
    }

    // علاقة مع الإنجازات
    public function achievements() {
        return $this->hasMany(Achievement::class);
    }

    // علاقة مع الإنجازات المفتوحة
    public function unlockedAchievements() {
        return $this->hasMany(Achievement::class)->where('is_unlocked', true);
    }

    // علاقة مع الإنجازات المقفلة
    public function lockedAchievements() {
        return $this->hasMany(Achievement::class)->where('is_unlocked', false);
    }

    // دالة لحساب مجموع النقاط
    public function getTotalPointsAttribute() {
        return $this->unlockedAchievements()->sum('points');
    }

    // دالة للحصول على المستوى الحالي
    public function getCurrentLevelAttribute() {
        $totalPoints = $this->total_points;
        
        if ($totalPoints >= 1000) return 'diamond';
        if ($totalPoints >= 500) return 'platinum';
        if ($totalPoints >= 200) return 'gold';
        if ($totalPoints >= 100) return 'silver';
        if ($totalPoints >= 50) return 'bronze';
        
        return 'beginner';
    }

    // دالة للتحقق من الإنجازات الجديدة
    public function checkForNewAchievements() {
        $systemAchievements = SystemAchievement::active()->get();
        $newAchievements = [];

        foreach ($systemAchievements as $systemAchievement) {
            // التحقق من وجود الإنجاز للمستخدم
            $existingAchievement = $this->achievements()
                ->where('title', $systemAchievement->title)
                ->first();

            if (!$existingAchievement) {
                // إنشاء إنجاز جديد للمستخدم
                $achievement = $systemAchievement->createForUser($this->id);
                $newAchievements[] = $achievement;
            }
        }

        return $newAchievements;
    }

    // Scope للمدربين
    public function scopeCoaches($query) {
        return $query->where('role', 'coach');
    }

    // Scope للأعضاء
    public function scopeMembers($query) {
        return $query->where('role', 'member');
    }

    // Scope للمديرين
    public function scopeAdmins($query) {
        return $query->where('role', 'admin');
    }

    // Scope للمستخدمين النشطين
    public function scopeActive($query) {
        return $query->whereHas('subscriptions', function($q) {
            $q->where('is_active', true);
        });
    }


//     public function getAgeAttribute() {
//     return \Carbon\Carbon::parse($this->birth_date)->age;
// }


}
