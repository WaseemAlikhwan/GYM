<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MembersApiController extends Controller
{
    /**
     * Display a listing of members
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $search = $request->get('search');
        $subscription_status = $request->get('subscription_status');
        $coach_id = $request->get('coach_id');

        if ($user->role === 'admin') {
            $query = User::where('role', 'member');
        } elseif ($user->role === 'coach') {
            $query = $user->members();
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by subscription status
        if ($subscription_status) {
            if ($subscription_status === 'active') {
                $query->whereHas('subscriptions', function($q) {
                    $q->where('is_active', true);
                });
            } elseif ($subscription_status === 'inactive') {
                $query->whereDoesntHave('subscriptions', function($q) {
                    $q->where('is_active', true);
                });
            }
        }

        // Filter by coach
        if ($coach_id) {
            $query->whereHas('memberCoachRelationships', function($q) use ($coach_id) {
                $q->where('coach_id', $coach_id);
            });
        }

        $members = $query->with([
            'subscriptions' => function($query) {
                $query->where('is_active', true);
            }, 
            'subscriptions.membership', 
            'coach',
            'attendances' => function($query) {
                $query->latest()->take(5);
            },
            'fitnessData' => function($query) {
                $query->latest()->take(1);
            }
        ])->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $members
        ]);
    }

    /**
     * Store a newly created member
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'emergency_contact' => 'nullable|string|max:255',
            'medical_conditions' => 'nullable|string|max:1000',
            'fitness_goals' => 'nullable|string|max:1000',
            'experience_level' => 'nullable|in:beginner,intermediate,advanced',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $memberData = $request->all();
        $memberData['password'] = Hash::make($request->password);
        $memberData['role'] = 'member';

        $member = User::create($memberData);

        return response()->json([
            'success' => true,
            'message' => 'Member created successfully',
            'data' => $member
        ], 201);
    }

    /**
     * Display the specified member
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role === 'admin') {
            $targetUser = User::where('role', 'member')->find($id);
        } elseif ($user->role === 'coach') {
            $targetUser = $user->members()->find($id);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        $targetUser->load([
            'subscriptions' => function($query) {
                $query->orderBy('created_at', 'desc');
            }, 
            'subscriptions.membership',
            'attendances' => function($query) {
                $query->latest()->take(10);
            }, 
            'fitnessData' => function($query) {
                $query->latest()->take(10);
            },
            'workoutPlans' => function($query) {
                $query->latest();
            },
            'workoutPlans.coach',
            'nutritionPlans' => function($query) {
                $query->latest();
            },
            'nutritionPlans.coach',
            'coach',
            'goals' => function($query) {
                $query->latest();
            },
            'achievements' => function($query) {
                $query->latest();
            },
            'payments' => function($query) {
                $query->latest()->take(10);
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $targetUser
        ]);
    }

    /**
     * Update the specified member
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role === 'admin') {
            $targetUser = User::where('role', 'member')->find($id);
        } elseif ($user->role === 'coach') {
            $targetUser = $user->members()->find($id);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($id)
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'emergency_contact' => 'nullable|string|max:255',
            'medical_conditions' => 'nullable|string|max:1000',
            'fitness_goals' => 'nullable|string|max:1000',
            'experience_level' => 'nullable|in:beginner,intermediate,advanced',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $targetUser->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Member updated successfully',
            'data' => $targetUser->fresh()
        ]);
    }

    /**
     * Remove the specified member
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $targetUser = User::where('role', 'member')->find($id);
        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        $targetUser->delete();

        return response()->json([
            'success' => true,
            'message' => 'Member deleted successfully'
        ]);
    }

    /**
     * Get member statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role === 'admin') {
            $query = User::where('role', 'member');
        } elseif ($user->role === 'coach') {
            $query = $user->members();
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $stats = [
            'total_members' => $query->count(),
            'active_members' => $query->whereHas('subscriptions', function($q) {
                $q->where('is_active', true);
            })->count(),
            'inactive_members' => $query->whereDoesntHave('subscriptions', function($q) {
                $q->where('is_active', true);
            })->count(),
            'members_by_gender' => $query->select('gender', \DB::raw('count(*) as count'))
                ->groupBy('gender')
                ->get(),
            'members_by_experience' => $query->select('experience_level', \DB::raw('count(*) as count'))
                ->groupBy('experience_level')
                ->get(),
            'new_members_this_month' => $query->where('created_at', '>=', now()->startOfMonth())->count(),
            'members_with_goals' => $query->whereHas('goals')->count(),
            'members_with_achievements' => $query->whereHas('achievements')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get member's coach
     */
    public function getMemberCoach(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role === 'admin') {
            $targetUser = User::where('role', 'member')->find($id);
        } elseif ($user->role === 'coach') {
            $targetUser = $user->members()->find($id);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        $coach = $targetUser->coach;
        
        if (!$coach) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No coach assigned to this member'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $coach
        ]);
    }

    /**
     * Assign coach to member
     */
    public function assignCoach(Request $request, $id): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $request->validate([
            'coach_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        $member = User::where('role', 'member')->find($id);
        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        $coach = User::where('role', 'coach')->find($request->coach_id);
        if (!$coach) {
            return response()->json([
                'success' => false,
                'message' => 'Coach not found'
            ], 404);
        }

        // Check if relationship already exists
        $existingRelationship = \App\Models\coach_member::where('coach_id', $request->coach_id)
            ->where('member_id', $id)
            ->first();

        if ($existingRelationship) {
            return response()->json([
                'success' => false,
                'message' => 'Member is already assigned to this coach'
            ], 400);
        }

        $relationship = \App\Models\coach_member::create([
            'coach_id' => $request->coach_id,
            'member_id' => $id,
            'start_date' => $request->start_date,
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coach assigned to member successfully',
            'data' => $relationship->load(['coach', 'member'])
        ], 201);
    }
}
