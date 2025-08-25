<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GoalController extends Controller
{
    /**
     * Display a listing of goals
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Goal::query();

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
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $goals = $query->with(['user:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $goals
        ]);
    }

    /**
     * Store a newly created goal
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only admins, coaches, and members can create goals
        if (!in_array($user->role, ['admin', 'coach', 'member'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only admins, coaches, and members can create goals.'
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|string|max:100',
            'target_value' => 'required|numeric',
            'current_value' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'deadline' => 'required|date|after:today',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:active,in_progress,completed,failed',
        ]);

        // Check if user is a member
        $member = User::find($request->user_id);
        if ($member->role !== 'member') {
            return response()->json([
                'success' => false,
                'message' => 'Goals can only be created for members.'
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
                    'message' => 'You can only create goals for your assigned members.'
                ], 403);
            }
        }

        // If user is a member, they can only create goals for themselves
        if ($user->role === 'member' && $user->id !== $request->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only create goals for yourself.'
            ], 403);
        }

        $goal = Goal::create([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'target_value' => $request->target_value,
            'current_value' => $request->current_value ?? 0,
            'unit' => $request->unit,
            'deadline' => $request->deadline,
            'priority' => $request->priority,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Goal created successfully',
            'data' => $goal->load(['user:id,name,email'])
        ], 201);
    }

    /**
     * Display the specified goal
     */
    public function show(Goal $goal): JsonResponse
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'member' && $goal->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'coach') {
            $isAssigned = \App\Models\coach_member::where('coach_id', $user->id)
                ->where('member_id', $goal->user_id)
                ->exists();
            
            if (!$isAssigned) {
                return response()->json(['message' => 'Access denied'], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $goal->load(['user:id,name,email'])
        ]);
    }

    /**
     * Update the specified goal
     */
    public function update(Request $request, Goal $goal): JsonResponse
    {
        $user = $request->user();
        
        // Check access permissions
        if ($user->role === 'member' && $goal->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'coach') {
            $isAssigned = \App\Models\coach_member::where('coach_id', $user->id)
                ->where('member_id', $goal->user_id)
                ->exists();
            
            if (!$isAssigned) {
                return response()->json(['message' => 'Access denied'], 403);
            }
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'type' => 'sometimes|string|max:100',
            'target_value' => 'sometimes|numeric',
            'current_value' => 'sometimes|numeric',
            'unit' => 'sometimes|string|max:50',
            'deadline' => 'sometimes|date|after:today',
            'priority' => 'sometimes|in:low,medium,high',
            'status' => 'sometimes|in:active,in_progress,completed,failed',
        ]);

        $goal->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Goal updated successfully',
            'data' => $goal->load(['user:id,name,email'])
        ]);
    }

    /**
     * Remove the specified goal
     */
    public function destroy(Goal $goal): JsonResponse
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 'member' && $goal->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'coach') {
            $isAssigned = \App\Models\coach_member::where('coach_id', $user->id)
                ->where('member_id', $goal->user_id)
                ->exists();
            
            if (!$isAssigned) {
                return response()->json(['message' => 'Access denied'], 403);
            }
        }

        $goal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Goal deleted successfully'
        ]);
    }

    /**
     * Update goal progress
     */
    public function updateProgress(Request $request, Goal $goal): JsonResponse
    {
        $user = $request->user();
        
        // Check access permissions
        if ($user->role === 'member' && $goal->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'coach') {
            $isAssigned = \App\Models\coach_member::where('coach_id', $user->id)
                ->where('member_id', $goal->user_id)
                ->exists();
            
            if (!$isAssigned) {
                return response()->json(['message' => 'Access denied'], 403);
            }
        }

        $request->validate([
            'current_value' => 'required|numeric',
            'notes' => 'nullable|string|max:500',
        ]);

        $goal->update([
            'current_value' => $request->current_value,
            'notes' => $request->notes,
        ]);

        // Check if goal is completed
        if ($goal->current_value >= $goal->target_value && $goal->status !== 'completed') {
            $goal->update(['status' => 'completed']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Goal progress updated successfully',
            'data' => $goal->load(['user:id,name,email'])
        ]);
    }

    /**
     * Get goal statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Goal::query();

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
            'total_goals' => $query->count(),
            'active_goals' => $query->where('status', 'active')->count(),
            'in_progress_goals' => $query->where('status', 'in_progress')->count(),
            'completed_goals' => $query->where('status', 'completed')->count(),
            'failed_goals' => $query->where('status', 'failed')->count(),
            'goals_by_type' => $query->select('type', \DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get(),
            'goals_by_priority' => $query->select('priority', \DB::raw('count(*) as count'))
                ->groupBy('priority')
                ->get(),
            'upcoming_deadlines' => $query->where('deadline', '>=', now())
                ->where('status', '!=', 'completed')
                ->orderBy('deadline')
                ->take(5)
                ->with(['user:id,name,email'])
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
