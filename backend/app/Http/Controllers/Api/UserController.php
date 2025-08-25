<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users based on role
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $request->get('role');
        $search = $request->get('search');
        $status = $request->get('status');

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $query = User::query();

        // Filter by role
        if ($role) {
            $query->where('role', $role);
        }

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Note: status filter removed as users table doesn't have status column
        // Apply status filter
        // if ($status) {
        //     $query->where('status', $status);
        // }

        $users = $query->with(['subscriptions' => function($query) {
            $query->where('is_active', true);
        }, 'subscriptions.membership'])
        ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Store a newly created user
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
            'role' => 'required|in:admin,coach,member',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'emergency_contact' => 'nullable|string|max:255',
            'medical_conditions' => 'nullable|string|max:1000',
            'fitness_goals' => 'nullable|string|max:1000',
            'experience_level' => 'nullable|in:beginner,intermediate,advanced',
            'specializations' => 'nullable|array',
            'specializations.*' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = $request->all();
        $userData['password'] = Hash::make($request->password);
        $userData['specializations'] = $request->specializations ? json_encode($request->specializations) : null;

        $user = User::create($userData);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role !== 'admin' && $user->id != $id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $targetUser = User::with([
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
            'coach' // المدرب المسند للعضو
        ])->find($id);

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $targetUser
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role !== 'admin' && $user->id != $id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $targetUser = User::find($id);
        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
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
            'password' => 'nullable|string|min:8',
            'role' => [
                'sometimes',
                'in:admin,coach,member',
                function($attribute, $value, $fail) use ($user) {
                    if ($user->role !== 'admin' && $value === 'admin') {
                        $fail('You cannot assign admin role.');
                    }
                }
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'emergency_contact' => 'nullable|string|max:255',
            'medical_conditions' => 'nullable|string|max:1000',
            'fitness_goals' => 'nullable|string|max:1000',
            'experience_level' => 'nullable|in:beginner,intermediate,advanced',
            'specializations' => 'nullable|array',
            'specializations.*' => 'string',
            'status' => 'sometimes|in:active,inactive,suspended'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = $request->except('password');
        
        if ($request->password) {
            $userData['password'] = Hash::make($request->password);
        }

        if ($request->specializations) {
            $userData['specializations'] = json_encode($request->specializations);
        }

        $targetUser->update($userData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $targetUser->fresh()
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $targetUser = User::find($id);
        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Prevent admin from deleting themselves
        if ($targetUser->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account'
            ], 400);
        }

        $targetUser->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get coaches list with details
     */
    public function getCoaches(Request $request): JsonResponse
    {
        $search = $request->get('search');
        $specialization = $request->get('specialization');

        $query = User::where('role', 'coach');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($specialization) {
            $query->where('specializations', 'like', "%{$specialization}%");
        }

        $coaches = $query->withCount(['memberCoachRelationships', 'coachedWorkoutPlans', 'coachedNutritionPlans'])
        ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $coaches
        ]);
    }

    /**
     * Get members list with details
     */
    public function getMembers(Request $request): JsonResponse
    {
        $user = $request->user();
        $search = $request->get('search');
        $subscription_status = $request->get('subscription_status');

        if ($user->role === 'admin') {
            $query = User::where('role', 'member');
        } elseif ($user->role === 'coach') {
            $query = $user->members();
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

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

        $members = $query->with(['subscriptions' => function($query) {
            $query->where('is_active', true);
        }, 'subscriptions.membership', 'coach'])
        ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $members
        ]);
    }

    /**
     * Get user statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $stats = [
            'total_users' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_coaches' => User::where('role', 'coach')->count(),
            'total_members' => User::where('role', 'member')->count(),
            'active_users' => User::where('role', 'coach')->count() + User::where('role', 'member')->count(),
            'inactive_users' => 0,
            'suspended_users' => 0,
            'new_users_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            'users_by_gender' => [
                'male' => User::where('gender', 'male')->count(),
                'female' => User::where('gender', 'female')->count(),
                'other' => User::where('gender', 'other')->count(),
            ],
            'coaches_by_experience' => [
                'beginner' => User::where('role', 'coach')->where('experience_level', 'beginner')->count(),
                'intermediate' => User::where('role', 'coach')->where('experience_level', 'intermediate')->count(),
                'advanced' => User::where('role', 'coach')->where('experience_level', 'advanced')->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Update user profile (for own profile)
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'emergency_contact' => 'nullable|string|max:255',
            'medical_conditions' => 'nullable|string|max:1000',
            'fitness_goals' => 'nullable|string|max:1000',
            'experience_level' => 'nullable|in:beginner,intermediate,advanced',
            'specializations' => 'nullable|array',
            'specializations.*' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = $request->all();
        
        if ($request->specializations) {
            $userData['specializations'] = json_encode($request->specializations);
        }

        $user->update($userData);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user->fresh()
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|different:current_password',
            'confirm_password' => 'required|same:new_password'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Get user subscriptions
     */
    public function getUserSubscriptions($id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        $subscriptions = $user->subscriptions()->with('membership')->get();
        
        return response()->json([
            'success' => true,
            'data' => $subscriptions
        ]);
    }

    /**
     * Get user workout plans
     */
    public function getUserWorkoutPlans($id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        $workoutPlans = $user->workoutPlans()->with('coach')->get();
        
        return response()->json([
            'success' => true,
            'data' => $workoutPlans
        ]);
    }

    /**
     * Get user nutrition plans
     */
    public function getUserNutritionPlans($id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        $nutritionPlans = $user->nutritionPlans()->with('coach')->get();
        
        return response()->json([
            'success' => true,
            'data' => $nutritionPlans
        ]);
    }

    /**
     * Get user fitness data
     */
    public function getUserFitnessData($id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        $fitnessData = $user->fitnessData()->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $fitnessData
        ]);
    }

    /**
     * Get user attendances
     */
    public function getUserAttendances($id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        $attendances = $user->attendances()->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $attendances
        ]);
    }
} 