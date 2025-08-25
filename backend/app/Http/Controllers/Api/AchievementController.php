<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AchievementController extends Controller
{
    /**
     * Display a listing of achievements
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Achievement::query();

        // Filter based on user role
        if ($user->role === 'coach') {
            $query->whereHas('user', function($q) use ($user) {
                $q->whereHas('memberCoachRelationships', function($coachQuery) use ($user) {
                    $coachQuery->where('coach_id', $user->id);
                });
            });
        } elseif ($user->role === 'member') {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('start_date')) {
            $query->whereDate('achieved_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('achieved_at', '<=', $request->end_date);
        }

        $achievements = $query->with(['user:id,name,email'])
            ->orderBy('achieved_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $achievements
        ]);
    }

    /**
     * Store a newly created achievement
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only admins and coaches can create achievements
        if (!in_array($user->role, ['admin', 'coach'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only admins and coaches can create achievements.'
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'points' => 'required|integer|min:0',
            'achieved_at' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if user is a member
        $member = User::find($request->user_id);
        if ($member->role !== 'member') {
            return response()->json([
                'success' => false,
                'message' => 'Achievements can only be created for members.'
            ], 400);
        }

        // If user is a coach, verify they are assigned to this member
        if ($user->role === 'coach') {
            $isAssigned = \App\Models\coach_member::where('coach_id', $user->id)
                ->where('member_id', $request->user_id)
                ->exists();
            
            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only create achievements for your assigned members.'
                ], 403);
            }
        }

        $achievement = Achievement::create([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'category' => $request->category,
            'points' => $request->points,
            'achieved_at' => $request->achieved_at,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Achievement created successfully',
            'data' => $achievement->load(['user:id,name,email'])
        ], 201);
    }

    /**
     * Display the specified achievement
     */
    public function show(Achievement $achievement): JsonResponse
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'member' && $achievement->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'coach') {
            $isAssigned = \App\Models\coach_member::where('coach_id', $user->id)
                ->where('member_id', $achievement->user_id)
                ->exists();
            
            if (!$isAssigned) {
                return response()->json(['message' => 'Access denied'], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $achievement->load(['user:id,name,email'])
        ]);
    }

    /**
     * Update the specified achievement
     */
    public function update(Request $request, Achievement $achievement): JsonResponse
    {
        $user = $request->user();
        
        // Check access permissions
        if ($user->role === 'coach') {
            $isAssigned = \App\Models\coach_member::where('coach_id', $user->id)
                ->where('member_id', $achievement->user_id)
                ->exists();
            
            if (!$isAssigned) {
                return response()->json(['message' => 'Access denied'], 403);
            }
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'type' => 'sometimes|string|max:100',
            'category' => 'sometimes|string|max:100',
            'points' => 'sometimes|integer|min:0',
            'achieved_at' => 'sometimes|date',
            'notes' => 'sometimes|string|max:500',
        ]);

        $achievement->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Achievement updated successfully',
            'data' => $achievement->load(['user:id,name,email'])
        ]);
    }

    /**
     * Remove the specified achievement
     */
    public function destroy(Achievement $achievement): JsonResponse
    {
        $user = Auth::user();
        
        // Only admins can delete achievements
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $achievement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Achievement deleted successfully'
        ]);
    }

    /**
     * Get achievement statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $period = $request->get('period', 'month'); // week, month, year
        
        $startDate = $this->getStartDate($period);
        $query = Achievement::query();

        if ($user->role === 'coach') {
            $query->whereHas('user', function($q) use ($user) {
                $q->whereHas('memberCoachRelationships', function($coachQuery) use ($user) {
                    $coachQuery->where('coach_id', $user->id);
                });
            });
        } elseif ($user->role === 'member') {
            $query->where('user_id', $user->id);
        }

        $stats = [
            'total_achievements' => $query->where('achieved_at', '>=', $startDate)->count(),
            'total_points' => $query->where('achieved_at', '>=', $startDate)->sum('points'),
            'achievements_by_type' => $query->where('achieved_at', '>=', $startDate)
                ->select('type', \DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get(),
            'achievements_by_category' => $query->where('achieved_at', '>=', $startDate)
                ->select('category', \DB::raw('count(*) as count'))
                ->groupBy('category')
                ->get(),
            'top_achievers' => $this->getTopAchievers($user, $startDate),
            'recent_achievements' => $query->where('achieved_at', '>=', $startDate)
                ->with(['user:id,name,email'])
                ->orderBy('achieved_at', 'desc')
                ->take(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get top achievers
     */
    private function getTopAchievers($user, $startDate)
    {
        if ($user->role === 'admin') {
            return User::where('role', 'member')
                ->withSum(['achievements' => function($query) use ($startDate) {
                    $query->where('achieved_at', '>=', $startDate);
                }], 'points')
                ->orderBy('achievements_sum_points', 'desc')
                ->take(10)
                ->get();
        } elseif ($user->role === 'coach') {
            return $user->members()
                ->withSum(['achievements' => function($query) use ($startDate) {
                    $query->where('achieved_at', '>=', $startDate);
                }], 'points')
                ->orderBy('achievements_sum_points', 'desc')
                ->get();
        }

        return collect();
    }

    /**
     * Get start date based on period
     */
    private function getStartDate($period)
    {
        switch ($period) {
            case 'week':
                return now()->subWeek();
            case 'month':
                return now()->subMonth();
            case 'year':
                return now()->subYear();
            default:
                return now()->subMonth();
        }
    }
}
