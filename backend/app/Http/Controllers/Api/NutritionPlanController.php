<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\nutrition_plan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class NutritionPlanController extends Controller
{
    /**
     * Display a listing of nutrition plans
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = nutrition_plan::query();

        // Filter based on user role
        if ($user->role === 'coach') {
            $query->where('coach_id', $user->id);
        } elseif ($user->role === 'member') {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        if ($request->has('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('end_date', '>=', now());
            } elseif ($status === 'expired') {
                $query->where('end_date', '<', now());
            }
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $nutritionPlans = $query->with(['user:id,name,email', 'coach:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $nutritionPlans
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created nutrition plan
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only coaches and admins can create nutrition plans
        if (!in_array($user->role, ['coach', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only coaches and admins can create nutrition plans.'
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        // Check if user is a member
        $member = \App\Models\User::find($request->user_id);
        if ($member->role !== 'member') {
            return response()->json([
                'success' => false,
                'message' => 'Nutrition plans can only be created for members.'
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
                    'message' => 'You can only create nutrition plans for your assigned members.'
                ], 403);
            }
        }

        $nutritionPlan = nutrition_plan::create([
            'user_id' => $request->user_id,
            'coach_id' => $user->role === 'coach' ? $user->id : $request->coach_id ?? $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nutrition plan created successfully',
            'data' => $nutritionPlan->load(['user:id,name,email', 'coach:id,name,email'])
        ], 201);
    }

    /**
     * Display the specified nutrition plan
     */
    public function show(nutrition_plan $nutrition_plan): JsonResponse
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Check access permissions
        if ($user->role === 'member' && $nutrition_plan->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'coach' && $nutrition_plan->coach_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $nutrition_plan->load(['user:id,name,email', 'coach:id,name,email'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(nutrition_plan $nutrition_plan)
    {
        //
    }

    /**
     * Update the specified nutrition plan
     */
    public function update(Request $request, nutrition_plan $nutrition_plan): JsonResponse
    {
        $user = $request->user();
        
        // Check access permissions
        if ($user->role === 'member' && $nutrition_plan->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'coach' && $nutrition_plan->coach_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
        ]);

        $nutrition_plan->update($request->only(['title', 'description', 'start_date', 'end_date']));

        return response()->json([
            'success' => true,
            'message' => 'Nutrition plan updated successfully',
            'data' => $nutrition_plan->load(['user:id,name,email', 'coach:id,name,email'])
        ]);
    }

    /**
     * Remove the specified nutrition plan
     */
    public function destroy(nutrition_plan $nutrition_plan): JsonResponse
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Check access permissions
        if ($user->role === 'member' && $nutrition_plan->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'coach' && $nutrition_plan->coach_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $nutrition_plan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nutrition plan deleted successfully'
        ]);
    }

    /**
     * Get nutrition plans for a specific member (coach only)
     */
    public function getMemberNutritionPlans(Request $request, $memberId): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role !== 'coach') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        // Verify coach is assigned to this member
        $isAssigned = \App\Models\coach_member::where('coach_id', $user->id)
            ->where('member_id', $memberId)
            ->exists();
        
        if (!$isAssigned) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $nutritionPlans = nutrition_plan::where('user_id', $memberId)
            ->where('coach_id', $user->id)
            ->with(['user:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $nutritionPlans
        ]);
    }

    /**
     * Get active nutrition plans for current user
     */
    public function getActiveNutritionPlans(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = nutrition_plan::query();
        
        if ($user->role === 'coach') {
            $query->where('coach_id', $user->id);
        } elseif ($user->role === 'member') {
            $query->where('user_id', $user->id);
        }

        $activePlans = $query->where('end_date', '>=', now())
            ->with(['user:id,name,email', 'coach:id,name,email'])
            ->orderBy('start_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $activePlans
        ]);
    }

    /**
     * Get nutrition plan statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = nutrition_plan::query();

        if ($user->role === 'coach') {
            $query->where('coach_id', $user->id);
        } elseif ($user->role === 'member') {
            $query->where('user_id', $user->id);
        }

        $stats = [
            'total_plans' => $query->count(),
            'active_plans' => $query->where('end_date', '>=', now())->count(),
            'expired_plans' => $query->where('end_date', '<', now())->count(),
            'plans_this_month' => $query->where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
