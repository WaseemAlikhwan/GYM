<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\coach_member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CoachMemberController extends Controller
{
    /**
     * Display a listing of coach-member relationships
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role === 'admin') {
            $relationships = coach_member::with(['coach', 'member'])
                ->paginate(15);
        } elseif ($user->role === 'coach') {
            $relationships = coach_member::where('coach_id', $user->id)
                ->with(['member'])
                ->paginate(15);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $relationships
        ]);
    }

    /**
     * Assign a member to a coach
     */
    public function assignMember(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'coach_id' => 'required|exists:users,id',
            'member_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if coach and member exist and have correct roles
        $coach = User::where('id', $request->coach_id)->where('role', 'coach')->first();
        $member = User::where('id', $request->member_id)->where('role', 'member')->first();

        if (!$coach || !$member) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coach or member'
            ], 400);
        }

        // Check if relationship already exists
        $existingRelationship = coach_member::where('coach_id', $request->coach_id)
            ->where('member_id', $request->member_id)
            ->first();

        if ($existingRelationship) {
            return response()->json([
                'success' => false,
                'message' => 'Member is already assigned to this coach'
            ], 400);
        }

        $relationship = coach_member::create([
            'coach_id' => $request->coach_id,
            'member_id' => $request->member_id,
            'start_date' => $request->start_date,
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member assigned to coach successfully',
            'data' => $relationship->load(['coach', 'member'])
        ], 201);
    }

    /**
     * Update coach-member relationship
     */
    public function update(Request $request, coach_member $coach_member): JsonResponse
    {
        $user = $request->user();
        
        // Only admin or the assigned coach can update
        if ($user->role !== 'admin' && $user->id !== $coach_member->coach_id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
            'end_date' => 'nullable|date|after:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $coach_member->update($request->only(['notes', 'end_date']));

        return response()->json([
            'success' => true,
            'message' => 'Relationship updated successfully',
            'data' => $coach_member->load(['coach', 'member'])
        ]);
    }

    /**
     * Remove coach-member relationship
     */
    public function destroy(coach_member $coach_member): JsonResponse
    {
        $coach_member->delete();

        return response()->json([
            'success' => true,
            'message' => 'Relationship removed successfully'
        ]);
    }

    /**
     * Get coach's members
     */
    public function getCoachMembers(Request $request, $coachId = null): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role === 'admin') {
            $coachId = $coachId ?? $request->get('coach_id');
            if (!$coachId) {
                return response()->json(['message' => 'Coach ID required'], 400);
            }
        } elseif ($user->role === 'coach') {
            $coachId = $user->id;
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $members = User::where('role', 'member')
            ->whereHas('memberCoachRelationships', function($query) use ($coachId) {
                $query->where('coach_id', $coachId);
            })
            ->with(['subscriptions' => function($query) {
                $query->where('is_active', true);
            }, 'subscriptions.membership', 'attendances' => function($query) {
                $query->latest()->take(5);
            }])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $members
        ]);
    }

    /**
     * Get member's coach
     */
    public function getMemberCoach(Request $request, $memberId = null): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role === 'admin') {
            $memberId = $memberId ?? $request->get('member_id');
            if (!$memberId) {
                return response()->json(['message' => 'Member ID required'], 400);
            }
        } elseif ($user->role === 'member') {
            $memberId = $user->id;
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $coach = User::where('role', 'coach')
            ->whereHas('memberCoachRelationships', function($query) use ($memberId) {
                $query->where('member_id', $memberId);
            })
            ->with(['coachedWorkoutPlans', 'coachedNutritionPlans'])
            ->first();

        if (!$coach) {
            return response()->json([
                'success' => false,
                'message' => 'No active coach found for this member'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $coach
        ]);
    }

    /**
     * Get available coaches for assignment
     */
    public function getAvailableCoaches(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $coaches = User::where('role', 'coach')
            ->withCount(['memberCoachRelationships'])
            ->get()
            ->map(function($coach) {
                return [
                    'id' => $coach->id,
                    'name' => $coach->name,
                    'email' => $coach->email,
                    'members_count' => $coach->member_coach_relationships_count,
                    'workout_plans_count' => $coach->coachedWorkoutPlans()->count(),
                    'nutrition_plans_count' => $coach->coachedNutritionPlans()->count()
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $coaches
        ]);
    }

    /**
     * Get unassigned members
     */
    public function getUnassignedMembers(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $members = User::where('role', 'member')
            ->whereDoesntHave('memberCoachRelationships')
            ->with(['subscriptions' => function($query) {
                $query->where('is_active', true);
            }, 'subscriptions.membership'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $members
        ]);
    }

    /**
     * Bulk assign members to coach
     */
    public function bulkAssign(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'coach_id' => 'required|exists:users,id',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:users,id',
            'start_date' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $coach = User::where('id', $request->coach_id)->where('role', 'coach')->first();
        if (!$coach) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coach'
            ], 400);
        }

        $assigned = [];
        $failed = [];

        foreach ($request->member_ids as $memberId) {
            $member = User::where('id', $memberId)->where('role', 'member')->first();
            
            if (!$member) {
                $failed[] = ['member_id' => $memberId, 'reason' => 'Invalid member'];
                continue;
            }

            // Check if already assigned
            $existingRelationship = coach_member::where('coach_id', $request->coach_id)
                ->where('member_id', $memberId)
                ->first();

            if ($existingRelationship) {
                $failed[] = ['member_id' => $memberId, 'reason' => 'Already assigned'];
                continue;
            }

            $relationship = coach_member::create([
                'coach_id' => $request->coach_id,
                'member_id' => $memberId,
                'start_date' => $request->start_date,
                'notes' => $request->notes
            ]);

            $assigned[] = $relationship->load(['member']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bulk assignment completed',
            'data' => [
                'assigned' => $assigned,
                'failed' => $failed
            ]
        ]);
    }

    /**
     * Get relationship statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role === 'admin') {
            $stats = [
                'total_relationships' => coach_member::count(),
                'active_relationships' => coach_member::count(),
                'coaches_with_members' => User::where('role', 'coach')
                    ->whereHas('memberCoachRelationships')->count(),
                'unassigned_members' => User::where('role', 'member')
                    ->whereDoesntHave('memberCoachRelationships')->count(),
                'recent_assignments' => coach_member::where('created_at', '>=', now()->subDays(30))->count()
            ];
        } elseif ($user->role === 'coach') {
            $stats = [
                'total_members' => $user->memberCoachRelationships()->count(),
                'recent_assignments' => $user->memberCoachRelationships()
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count(),
                'active_members_with_subscriptions' => $user->members()
                    ->whereHas('subscriptions', function($query) {
                        $query->where('status', 'active');
                    })->count()
            ];
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
