<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CoachesApiController extends Controller
{
    /**
     * Display a listing of coaches
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->get('search');
        $specialization = $request->get('specialization');
        $experience_level = $request->get('experience_level');

        $query = User::where('role', 'coach');

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by specialization
        if ($specialization) {
            $query->where('specializations', 'like', "%{$specialization}%");
        }

        // Filter by experience level
        if ($experience_level) {
            $query->where('experience_level', $experience_level);
        }

        $coaches = $query->withCount([
            'memberCoachRelationships', 
            'coachedWorkoutPlans', 
            'coachedNutritionPlans'
        ])->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $coaches
        ]);
    }

    /**
     * Store a newly created coach
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
            'experience_level' => 'required|in:beginner,intermediate,advanced',
            'specializations' => 'required|array|min:1',
            'specializations.*' => 'string|max:100',
            'certifications' => 'nullable|array',
            'certifications.*' => 'string|max:255',
            'bio' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $coachData = $request->all();
        $coachData['password'] = Hash::make($request->password);
        $coachData['role'] = 'coach';
        $coachData['specializations'] = json_encode($request->specializations);
        $coachData['certifications'] = $request->certifications ? json_encode($request->certifications) : null;

        $coach = User::create($coachData);

        return response()->json([
            'success' => true,
            'message' => 'Coach created successfully',
            'data' => $coach
        ], 201);
    }

    /**
     * Display the specified coach
     */
    public function show(Request $request, $id): JsonResponse
    {
        $coach = User::where('role', 'coach')->find($id);
        
        if (!$coach) {
            return response()->json([
                'success' => false,
                'message' => 'Coach not found'
            ], 404);
        }

        $coach->load([
            'memberCoachRelationships' => function($query) {
                $query->with(['member:id,name,email']);
            },
            'coachedWorkoutPlans' => function($query) {
                $query->latest()->take(10);
            },
            'coachedNutritionPlans' => function($query) {
                $query->latest()->take(10);
            },
            'sessions' => function($query) {
                $query->latest()->take(10);
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $coach
        ]);
    }

    /**
     * Update the specified coach
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role !== 'admin' && $user->id != $id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $coach = User::where('role', 'coach')->find($id);
        if (!$coach) {
            return response()->json([
                'success' => false,
                'message' => 'Coach not found'
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
            'experience_level' => 'sometimes|in:beginner,intermediate,advanced',
            'specializations' => 'sometimes|array|min:1',
            'specializations.*' => 'string|max:100',
            'certifications' => 'nullable|array',
            'certifications.*' => 'string|max:255',
            'bio' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $coachData = $request->all();
        
        if ($request->specializations) {
            $coachData['specializations'] = json_encode($request->specializations);
        }
        
        if ($request->certifications) {
            $coachData['certifications'] = json_encode($request->certifications);
        }

        $coach->update($coachData);

        return response()->json([
            'success' => true,
            'message' => 'Coach updated successfully',
            'data' => $coach->fresh()
        ]);
    }

    /**
     * Remove the specified coach
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $coach = User::where('role', 'coach')->find($id);
        if (!$coach) {
            return response()->json([
                'success' => false,
                'message' => 'Coach not found'
            ], 404);
        }

        // Check if coach has assigned members
        if ($coach->memberCoachRelationships()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete coach with assigned members. Please reassign members first.'
            ], 400);
        }

        $coach->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coach deleted successfully'
        ]);
    }

    /**
     * Get coach statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $stats = [
            'total_coaches' => User::where('role', 'coach')->count(),
            'coaches_by_experience' => User::where('role', 'coach')
                ->select('experience_level', \DB::raw('count(*) as count'))
                ->groupBy('experience_level')
                ->get(),
            'coaches_with_members' => User::where('role', 'coach')
                ->whereHas('memberCoachRelationships')
                ->count(),
            'coaches_without_members' => User::where('role', 'coach')
                ->whereDoesntHave('memberCoachRelationships')
                ->count(),
            'top_coaches_by_members' => User::where('role', 'coach')
                ->withCount('memberCoachRelationships')
                ->orderBy('member_coach_relationships_count', 'desc')
                ->take(5)
                ->get(),
            'coaches_by_specialization' => $this->getCoachesBySpecialization(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get coaches by specialization
     */
    private function getCoachesBySpecialization()
    {
        $coaches = User::where('role', 'coach')->get();
        $specializations = [];
        
        foreach ($coaches as $coach) {
            if ($coach->specializations) {
                $coachSpecializations = json_decode($coach->specializations, true);
                if (is_array($coachSpecializations)) {
                    foreach ($coachSpecializations as $specialization) {
                        if (!isset($specializations[$specialization])) {
                            $specializations[$specialization] = 0;
                        }
                        $specializations[$specialization]++;
                    }
                }
            }
        }
        
        $result = [];
        foreach ($specializations as $specialization => $count) {
            $result[] = [
                'specialization' => $specialization,
                'count' => $count
            ];
        }
        
        return collect($result)->sortByDesc('count')->values();
    }

    /**
     * Get coach's members
     */
    public function getCoachMembers(Request $request, $id): JsonResponse
    {
        $coach = User::where('role', 'coach')->find($id);
        
        if (!$coach) {
            return response()->json([
                'success' => false,
                'message' => 'Coach not found'
            ], 404);
        }

        $members = $coach->members()->with([
            'subscriptions' => function($query) {
                $query->where('is_active', true);
            },
            'subscriptions.membership',
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
                    'experience_level' => $coach->experience_level,
                    'specializations' => $coach->specializations ? json_decode($coach->specializations, true) : [],
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
}
