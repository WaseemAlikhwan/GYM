<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getStats()
    {
        try {
            // Get current month and last month
            $currentMonth = Carbon::now()->startOfMonth();
            $lastMonth = Carbon::now()->subMonth()->startOfMonth();
            
            // Total members (users with role 'member')
            $totalMembers = User::where('role', 'member')->count();
            $lastMonthMembers = User::where('role', 'member')
                ->where('created_at', '<', $currentMonth)
                ->count();
            $memberGrowth = $this->calculateGrowth($totalMembers, $lastMonthMembers);
            
            // Active coaches (users with role 'coach')
            $activeCoaches = User::where('role', 'coach')->count();
            $lastMonthCoaches = User::where('role', 'coach')
                ->where('created_at', '<', $currentMonth)
                ->count();
            $coachGrowth = $this->calculateGrowth($activeCoaches, $lastMonthCoaches);
            
            // Today's attendance - only if table exists and has data
            $todayAttendance = 0;
            $yesterdayAttendance = 0;
            try {
                $todayAttendance = Attendance::whereDate('created_at', Carbon::today())->count();
                $yesterdayAttendance = Attendance::whereDate('created_at', Carbon::yesterday())->count();
            } catch (\Exception $e) {
                // إذا لم يكن هناك جدول attendances، استخدم 0
                $todayAttendance = 0;
                $yesterdayAttendance = 0;
            }
            $attendanceGrowth = $this->calculateGrowth($todayAttendance, $yesterdayAttendance);
            
            // Monthly revenue - only if table exists and has data
            $monthlyRevenue = 0;
            $lastMonthRevenue = 0;
            try {
                $monthlyRevenue = Payment::whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->sum('amount');
                $lastMonthRevenue = Payment::whereMonth('created_at', Carbon::now()->subMonth()->month)
                    ->whereYear('created_at', Carbon::now()->subMonth()->year)
                    ->sum('amount');
            } catch (\Exception $e) {
                // إذا لم يكن هناك جدول payments، استخدم 0
                $monthlyRevenue = 0;
                $lastMonthRevenue = 0;
            }
            $revenueGrowth = $this->calculateGrowth($monthlyRevenue, $lastMonthRevenue);
            
            // Weekly attendance data - only if table exists and has data
            $weeklyAttendance = $this->getWeeklyAttendance();
            
            // Membership distribution - only if table exists and has data
            $membershipDistribution = $this->getMembershipDistribution();
            
            // Recent activities - only if table exists and has data
            $recentActivities = $this->getRecentActivities();
            
            // Expiring subscriptions - only if table exists and has data
            $expiringSubscriptions = $this->getExpiringSubscriptions();

            return response()->json([
                'stats' => [
                    'totalMembers' => $totalMembers,
                    'activeCoaches' => $activeCoaches,
                    'todayAttendance' => $todayAttendance,
                    'monthlyRevenue' => $monthlyRevenue,
                    'memberGrowth' => $memberGrowth,
                    'coachGrowth' => $coachGrowth,
                    'attendanceGrowth' => $attendanceGrowth,
                    'revenueGrowth' => $revenueGrowth,
                ],
                'weeklyAttendance' => $weeklyAttendance,
                'membershipDistribution' => $membershipDistribution,
                'recentActivities' => $recentActivities,
                'expiringSubscriptions' => $expiringSubscriptions,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch dashboard stats',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get members statistics
     */
    public function getMembersStats()
    {
        try {
            $totalMembers = User::where('role', 'member')->count();
            $lastMonthMembers = User::where('role', 'member')
                ->where('created_at', '<', Carbon::now()->startOfMonth())
                ->count();
            $growth = $this->calculateGrowth($totalMembers, $lastMonthMembers);

            return response()->json([
                'total' => $totalMembers,
                'growth' => $growth
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch members stats',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get coaches statistics
     */
    public function getCoachesStats()
    {
        try {
            $totalCoaches = User::where('role', 'coach')->count();
            $lastMonthCoaches = User::where('role', 'coach')
                ->where('created_at', '<', Carbon::now()->startOfMonth())
                ->count();
            $growth = $this->calculateGrowth($totalCoaches, $lastMonthCoaches);

            return response()->json([
                'total' => $totalCoaches,
                'growth' => $growth
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch coaches stats',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance statistics
     */
    public function getAttendanceStats()
    {
        try {
            $todayAttendance = Attendance::whereDate('created_at', Carbon::today())->count();
            $yesterdayAttendance = Attendance::whereDate('created_at', Carbon::yesterday())->count();
            $growth = $this->calculateGrowth($todayAttendance, $yesterdayAttendance);

            return response()->json([
                'today' => $todayAttendance,
                'growth' => $growth
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch attendance stats',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get revenue statistics
     */
    public function getRevenueStats()
    {
        try {
            $monthlyRevenue = Payment::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('amount');
            $lastMonthRevenue = Payment::whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->whereYear('created_at', Carbon::now()->subMonth()->year)
                ->sum('amount');
            $growth = $this->calculateGrowth($monthlyRevenue, $lastMonthRevenue);

            return response()->json([
                'monthly' => $monthlyRevenue,
                'growth' => $growth
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch revenue stats',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get expiring subscriptions
     */
    public function getExpiringSubscriptions()
    {
        try {
            $today = Carbon::today();
            $nextWeek = Carbon::today()->addDays(7);
            
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
                        $daysUntilExpiry = Carbon::parse($subscription->end_date)->diffInDays(Carbon::today());
                        $status = $daysUntilExpiry === 0 ? 'expires_today' : 'expires_soon';
                        
                        return [
                            'id' => $subscription->id,
                            'user_name' => $subscription->user->name ?? 'Unknown',
                            'user_email' => $subscription->user->email ?? 'Unknown',
                            'plan_type' => $subscription->membership->name ?? 'Basic',
                            'end_date' => $subscription->end_date,
                            'days_until_expiry' => $daysUntilExpiry,
                            'status' => $status,
                            'formatted_end_date' => Carbon::parse($subscription->end_date)->format('Y-m-d'),
                            'formatted_days' => $daysUntilExpiry === 0 ? 'اليوم' : "بعد $daysUntilExpiry يوم",
                            'price' => $subscription->membership->price ?? 0,
                            'payment_method' => 'subscription',
                        ];
                    });
            } catch (\Exception $e) {
                // إذا لم يكن هناك جدول subscriptions، استخدم بيانات فارغة
                // لا نضيف بيانات وهمية، نرجع collection فارغة
            }
            
            return $expiringSubscriptions;

        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Calculate growth percentage
     */
    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? '+100%' : '0%';
        }
        
        $growth = (($current - $previous) / $previous) * 100;
        $sign = $growth >= 0 ? '+' : '';
        
        return $sign . round($growth, 1) . '%';
    }
    
    /**
     * Get weekly attendance data
     */
    private function getWeeklyAttendance()
    {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $weeklyData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayName = $days[$date->dayOfWeek - 1];
            $attendance = 0;
            
            try {
                $attendance = Attendance::whereDate('created_at', $date)->count();
        } catch (\Exception $e) {
                // إذا لم يكن هناك جدول attendances، استخدم 0
            }
            
            $weeklyData[] = [
                'name' => $dayName,
                'attendance' => $attendance
            ];
        }
        
        return $weeklyData;
    }
    
    /**
     * Get membership distribution
     */
    private function getMembershipDistribution()
    {
        try {
            $memberships = Membership::select('type', DB::raw('count(*) as value'))
                ->groupBy('type')
                ->get();
                
            if ($memberships->isEmpty()) {
                // إرجاع collection فارغة إذا لم تكن هناك عضويات
                return collect();
            }
            
            return $memberships->map(function ($item) {
                return [
                    'name' => ucfirst($item->type),
                    'value' => $item->value
                ];
            });
        } catch (\Exception $e) {
            // إرجاع collection فارغة إذا لم يكن هناك جدول memberships
            return collect();
        }
    }
    
    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        $activities = collect();

        // Recent member registrations
        $recentMembers = User::where('role', 'member')
            ->latest()
            ->take(2)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'action' => 'New member registered',
                    'user' => $user->name,
                    'time' => $user->created_at->diffForHumans(),
                    'type' => 'member'
                ];
            });

        // Recent payments
        $recentPayments = collect();
        try {
            $recentPayments = Payment::latest()
                ->take(2)
                ->get()
                ->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'action' => 'Payment received',
                        'user' => $payment->user->name ?? 'Unknown',
                        'time' => $payment->created_at->diffForHumans(),
                        'type' => 'payment'
                    ];
                });
        } catch (\Exception $e) {
            // إذا لم يكن هناك جدول payments، تجاهل
        }
        
        $activities = $activities->merge($recentMembers)->merge($recentPayments);
        
        // إرجاع النشاطات الموجودة فعلياً فقط
        return $activities->sortByDesc('time')->take(4)->values();
    }
}
