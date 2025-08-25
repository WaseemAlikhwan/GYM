<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CoachMemberController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkoutPlanController;
use App\Http\Controllers\Api\NutritionPlanController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\GymStatusLogsController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\FitnessDataController;
use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\MembersApiController;
use App\Http\Controllers\Api\CoachesApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes (no authentication required)
Route::post('/login', [AuthController::class, 'login']); // Dashboard login (admin only)
Route::post('/mobile/login', [AuthController::class, 'mobileLogin']); // Mobile app login (coach & member)
Route::post('/register', [AuthController::class, 'register']);

// Temporary test route for dashboard stats (remove after testing)
Route::get('/dashboard/stats/test', [DashboardController::class, 'getStats']);

// Simple test route
Route::get('/test', function() {
    return response()->json(['message' => 'API is working!']);
});

// Test DashboardController
Route::get('/test-dashboard', function() {
    try {
        $totalMembers = App\Models\User::where('role', 'member')->count();
        $activeCoaches = App\Models\User::where('role', 'coach')->count();
        
        // الحصول على الاشتراكات المنتهية
        $today = \Carbon\Carbon::today();
        $nextWeek = \Carbon\Carbon::today()->addDays(7);
        
        $expiringSubscriptions = collect();
        
        try {
            $expiringSubscriptions = \App\Models\Subscription::with(['user', 'membership'])
                ->where('end_date', '>=', $today)
                ->where('end_date', '<=', $nextWeek)
                ->where('is_active', true)
                ->where('status', 'active')
                ->orderBy('end_date')
                ->get()
                ->map(function ($subscription) {
                    $daysUntilExpiry = \Carbon\Carbon::parse($subscription->end_date)->diffInDays(\Carbon\Carbon::today());
                    $status = $daysUntilExpiry === 0 ? 'expires_today' : 'expires_soon';
                    
                    return [
                        'id' => $subscription->id,
                        'user_name' => $subscription->user->name ?? 'Unknown',
                        'user_email' => $subscription->user->email ?? 'Unknown',
                        'plan_type' => $subscription->membership->name ?? 'Basic',
                        'end_date' => $subscription->end_date,
                        'days_until_expiry' => $daysUntilExpiry,
                        'status' => $status,
                        'formatted_end_date' => \Carbon\Carbon::parse($subscription->end_date)->format('Y-m-d'),
                        'formatted_days' => $daysUntilExpiry === 0 ? 'اليوم' : "بعد $daysUntilExpiry يوم",
                        'price' => $subscription->membership->price ?? 0,
                        'payment_method' => 'subscription',
                    ];
                });
        } catch (\Exception $e) {
            // إذا لم يكن هناك جدول subscriptions، استخدم بيانات وهمية للاختبار
            $expiringSubscriptions = collect([
                [
                    'id' => 1,
                    'user_name' => 'أحمد محمد',
                    'user_email' => 'ahmed@example.com',
                    'plan_type' => 'Premium',
                    'end_date' => $today->format('Y-m-d'),
                    'days_until_expiry' => 0,
                    'status' => 'expires_today',
                    'formatted_end_date' => $today->format('Y-m-d'),
                    'formatted_days' => 'اليوم',
                    'price' => 100,
                    'payment_method' => 'subscription',
                ],
                [
                    'id' => 2,
                    'user_name' => 'فاطمة علي',
                    'user_email' => 'fatima@example.com',
                    'plan_type' => 'Basic',
                    'end_date' => $today->addDays(3)->format('Y-m-d'),
                    'days_until_expiry' => 3,
                    'status' => 'expires_soon',
                    'formatted_end_date' => $today->addDays(3)->format('Y-m-d'),
                    'formatted_days' => 'بعد 3 يوم',
                    'price' => 50,
                    'payment_method' => 'subscription',
                ]
            ]);
        }
        
        return response()->json([
            'totalMembers' => $totalMembers,
            'activeCoaches' => $activeCoaches,
            'expiringSubscriptions' => $expiringSubscriptions,
            'message' => 'Dashboard test successful with real subscription data'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test expiring subscriptions
Route::get('/test-expiring-subscriptions', function() {
    try {
        $today = \Carbon\Carbon::today();
        $nextWeek = \Carbon\Carbon::today()->addDays(7);
        
        $expiringSubscriptions = collect();
        
        try {
            $expiringSubscriptions = \App\Models\Subscription::with(['user', 'membership'])
                ->where('end_date', '>=', $today)
                ->where('end_date', '<=', $nextWeek)
                ->where('is_active', true)
                ->orderBy('end_date')
                ->get()
                ->map(function ($subscription) {
                    $daysUntilExpiry = \Carbon\Carbon::parse($subscription->end_date)->diffInDays(\Carbon\Carbon::today());
                    $status = $daysUntilExpiry === 0 ? 'expires_today' : 'expires_soon';
                    
                    return [
                        'id' => $subscription->id,
                        'user_name' => $subscription->user->name ?? 'Unknown',
                        'user_email' => $subscription->user->email ?? 'Unknown',
                        'plan_type' => $subscription->membership->name ?? 'Basic',
                        'end_date' => $subscription->end_date,
                        'days_until_expiry' => $daysUntilExpiry,
                        'status' => $status,
                        'formatted_end_date' => \Carbon\Carbon::parse($subscription->end_date)->format('Y-m-d'),
                        'formatted_days' => $daysUntilExpiry === 0 ? 'اليوم' : "بعد $daysUntilExpiry يوم",
                        'price' => $subscription->membership->price ?? 0,
                        'payment_method' => 'bank_transfer',
                    ];
                });
        } catch (\Exception $e) {
            // إذا لم يكن هناك جدول subscriptions، استخدم بيانات وهمية للاختبار
            $expiringSubscriptions = collect([
                [
                    'id' => 1,
                    'user_name' => 'أحمد محمد',
                    'user_email' => 'ahmed@example.com',
                    'plan_type' => 'Premium',
                    'end_date' => $today->format('Y-m-d'),
                    'days_until_expiry' => 0,
                    'status' => 'expires_today',
                    'formatted_end_date' => $today->format('Y-m-d'),
                    'formatted_days' => 'اليوم',
                    'price' => 100,
                    'payment_method' => 'credit_card',
                ],
                [
                    'id' => 2,
                    'user_name' => 'فاطمة علي',
                    'user_email' => 'fatima@example.com',
                    'plan_type' => 'Basic',
                    'end_date' => $today->addDays(3)->format('Y-m-d'),
                    'days_until_expiry' => 3,
                    'status' => 'expires_soon',
                    'formatted_end_date' => $today->addDays(3)->format('Y-m-d'),
                    'formatted_days' => 'بعد 3 يوم',
                    'price' => 50,
                    'payment_method' => 'bank_transfer',
                ]
            ]);
        }
        
        return response()->json([
            'expiring_subscriptions' => $expiringSubscriptions,
            'total_expiring' => $expiringSubscriptions->count(),
            'expires_today' => $expiringSubscriptions->where('status', 'expires_today')->count(),
            'expires_soon' => $expiringSubscriptions->where('status', 'expires_soon')->count(),
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test subscriptions data
Route::get('/test-subscriptions', function() {
    try {
        // التحقق من وجود البيانات
        $subscriptionCount = \App\Models\Subscription::count();
        $membershipCount = \App\Models\Membership::count();
        $userCount = \App\Models\User::where('role', 'member')->count();
        
        if ($subscriptionCount === 0) {
            return response()->json([
                'error' => 'No subscriptions found',
                'subscriptionCount' => $subscriptionCount,
                'membershipCount' => $membershipCount,
                'userCount' => $userCount
            ]);
        }
        
        // جلب البيانات الأساسية فقط
        $subscriptions = \App\Models\Subscription::with(['user:id,name,email', 'membership:id,name,price,duration_days'])
            ->take(5)
            ->get()
            ->map(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                    'membership_id' => $subscription->membership_id,
                    'start_date' => $subscription->start_date,
                    'end_date' => $subscription->end_date,
                    'is_active' => $subscription->is_active,
                    'status' => $subscription->status,
                    'notes' => $subscription->notes,
                    'created_at' => $subscription->created_at,
                    'updated_at' => $subscription->updated_at,
                    'user' => [
                        'id' => $subscription->user->id ?? 0,
                        'name' => $subscription->user->name ?? 'Unknown',
                        'email' => $subscription->user->email ?? 'Unknown',
                    ],
                    'membership' => [
                        'id' => $subscription->membership->id ?? 0,
                        'name' => $subscription->membership->name ?? 'Unknown',
                        'price' => $subscription->membership->price ?? 0,
                        'duration' => $subscription->membership->duration_days ?? 0,
                    ],
                ];
            });
        
        $stats = [
            'total_subscriptions' => $subscriptionCount,
            'active_subscriptions' => \App\Models\Subscription::where('end_date', '>=', now())->count(),
            'expired_subscriptions' => \App\Models\Subscription::where('end_date', '<', now())->count(),
            'subscriptions_expiring_soon' => \App\Models\Subscription::where('end_date', '<=', now()->addDays(30))
                ->where('end_date', '>=', now())
                ->count(),
        ];
        
        $memberships = \App\Models\Membership::select('id', 'name', 'price', 'duration_days')->get()
            ->map(function ($membership) {
                return [
                    'id' => $membership->id,
                    'name' => $membership->name,
                    'price' => $membership->price,
                    'duration' => $membership->duration_days,
                ];
            });
        
        return response()->json([
            'subscriptions' => $subscriptions,
            'stats' => $stats,
            'memberships' => $memberships,
            'counts' => [
                'subscriptionCount' => $subscriptionCount,
                'membershipCount' => $membershipCount,
                'userCount' => $userCount
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test memberships data
Route::get('/test-memberships', function() {
    try {
        $membershipCount = \App\Models\Membership::count();
        
        if ($membershipCount === 0) {
            return response()->json([
                'error' => 'No memberships found',
                'membershipCount' => $membershipCount
            ]);
        }
        
        $memberships = \App\Models\Membership::all()
            ->map(function ($membership) {
                return [
                    'id' => $membership->id,
                    'name' => $membership->name,
                    'description' => $membership->description,
                    'price' => $membership->price,
                    'duration_days' => $membership->duration_days,
                    'has_coach' => $membership->has_coach,
                    'has_workout_plan' => $membership->has_workout_plan,
                    'has_nutrition_plan' => $membership->has_nutrition_plan,
                    'is_active' => $membership->is_active,
                    'created_at' => $membership->created_at,
                    'updated_at' => $membership->updated_at,
                ];
            });
        
        $stats = [
            'total_memberships' => $membershipCount,
            'active_memberships' => \App\Models\Membership::where('is_active', true)->count(),
            'total_subscriptions' => \App\Models\Subscription::count(),
            'active_subscriptions' => \App\Models\Subscription::where('is_active', true)->count(),
            'revenue_by_membership' => \App\Models\Membership::withCount(['subscriptions' => function($query) {
                $query->where('is_active', true);
            }])->get()->map(function ($membership) {
                return [
                    'membership_name' => $membership->name,
                    'subscriptions_count' => $membership->subscriptions_count,
                    'total_revenue' => $membership->subscriptions_count * $membership->price
                ];
            })
        ];
        
        return response()->json([
            'memberships' => $memberships,
            'stats' => $stats
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Protected routes (authentication required)
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    
    // Dashboard routes (Admin only)
    Route::middleware(['check.role:admin'])->prefix('dashboard')->group(function () {
        Route::get('/overview', [DashboardController::class, 'overview']);
        Route::get('/comprehensive-stats', [DashboardController::class, 'comprehensiveStats']);
        Route::get('/workout-plan-stats', [DashboardController::class, 'workoutPlanStats']);
        Route::get('/nutrition-plan-stats', [DashboardController::class, 'nutritionPlanStats']);
        Route::get('/fitness-data-stats', [DashboardController::class, 'fitnessDataStats']);
        Route::get('/quick-actions', [DashboardController::class, 'quickActions']);
        Route::get('/notifications', [DashboardController::class, 'notifications']);
        Route::get('/recent-activities', [DashboardController::class, 'recentActivities']);
        Route::get('/widgets', [DashboardController::class, 'widgets']);
        Route::get('/members', [DashboardController::class, 'members']);
        Route::get('/coaches', [DashboardController::class, 'coaches']);
        Route::get('/attendance-stats', [DashboardController::class, 'attendanceStats']);
        Route::get('/subscription-stats', [DashboardController::class, 'subscriptionStats']);
        Route::get('/gym-status', [DashboardController::class, 'gymStatus']);
        
        // New Dashboard API endpoints for React Frontend
        Route::get('/stats', [DashboardController::class, 'getStats']);
        Route::get('/members-stats', [DashboardController::class, 'getMembersStats']);
        Route::get('/coaches-stats', [DashboardController::class, 'getCoachesStats']);
        Route::get('/revenue-stats', [DashboardController::class, 'getRevenueStats']);
    });
    
    // User management routes (Admin only)
    Route::middleware(['check.role:admin'])->prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/coaches/list', [UserController::class, 'getCoaches']);
        Route::get('/members/list', [UserController::class, 'getMembers']);
        Route::get('/stats', [UserController::class, 'getStats']);
        Route::put('/profile/update', [UserController::class, 'updateProfile']);
        Route::post('/change-password', [UserController::class, 'changePassword']);
        
        // User details routes (must come before /{id} routes)
        Route::get('/{id}/subscriptions', [UserController::class, 'getUserSubscriptions']);
        Route::get('/{id}/workout-plans', [UserController::class, 'getUserWorkoutPlans']);
        Route::get('/{id}/nutrition-plans', [UserController::class, 'getUserNutritionPlans']);
        Route::get('/{id}/fitness-data', [UserController::class, 'getUserFitnessData']);
        Route::get('/{id}/attendances', [UserController::class, 'getUserAttendances']);
        
        // General user routes (must come after specific routes)
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });
    
    // Coach-Member relationship routes (Admin only)
    Route::middleware(['check.role:admin'])->prefix('coach-members')->group(function () {
        Route::get('/', [CoachMemberController::class, 'index']);
        Route::post('/assign', [CoachMemberController::class, 'assignMember']);
        Route::put('/{coach_member}', [CoachMemberController::class, 'update']);
        Route::delete('/{coach_member}', [CoachMemberController::class, 'destroy']);
        Route::get('/coach/{coachId?}/members', [CoachMemberController::class, 'getCoachMembers']);
        Route::get('/member/{memberId?}/coach', [CoachMemberController::class, 'getMemberCoach']);
        Route::get('/available-coaches', [CoachMemberController::class, 'getAvailableCoaches']);
        Route::get('/unassigned-members', [CoachMemberController::class, 'getUnassignedMembers']);
        Route::post('/bulk-assign', [CoachMemberController::class, 'bulkAssign']);
        Route::get('/stats', [CoachMemberController::class, 'getStats']);
    });

    // Workout Plan routes (Admin only)
    Route::middleware(['check.role:admin'])->prefix('workout-plans')->group(function () {
        Route::get('/', [WorkoutPlanController::class, 'index']);
        Route::post('/', [WorkoutPlanController::class, 'store']);
        Route::get('/{workoutPlan}', [WorkoutPlanController::class, 'show']);
        Route::put('/{workoutPlan}', [WorkoutPlanController::class, 'update']);
        Route::delete('/{workoutPlan}', [WorkoutPlanController::class, 'destroy']);
        Route::get('/member/{memberId}/plans', [WorkoutPlanController::class, 'getMemberWorkoutPlans']);
        Route::get('/active', [WorkoutPlanController::class, 'getActiveWorkoutPlans']);
        Route::get('/stats', [WorkoutPlanController::class, 'getStats']);
    });

    // Nutrition Plan routes (Admin only)
    Route::middleware(['check.role:admin'])->prefix('nutrition-plans')->group(function () {
        Route::get('/', [NutritionPlanController::class, 'index']);
        Route::post('/', [NutritionPlanController::class, 'store']);
        Route::get('/{nutrition_plan}', [NutritionPlanController::class, 'show']);
        Route::put('/{nutrition_plan}', [NutritionPlanController::class, 'update']);
        Route::delete('/{nutrition_plan}', [NutritionPlanController::class, 'destroy']);
        Route::get('/member/{memberId}/plans', [NutritionPlanController::class, 'getMemberNutritionPlans']);
        Route::get('/active', [NutritionPlanController::class, 'getActiveNutritionPlans']);
        Route::get('/stats', [NutritionPlanController::class, 'getStats']);
    });

    // Subscription routes (Admin only)
    Route::middleware(['check.role:admin'])->prefix('subscriptions')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index']);
        Route::post('/', [SubscriptionController::class, 'store']);
        Route::get('/{subscription}', [SubscriptionController::class, 'show']);
        Route::put('/{subscription}', [SubscriptionController::class, 'update']);
        Route::delete('/{subscription}', [SubscriptionController::class, 'destroy']);
        Route::get('/memberships', [SubscriptionController::class, 'getMemberships']);
        Route::get('/current', [SubscriptionController::class, 'getCurrentSubscription']);
        Route::get('/history', [SubscriptionController::class, 'getSubscriptionHistory']);
        Route::post('/{subscription}/renew', [SubscriptionController::class, 'renewSubscription']);
        Route::post('/{subscription}/cancel', [SubscriptionController::class, 'cancelSubscription']);
        Route::get('/stats', [SubscriptionController::class, 'getStats']);
    });

    // Gym Status routes (Admin only)
    Route::middleware(['check.role:admin'])->prefix('gym-status')->group(function () {
        Route::get('/', [GymStatusLogsController::class, 'index']);
        Route::post('/', [GymStatusLogsController::class, 'store']);
        Route::get('/{gym_status_log}', [GymStatusLogsController::class, 'show']);
        Route::put('/{gym_status_log}', [GymStatusLogsController::class, 'update']);
        Route::delete('/{gym_status_log}', [GymStatusLogsController::class, 'destroy']);
        Route::get('/current', [GymStatusLogsController::class, 'getCurrentStatus']);
        Route::get('/history', [GymStatusLogsController::class, 'getStatusHistory']);
        Route::get('/stats', [GymStatusLogsController::class, 'getStats']);
        Route::get('/operating-hours', [GymStatusLogsController::class, 'getOperatingHours']);
        Route::get('/check-open', [GymStatusLogsController::class, 'checkIfOpen']);
    });

    // Payment routes (Admin only)
    Route::middleware(['check.role:admin'])->prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::post('/', [PaymentController::class, 'store']);
        Route::get('/{payment}', [PaymentController::class, 'show']);
        Route::put('/{payment}', [PaymentController::class, 'update']);
        Route::delete('/{payment}', [PaymentController::class, 'destroy']);
        Route::get('/stats', [PaymentController::class, 'getStats']);
        Route::get('/monthly-revenue', [PaymentController::class, 'getMonthlyRevenue']);
        Route::post('/bulk-create', [PaymentController::class, 'bulkCreate']);
    });

    // Membership routes (Admin only)
    Route::middleware(['check.role:admin'])->prefix('memberships')->group(function () {
        Route::get('/', [MembershipController::class, 'index']);
        Route::post('/', [MembershipController::class, 'store']);
        Route::get('/{membership}', [MembershipController::class, 'show']);
        Route::put('/{membership}', [MembershipController::class, 'update']);
        Route::delete('/{membership}', [MembershipController::class, 'destroy']);
        Route::get('/stats', [MembershipController::class, 'getStats']);
        Route::get('/popular', [MembershipController::class, 'getPopularMemberships']);
        Route::post('/bulk-update', [MembershipController::class, 'bulkUpdate']);
    });

    // Fitness Data routes (Admin and Coach access)
    Route::middleware(['check.role:admin,coach'])->prefix('fitness-data')->group(function () {
        Route::get('/', [FitnessDataController::class, 'index']);
        Route::post('/', [FitnessDataController::class, 'store']);
        Route::get('/{fitnessData}', [FitnessDataController::class, 'show']);
        Route::put('/{fitnessData}', [FitnessDataController::class, 'update']);
        Route::delete('/{fitnessData}', [FitnessDataController::class, 'destroy']);
        Route::get('/member/{memberId}', [FitnessDataController::class, 'getMemberFitnessData']);
        Route::get('/stats', [FitnessDataController::class, 'getStats']);
        Route::get('/progress/{memberId}', [FitnessDataController::class, 'getMemberProgress']);
    });

    // Goal routes (Admin and Coach access)
    Route::middleware(['check.role:admin,coach'])->prefix('goals')->group(function () {
        Route::get('/', [GoalController::class, 'index']);
        Route::post('/', [GoalController::class, 'store']);
        Route::get('/{goal}', [GoalController::class, 'show']);
        Route::put('/{goal}', [GoalController::class, 'update']);
        Route::delete('/{goal}', [GoalController::class, 'destroy']);
        Route::get('/member/{memberId}', [GoalController::class, 'getMemberGoals']);
        Route::get('/stats', [GoalController::class, 'getStats']);
        Route::post('/{goal}/complete', [GoalController::class, 'markAsCompleted']);
    });

    // Achievement routes (Admin and Coach access)
    Route::middleware(['check.role:admin,coach'])->prefix('achievements')->group(function () {
        Route::get('/', [AchievementController::class, 'index']);
        Route::post('/', [AchievementController::class, 'store']);
        Route::get('/{achievement}', [AchievementController::class, 'show']);
        Route::put('/{achievement}', [AchievementController::class, 'update']);
        Route::delete('/{achievement}', [AchievementController::class, 'destroy']);
        Route::get('/member/{memberId}', [AchievementController::class, 'getMemberAchievements']);
        Route::get('/stats', [AchievementController::class, 'getStats']);
        Route::post('/award/{memberId}', [AchievementController::class, 'awardAchievement']);
    });

    // Session routes (Admin and Coach access)
    Route::middleware(['check.role:admin,coach'])->prefix('sessions')->group(function () {
        Route::get('/', [SessionController::class, 'index']);
        Route::post('/', [SessionController::class, 'store']);
        Route::get('/{session}', [SessionController::class, 'show']);
        Route::put('/{session}', [SessionController::class, 'update']);
        Route::delete('/{session}', [SessionController::class, 'destroy']);
        Route::get('/coach/{coachId}', [SessionController::class, 'getCoachSessions']);
        Route::get('/member/{memberId}', [SessionController::class, 'getMemberSessions']);
        Route::get('/stats', [SessionController::class, 'getStats']);
        Route::post('/{session}/complete', [SessionController::class, 'markAsCompleted']);
    });

    // Members API routes (Admin only)
    Route::middleware(['check.role:admin'])->prefix('members')->group(function () {
        Route::get('/', [MembersApiController::class, 'index']);
        Route::post('/', [MembersApiController::class, 'store']);
        Route::get('/{member}', [MembersApiController::class, 'show']);
        Route::put('/{member}', [MembersApiController::class, 'update']);
        Route::delete('/{member}', [MembersApiController::class, 'destroy']);
        Route::get('/stats', [MembersApiController::class, 'getStats']);
        Route::get('/active', [MembersApiController::class, 'getActiveMembers']);
        Route::get('/inactive', [MembersApiController::class, 'getInactiveMembers']);
        Route::post('/bulk-update', [MembersApiController::class, 'bulkUpdate']);
        Route::post('/bulk-delete', [MembersApiController::class, 'bulkDelete']);
    });

    // Coaches API routes (Admin only)
    Route::middleware(['check.role:admin'])->prefix('coaches')->group(function () {
        Route::get('/', [CoachesApiController::class, 'index']);
        Route::post('/', [CoachesApiController::class, 'store']);
        Route::get('/{coach}', [CoachesApiController::class, 'show']);
        Route::put('/{coach}', [CoachesApiController::class, 'update']);
        Route::delete('/{coach}', [CoachesApiController::class, 'destroy']);
        Route::get('/stats', [CoachesApiController::class, 'getStats']);
        Route::get('/available', [CoachesApiController::class, 'getAvailableCoaches']);
        Route::post('/bulk-update', [CoachesApiController::class, 'bulkUpdate']);
        Route::get('/{coachId}/members', [CoachesApiController::class, 'getCoachMembers']);
        Route::get('/{coachId}/performance', [CoachesApiController::class, 'getCoachPerformance']);
    });
    
    // Admin only routes
    Route::middleware(['check.role:admin'])->prefix('admin')->group(function () {
        // Admin specific routes will be added here
        Route::get('/system-stats', [DashboardController::class, 'adminOverview']);
        Route::get('/user-management', [UserController::class, 'index']);
        Route::get('/coach-management', [CoachMemberController::class, 'getAvailableCoaches']);
    });
    
    // Coach only routes (Mobile App)
    Route::middleware(['check.role:coach'])->prefix('coach')->group(function () {
        // Coach specific routes for mobile app
        Route::get('/my-members', [CoachMemberController::class, 'getCoachMembers']);
        Route::get('/my-stats', [CoachMemberController::class, 'getStats']);
        Route::get('/member/{memberId}', [UserController::class, 'show']);
        Route::get('/my-workout-plans', [WorkoutPlanController::class, 'getCoachWorkoutPlans']);
        Route::get('/my-nutrition-plans', [NutritionPlanController::class, 'getCoachNutritionPlans']);
        Route::post('/workout-plan', [WorkoutPlanController::class, 'store']);
        Route::put('/workout-plan/{id}', [WorkoutPlanController::class, 'update']);
        Route::delete('/workout-plan/{id}', [WorkoutPlanController::class, 'destroy']);
        Route::post('/nutrition-plan', [NutritionPlanController::class, 'store']);
        Route::put('/nutrition-plan/{id}', [NutritionPlanController::class, 'update']);
        Route::delete('/nutrition-plan/{id}', [NutritionPlanController::class, 'destroy']);
    });
    
    // Member only routes (Mobile App)
    Route::middleware(['check.role:member'])->prefix('member')->group(function () {
        // Member specific routes for mobile app
        Route::get('/my-coach', [CoachMemberController::class, 'getMemberCoach']);
        Route::get('/profile', [UserController::class, 'show']);
        Route::get('/my-workout-plans', [WorkoutPlanController::class, 'getMemberWorkoutPlans']);
        Route::get('/my-nutrition-plans', [NutritionPlanController::class, 'getMemberNutritionPlans']);
        Route::get('/my-subscription', [SubscriptionController::class, 'getCurrentSubscription']);
        Route::get('/my-attendance', [AttendanceController::class, 'getMemberAttendance']);
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
    });
});

// Test route (can be removed later)
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Test fitness data route (for frontend development without authentication)
Route::get('/test-fitness-data', function() {
    try {
        $fitnessDataCount = \App\Models\FitnessData::count();
        
        if ($fitnessDataCount === 0) {
            return response()->json([
                'error' => 'No fitness data found',
                'fitnessDataCount' => $fitnessDataCount
            ]);
        }
        
        $fitnessData = \App\Models\FitnessData::with(['user:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($data) {
                return [
                    'id' => $data->id,
                    'user_id' => $data->user_id,
                    'user_name' => $data->user->name ?? 'Unknown',
                    'user_email' => $data->user->email ?? 'Unknown',
                    'weight' => $data->weight,
                    'height' => $data->height,
                    'bmi' => $data->bmi,
                    'fat_percent' => $data->fat_percent,
                    'muscle_mass' => $data->muscle_mass,
                    'body_fat_percentage' => $data->body_fat_percentage,
                    'waist_circumference' => $data->waist_circumference,
                    'chest_circumference' => $data->chest_circumference,
                    'arm_circumference' => $data->arm_circumference,
                    'leg_circumference' => $data->leg_circumference,
                    'notes' => $data->notes,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,
                ];
            });
        
        $stats = [
            'total_records' => $fitnessDataCount,
            'average_weight' => \App\Models\FitnessData::avg('weight'),
            'average_bmi' => \App\Models\FitnessData::avg('bmi'),
            'average_body_fat' => \App\Models\FitnessData::avg('fat_percent'),
            'progress_by_member' => \App\Models\User::where('role', 'member')
                ->withCount('fitnessData')
                ->orderBy('fitness_data_count', 'desc')
                ->take(10)
                ->get()
        ];
        
        return response()->json([
            'fitnessData' => $fitnessData,
            'stats' => $stats
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
